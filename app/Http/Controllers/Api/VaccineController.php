<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\Vaccine;
use App\Models\VaccineInventory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VaccineController extends Controller
{
    /**
     * Display a listing of vaccines.
     */
    public function index(Request $request)
    {
        try {
            $query = Vaccine::query();

            // Filter by availability
            if ($request->has('available')) {
                $query->where('available', $request->boolean('available'));
            }

            // Search by name
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('manufacturer', 'like', "%{$search}%");
                });
            }

            $vaccines = $query->orderBy('name')->get();

            return apiSuccess('Vaccines retrieved successfully', $vaccines);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve vaccines: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created vaccine.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'manufacturer' => 'required|string|max:255',
                'doses_required' => 'required|integer|min:1|max:5',
                'available' => 'boolean',
            ]);

            $vaccine = Vaccine::create($validated);

            // Auto-create inventory entries for all approved hospitals with quantity 0
            $approvedHospitals = Hospital::where('status', 'Approved')->get();
            foreach ($approvedHospitals as $hospital) {
                VaccineInventory::create([
                    'hospital_id' => $hospital->id,
                    'vaccine_id' => $vaccine->id,
                    'quantity' => 0,
                    'min_threshold' => 10,
                ]);
            }

            return apiSuccess('Vaccine created successfully', $vaccine, 201);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to create vaccine: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified vaccine.
     */
    public function show(string $id)
    {
        try {
            $vaccine = Vaccine::withCount('vaccinations')->find($id);

            if (!$vaccine) {
                return apiNotFound('Vaccine not found');
            }

            return apiSuccess('Vaccine retrieved successfully', $vaccine);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve vaccine: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified vaccine.
     */
    public function update(Request $request, string $id)
    {
        try {
            $vaccine = Vaccine::find($id);

            if (!$vaccine) {
                return apiNotFound('Vaccine not found');
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'manufacturer' => 'sometimes|string|max:255',
                'doses_required' => 'sometimes|integer|min:1|max:5',
                'available' => 'sometimes|boolean',
            ]);

            $vaccine->update($validated);

            return apiSuccess('Vaccine updated successfully', $vaccine);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to update vaccine: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update vaccine availability.
     */
    public function updateAvailability(Request $request, string $id)
    {
        try {
            $vaccine = Vaccine::find($id);

            if (!$vaccine) {
                return apiNotFound('Vaccine not found');
            }

            $validated = $request->validate([
                'available' => 'required|boolean',
            ]);

            $vaccine->update(['available' => $validated['available']]);

            return apiSuccess('Vaccine availability updated successfully', $vaccine);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to update vaccine availability: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified vaccine.
     */
    public function destroy(string $id)
    {
        try {
            $vaccine = Vaccine::find($id);

            if (!$vaccine) {
                return apiNotFound('Vaccine not found');
            }

            // Check if vaccine has vaccinations
            if ($vaccine->vaccinations()->count() > 0) {
                return apiError('Cannot delete vaccine that has been used for vaccinations', null, 400);
            }

            $vaccine->delete();

            return apiSuccess('Vaccine deleted successfully');
        } catch (\Exception $e) {
            return apiError('Failed to delete vaccine: ' . $e->getMessage(), null, 500);
        }
    }
}
