<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CovidTest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CovidTestController extends Controller
{
    /**
     * Display a listing of covid tests.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = CovidTest::with(['patient', 'hospital', 'appointment']);

            // Filter based on user role
            if ($user instanceof \App\Models\Patient) {
                $query->where('patient_id', $user->id);
            } elseif ($user instanceof \App\Models\Hospital) {
                $query->where('hospital_id', $user->id);
            }
            // Admins can see all, so no additional filter needed

            // Filter by result
            if ($request->has('result')) {
                $query->where('result', $request->get('result'));
            }

            // Filter by date range
            if ($request->has('from_date')) {
                $query->where('test_date', '>=', $request->get('from_date'));
            }
            if ($request->has('to_date')) {
                $query->where('test_date', '<=', $request->get('to_date'));
            }

            $tests = $query->orderBy('test_date', 'desc')->get();

            return apiSuccess('Covid tests retrieved successfully', $tests);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve covid tests: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Store a newly created covid test.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'appointment_id' => 'required|exists:appointments,id',
                'patient_id' => 'required|exists:patients,id',
                'hospital_id' => 'required|exists:hospitals,id',
                'result' => 'required|in:Positive,Negative,Pending',
                'test_date' => 'required|date',
                'remarks' => 'nullable|string|max:1000',
                'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // Max 5MB
            ]);

            // Handle file upload
            $filePath = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('covid_reports', $fileName, 'public');
            }

            $test = CovidTest::create([
                'appointment_id' => $validated['appointment_id'],
                'patient_id' => $validated['patient_id'],
                'hospital_id' => $validated['hospital_id'],
                'result' => $validated['result'],
                'test_date' => $validated['test_date'],
                'remarks' => $validated['remarks'] ?? null,
                'file_path' => $filePath,
            ]);
            $test->load(['patient', 'hospital', 'appointment']);

            return apiSuccess('Covid test recorded successfully', $test, 201);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to record covid test: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified covid test.
     */
    public function show(string $id)
    {
        try {
            $test = CovidTest::with(['patient', 'hospital', 'appointment'])->find($id);

            if (!$test) {
                return apiNotFound('Covid test not found');
            }

            return apiSuccess('Covid test retrieved successfully', $test);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve covid test: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified covid test.
     */
    public function update(Request $request, string $id)
    {
        try {
            $test = CovidTest::find($id);

            if (!$test) {
                return apiNotFound('Covid test not found');
            }

            $validated = $request->validate([
                'result' => 'sometimes|in:Positive,Negative,Pending',
                'test_date' => 'sometimes|date',
                'remarks' => 'nullable|string|max:1000',
            ]);

            $test->update($validated);
            $test->load(['patient', 'hospital', 'appointment']);

            return apiSuccess('Covid test updated successfully', $test);
        } catch (ValidationException $e) {
            return apiValidationError($e->errors());
        } catch (\Exception $e) {
            return apiError('Failed to update covid test: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified covid test.
     */
    public function destroy(string $id)
    {
        try {
            $test = CovidTest::find($id);

            if (!$test) {
                return apiNotFound('Covid test not found');
            }

            $test->delete();

            return apiSuccess('Covid test deleted successfully');
        } catch (\Exception $e) {
            return apiError('Failed to delete covid test: ' . $e->getMessage(), null, 500);
        }
    }
}
