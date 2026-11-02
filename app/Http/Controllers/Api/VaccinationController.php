<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vaccination;
use App\Models\VaccineInventory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VaccinationController extends Controller
{
    /**
     * Display a listing of vaccinations.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = Vaccination::with(['patient', 'hospital', 'vaccine', 'appointment']);

            // Filter based on user role
            if ($user instanceof \App\Models\Patient) {
                $query->where('patient_id', $user->id);
            } elseif ($user instanceof \App\Models\Hospital) {
                $query->where('hospital_id', $user->id);
            }
            // Admins can see all, so no additional filter needed

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Filter by vaccine
            if ($request->has('vaccine_id')) {
                $query->where('vaccine_id', $request->get('vaccine_id'));
            }

            // Filter by date range
            if ($request->has('from_date')) {
                $query->where('vaccination_date', '>=', $request->get('from_date'));
            }
            if ($request->has('to_date')) {
                $query->where('vaccination_date', '<=', $request->get('to_date'));
            }

            $vaccinations = $query->orderBy('vaccination_date', 'desc')->get();

            return apiSuccess('Vaccinations retrieved successfully', $vaccinations);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve vaccinations: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created vaccination.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'appointment_id' => 'required|exists:appointments,id',
                'patient_id' => 'required|exists:patients,id',
                'hospital_id' => 'required|exists:hospitals,id',
                'vaccine_id' => 'required|exists:vaccines,id',
                'dose_number' => 'required|integer|min:1|max:5',
                'status' => 'required|in:Completed,Scheduled,Cancelled',
                'vaccination_date' => 'required|date',
            ]);

            $vaccination = Vaccination::create($validated);

            // Decrement inventory when vaccination is completed
            if ($validated['status'] === 'Completed') {
                $this->decrementInventory($validated['hospital_id'], $validated['vaccine_id']);
            }

            $vaccination->load(['patient', 'hospital', 'vaccine', 'appointment']);

            return apiSuccess('Vaccination recorded successfully', $vaccination, 201);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to record vaccination: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified vaccination.
     */
    public function show(string $id)
    {
        try {
            $vaccination = Vaccination::with(['patient', 'hospital', 'vaccine', 'appointment'])->find($id);

            if (!$vaccination) {
                return apiNotFound('Vaccination not found');
            }

            return apiSuccess('Vaccination retrieved successfully', $vaccination);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve vaccination: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified vaccination.
     */
    public function update(Request $request, string $id)
    {
        try {
            $vaccination = Vaccination::find($id);

            if (!$vaccination) {
                return apiNotFound('Vaccination not found');
            }

            // Track if status is changing TO Completed (from non-Completed)
            $previousStatus = $vaccination->status;

            $validated = $request->validate([
                'dose_number' => 'sometimes|integer|min:1|max:5',
                'status' => 'sometimes|in:Completed,Scheduled,Cancelled',
                'vaccination_date' => 'sometimes|date',
            ]);

            $vaccination->update($validated);

            // Decrement inventory only if status changed TO Completed (not already Completed)
            if (isset($validated['status']) && $validated['status'] === 'Completed' && $previousStatus !== 'Completed') {
                $this->decrementInventory($vaccination->hospital_id, $vaccination->vaccine_id);
            }

            $vaccination->load(['patient', 'hospital', 'vaccine', 'appointment']);

            return apiSuccess('Vaccination updated successfully', $vaccination);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to update vaccination: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified vaccination.
     */
    public function destroy(string $id)
    {
        try {
            $vaccination = Vaccination::find($id);

            if (!$vaccination) {
                return apiNotFound('Vaccination not found');
            }

            $vaccination->delete();

            return apiSuccess('Vaccination deleted successfully');
        } catch (\Exception $e) {
            return apiError('Failed to delete vaccination: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Decrement vaccine inventory for a hospital.
     * Safely decrements by 1 dose if stock is available.
     */
    private function decrementInventory(int $hospitalId, int $vaccineId): void
    {
        $inventory = VaccineInventory::where('hospital_id', $hospitalId)
            ->where('vaccine_id', $vaccineId)
            ->first();

        if ($inventory && $inventory->quantity > 0) {
            $inventory->decrementStock(1);
        }
        // If no inventory record exists or quantity is 0, we still allow the vaccination
        // (vaccination is more important than inventory tracking)
    }
}
