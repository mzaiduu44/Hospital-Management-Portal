<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class HospitalController extends Controller
{
    /**
     * Display a listing of hospitals.
     */
    public function index(Request $request)
    {
        try {
            $query = Hospital::query();

            // Search by name
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where('name', 'like', "%{$search}%");
            }

            // Filter by city
            if ($request->has('city')) {
                $query->where('city', $request->get('city'));
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Default: only show approved hospitals for non-admin users
            if ($request->get('approved_only', false)) {
                $query->where('status', 'Approved');
            }

            $hospitals = $query->orderBy('created_at', 'desc')->get();

            return apiSuccess('Hospitals retrieved successfully', $hospitals);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve hospitals: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified hospital.
     */
    public function show(string $id)
    {
        try {
            $hospital = Hospital::with(['appointments', 'covidTests', 'vaccinations'])->find($id);

            if (!$hospital) {
                return apiNotFound('Hospital not found');
            }

            return apiSuccess('Hospital retrieved successfully', $hospital);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve hospital: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified hospital.
     */
    public function update(Request $request, string $id)
    {
        try {
            $hospital = Hospital::find($id);

            if (!$hospital) {
                return apiNotFound('Hospital not found');
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:hospitals,email,' . $id,
                'phone' => 'sometimes|string|max:20',
                'address' => 'sometimes|string|max:500',
                'city' => 'sometimes|string|max:100',
                'state' => 'sometimes|string|max:100',
                'zip_code' => 'sometimes|string|max:20',
                'website' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
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
                if ($hospital->profile_photo_path && file_exists(public_path($hospital->profile_photo_path))) {
                    @unlink(public_path($hospital->profile_photo_path));
                }
            }

            $hospital->update($validated);

            return apiSuccess('Hospital updated successfully', $hospital);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to update hospital: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update hospital status (admin only).
     */
    public function updateStatus(Request $request, string $id)
    {
        try {
            $hospital = Hospital::find($id);

            if (!$hospital) {
                return apiNotFound('Hospital not found');
            }

            $validated = $request->validate([
                'status' => 'required|in:Approved,Rejected,Pending',
            ]);

            $hospital->update(['status' => $validated['status']]);

            return apiSuccess('Hospital status updated successfully', $hospital);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to update hospital status: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified hospital.
     */
    public function destroy(string $id)
    {
        try {
            $hospital = Hospital::find($id);

            if (!$hospital) {
                return apiNotFound('Hospital not found');
            }

            $hospital->delete();

            return apiSuccess('Hospital deleted successfully');
        } catch (\Exception $e) {
            return apiError('Failed to delete hospital: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get hospital's appointments.
     */
    public function appointments(string $id)
    {
        try {
            $hospital = Hospital::find($id);

            if (!$hospital) {
                return apiNotFound('Hospital not found');
            }

            $appointments = $hospital->appointments()
                ->with('patient')
                ->orderBy('date', 'desc')
                ->get();

            return apiSuccess('Appointments retrieved successfully', $appointments);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve appointments: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get list of cities with approved hospitals.
     */
    public function cities()
    {
        try {
            $cities = Hospital::where('status', 'Approved')
                ->distinct()
                ->pluck('city')
                ->sort()
                ->values();

            return apiSuccess('Cities retrieved successfully', $cities);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve cities: ' . $e->getMessage(), null, 500);
        }
    }
}
