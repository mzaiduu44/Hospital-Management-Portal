<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\VaccineInventory;
use App\Models\Vaccine;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = Appointment::with(['patient', 'hospital', 'vaccine']);

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

            // Filter by purpose
            if ($request->has('purpose')) {
                $query->where('purpose', $request->get('purpose'));
            }

            // Filter by date range
            if ($request->has('from_date')) {
                $query->where('date', '>=', $request->get('from_date'));
            }
            if ($request->has('to_date')) {
                $query->where('date', '<=', $request->get('to_date'));
            }

            $appointments = $query->orderBy('date', 'desc')->get();

            return apiSuccess('Appointments retrieved successfully', $appointments);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve appointments: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'hospital_id' => 'required|exists:hospitals,id',
                'purpose' => 'required|in:Covid Test,Vaccination',
                'vaccine_id' => 'nullable|exists:vaccines,id',
                'date' => 'required|date|after_or_equal:today',
                'time' => 'required|string',
            ]);

            // Check vaccine inventory for vaccination appointments
            if ($validated['purpose'] === 'Vaccination' && isset($validated['vaccine_id'])) {
                $vaccineId = $validated['vaccine_id'];
                $hospitalId = $validated['hospital_id'];

                // Get the vaccine details for error message
                $vaccine = Vaccine::find($vaccineId);

                // Check inventory at this hospital
                $inventory = VaccineInventory::where('hospital_id', $hospitalId)
                    ->where('vaccine_id', $vaccineId)
                    ->first();

                $quantity = $inventory?->quantity ?? 0;

                if ($quantity <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vaccine not available',
                        'error_type' => 'vaccine_out_of_stock',
                        'data' => [
                            'vaccine_name' => $vaccine->name ?? 'Selected vaccine',
                            'hospital_id' => $hospitalId,
                        ]
                    ], 422);
                }
            }

            $appointment = Appointment::create([
                'patient_id' => $validated['patient_id'],
                'hospital_id' => $validated['hospital_id'],
                'purpose' => $validated['purpose'],
                'vaccine_id' => $validated['vaccine_id'] ?? null,
                'date' => $validated['date'],
                'time' => $validated['time'],
                'status' => 'Pending',
            ]);

            $appointment->load(['patient', 'hospital', 'vaccine']);

            return apiSuccess('Appointment created successfully', $appointment, 201);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to create appointment: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified appointment.
     */
    public function show(string $id)
    {
        try {
            $appointment = Appointment::with(['patient', 'hospital', 'vaccine', 'covidTest', 'vaccination.vaccine'])->find($id);

            if (!$appointment) {
                return apiNotFound('Appointment not found');
            }

            return apiSuccess('Appointment retrieved successfully', $appointment);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve appointment: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, string $id)
    {
        try {
            $appointment = Appointment::find($id);

            if (!$appointment) {
                return apiNotFound('Appointment not found');
            }

            $validated = $request->validate([
                'purpose' => 'sometimes|in:Covid Test,Vaccination',
                'vaccine_id' => 'nullable|exists:vaccines,id',
                'date' => 'sometimes|date',
                'time' => 'sometimes|string',
            ]);

            $appointment->update($validated);
            $appointment->load(['patient', 'hospital']);

            return apiSuccess('Appointment updated successfully', $appointment);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to update appointment: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update appointment status.
     */
    public function updateStatus(Request $request, string $id)
    {
        try {
            $appointment = Appointment::find($id);

            if (!$appointment) {
                return apiNotFound('Appointment not found');
            }

            $validated = $request->validate([
                'status' => 'required|in:Approved,Rejected,Pending',
            ]);

            $appointment->update(['status' => $validated['status']]);
            $appointment->load(['patient', 'hospital']);

            return apiSuccess('Appointment status updated successfully', $appointment);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to update appointment status: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy(string $id)
    {
        try {
            $appointment = Appointment::find($id);

            if (!$appointment) {
                return apiNotFound('Appointment not found');
            }

            $appointment->delete();

            return apiSuccess('Appointment deleted successfully');
        } catch (\Exception $e) {
            return apiError('Failed to delete appointment: ' . $e->getMessage(), null, 500);
        }
    }
}
