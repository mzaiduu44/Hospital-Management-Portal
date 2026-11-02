<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Hospital;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new patient.
     */
    public function registerPatient(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:patients',
                'password' => 'required|string|min:6|confirmed',
                'mobile' => 'required|string|max:20',
                'dob' => 'required|date',
                'gender' => 'required|in:Male,Female,Other',
                'address' => 'required|string|max:500',
                'city' => 'required|string|max:100',
            ]);

            $patient = Patient::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'mobile' => $validated['mobile'],
                'dob' => $validated['dob'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'city' => $validated['city'],
            ]);

            $token = $patient->createToken('patient-token')->plainTextToken;

            return apiSuccess('Patient registered successfully', [
                'user' => $patient,
                'token' => $token,
                'role' => 'patient',
            ], 201);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Registration failed: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Login a patient.
     */
    public function loginPatient(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $patient = Patient::where('email', $validated['email'])->first();

            if (!$patient || !Hash::check($validated['password'], $patient->password)) {
                return apiError('Invalid credentials', null, 401);
            }

            $token = $patient->createToken('patient-token')->plainTextToken;

            return apiSuccess('Login successful', [
                'user' => $patient,
                'token' => $token,
                'role' => 'patient',
            ]);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Login failed: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Register a new hospital.
     */
    public function registerHospital(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:hospitals',
                'password' => 'required|string|min:6|confirmed',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:500',
                'city' => 'required|string|max:100',
            ]);

            $hospital = Hospital::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'status' => 'Pending',
            ]);

            return apiSuccess('Hospital registered successfully. Please wait for admin approval.', [
                'hospital' => $hospital,
            ], 201);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Registration failed: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Login a hospital.
     */
    public function loginHospital(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $hospital = Hospital::where('email', $validated['email'])->first();

            if (!$hospital) {
                return apiError('Hospital not found', null, 404);
            }

            if (!Hash::check($validated['password'], $hospital->password)) {
                return apiError('Invalid password', null, 401);
            }

            if (!$hospital->isApproved()) {
                return apiError(
                    "Your hospital registration is {$hospital->status}. Please wait for admin approval.",
                    ['status' => $hospital->status],
                    403
                );
            }

            $token = $hospital->createToken('hospital-token')->plainTextToken;

            return apiSuccess('Login successful', [
                'user' => $hospital,
                'token' => $token,
                'role' => 'hospital',
            ]);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Login failed: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Login an admin.
     */
    public function loginAdmin(Request $request)
    {
        try {
            $validated = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $admin = Admin::where('username', $validated['username'])->first();

            if (!$admin || !Hash::check($validated['password'], $admin->password)) {
                return apiError('Invalid credentials', null, 401);
            }

            $token = $admin->createToken('admin-token')->plainTextToken;

            return apiSuccess('Login successful', [
                'user' => $admin,
                'token' => $token,
                'role' => 'admin',
            ]);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Login failed: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Logout the authenticated user.
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return apiSuccess('Logged out successfully');
        } catch (\Exception $e) {
            return apiError('Logout failed: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();
            $role = match (get_class($user)) {
                Patient::class => 'patient',
                Hospital::class => 'hospital',
                Admin::class => 'admin',
                default => null,
            };

            return apiSuccess('User retrieved', [
                'user' => $user,
                'role' => $role,
            ]);
        } catch (\Exception $e) {
            return apiError('Failed to get user: ' . $e->getMessage(), null, 500);
        }
    }
}
