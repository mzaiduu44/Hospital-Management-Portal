<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    /**
     * Display a listing of patients.
     */
    public function index(Request $request)
    {
        try {
            $query = Patient::query();

            // Search by name or email
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by city
            if ($request->has('city')) {
                $query->where('city', $request->get('city'));
            }

            $patients = $query->orderBy('created_at', 'desc')->get();

            return apiSuccess('Patients retrieved successfully', $patients);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve patients: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified patient.
     */
    public function show(string $id)
    {
        try {
            $patient = Patient::with(['appointments', 'covidTests', 'vaccinations'])->find($id);

            if (!$patient) {
                return apiNotFound('Patient not found');
            }

            return apiSuccess('Patient retrieved successfully', $patient);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve patient: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified patient.
     */
    public function update(Request $request, string $id)
    {
        try {
            $patient = Patient::find($id);

            if (!$patient) {
                return apiNotFound('Patient not found');
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:patients,email,' . $id,
                'mobile' => 'sometimes|string|max:20',
                'dob' => 'sometimes|date',
                'gender' => 'sometimes|in:Male,Female,Other',
                'address' => 'sometimes|string|max:500',
                'city' => 'sometimes|string|max:100',
                'password' => 'sometimes|string|min:6|confirmed',
                'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Hash password if provided
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            // Handle Profile Photo Upload
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/profile_photos'), $filename);
                $validated['profile_photo_path'] = '/uploads/profile_photos/' . $filename;

                // Delete old photo if exists
                if ($patient->profile_photo_path && file_exists(public_path($patient->profile_photo_path))) {
                    @unlink(public_path($patient->profile_photo_path));
                }
            }

            $patient->update($validated);

            return apiSuccess('Patient updated successfully', $patient);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to update patient: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified patient.
     */
    public function destroy(string $id)
    {
        try {
            $patient = Patient::find($id);

            if (!$patient) {
                return apiNotFound('Patient not found');
            }

            $patient->delete();

            return apiSuccess('Patient deleted successfully');
        } catch (\Exception $e) {
            return apiError('Failed to delete patient: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get patient's appointments.
     */
    public function appointments(string $id)
    {
        try {
            $patient = Patient::find($id);

            if (!$patient) {
                return apiNotFound('Patient not found');
            }

            $appointments = $patient->appointments()
                ->with('hospital')
                ->orderBy('date', 'desc')
                ->get();

            return apiSuccess('Appointments retrieved successfully', $appointments);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve appointments: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get patient's covid tests.
     */
    public function covidTests(string $id)
    {
        try {
            $patient = Patient::find($id);

            if (!$patient) {
                return apiNotFound('Patient not found');
            }

            $tests = $patient->covidTests()
                ->with('hospital')
                ->orderBy('test_date', 'desc')
                ->get();

            return apiSuccess('Covid tests retrieved successfully', $tests);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve covid tests: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get patient's vaccinations.
     */
    public function vaccinations(string $id)
    {
        try {
            $patient = Patient::find($id);

            if (!$patient) {
                return apiNotFound('Patient not found');
            }

            $vaccinations = $patient->vaccinations()
                ->with(['hospital', 'vaccine'])
                ->orderBy('vaccination_date', 'desc')
                ->get();

            return apiSuccess('Vaccinations retrieved successfully', $vaccinations);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve vaccinations: ' . $e->getMessage(), null, 500);
        }
    }
}
