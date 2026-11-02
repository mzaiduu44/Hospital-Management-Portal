<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\CovidTest;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Vaccination;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function stats()
    {
        try {
            $stats = [
                'totalPatients' => Patient::count(),
                'totalHospitals' => Hospital::count(),
                'totalVaccinations' => Vaccination::where('status', 'Completed')->count(),
                'totalTests' => CovidTest::count(),
                'pendingAppointments' => Appointment::where('status', 'Pending')->count(),
                'approvedHospitals' => Hospital::where('status', 'Approved')->count(),
                'pendingHospitals' => Hospital::where('status', 'Pending')->count(),
                'rejectedHospitals' => Hospital::where('status', 'Rejected')->count(),
                'positiveTests' => CovidTest::where('result', 'Positive')->count(),
                'negativeTests' => CovidTest::where('result', 'Negative')->count(),
                'scheduledVaccinations' => Vaccination::where('status', 'Scheduled')->count(),
            ];

            return apiSuccess('Dashboard statistics retrieved successfully', $stats);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve dashboard statistics: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get recent appointments.
     */
    public function recentAppointments(Request $request)
    {
        try {
            $limit = $request->get('limit', 5);

            $appointments = Appointment::with(['patient', 'hospital'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return apiSuccess('Recent appointments retrieved successfully', $appointments);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve recent appointments: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get monthly statistics.
     */
    public function monthlyStats(Request $request)
    {
        try {
            $year = $request->get('year', now()->year);

            $monthlyVaccinations = Vaccination::selectRaw('MONTH(vaccination_date) as month, COUNT(*) as count')
                ->whereYear('vaccination_date', $year)
                ->where('status', 'Completed')
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month');

            $monthlyTests = CovidTest::selectRaw('MONTH(test_date) as month, COUNT(*) as count')
                ->whereYear('test_date', $year)
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month');

            $monthlyAppointments = Appointment::selectRaw('MONTH(date) as month, COUNT(*) as count')
                ->whereYear('date', $year)
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month');

            return apiSuccess('Monthly statistics retrieved successfully', [
                'year' => $year,
                'vaccinations' => $monthlyVaccinations,
                'tests' => $monthlyTests,
                'appointments' => $monthlyAppointments,
            ]);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve monthly statistics: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get city-wise statistics.
     */
    public function cityStats()
    {
        try {
            $hospitalsByCitty = Hospital::where('status', 'Approved')
                ->selectRaw('city, COUNT(*) as count')
                ->groupBy('city')
                ->orderByDesc('count')
                ->get();

            $patientsByCity = Patient::selectRaw('city, COUNT(*) as count')
                ->groupBy('city')
                ->orderByDesc('count')
                ->get();

            return apiSuccess('City statistics retrieved successfully', [
                'hospitals' => $hospitalsByCitty,
                'patients' => $patientsByCity,
            ]);
        } catch (\Exception $e) {
            return apiError('Failed to retrieve city statistics: ' . $e->getMessage(), null, 500);
        }
    }
}
