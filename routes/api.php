<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CovidTestController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\VaccinationController;
use App\Http\Controllers\Api\VaccineController;
use App\Http\Controllers\Api\VaccineInventoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// ============================================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================================

// Authentication Routes
Route::prefix('auth')->group(function () {
    // Patient Auth
    Route::post('/patient/register', [AuthController::class, 'registerPatient']);
    Route::post('/patient/login', [AuthController::class, 'loginPatient']);

    // Hospital Auth
    Route::post('/hospital/register', [AuthController::class, 'registerHospital']);
    Route::post('/hospital/login', [AuthController::class, 'loginHospital']);

    // Admin Auth
    Route::post('/admin/login', [AuthController::class, 'loginAdmin']);
});

// Public Hospital listing (for patients searching hospitals)
Route::get('/hospitals', [HospitalController::class, 'index']);
Route::get('/hospitals/cities', [HospitalController::class, 'cities']);
Route::get('/hospitals/{id}', [HospitalController::class, 'show']);

// Public Vaccine listing
Route::get('/vaccines', [VaccineController::class, 'index']);
Route::get('/vaccines/{id}', [VaccineController::class, 'show']);

// Public News listing (for frontend display)
Route::get('/news/published', [NewsController::class, 'published']);

// Public Stats (Homepage)
Route::get('/public/stats', [DashboardController::class, 'stats']);

// Public Vaccine Inventory - Check availability at hospital
Route::get('/hospitals/{hospitalId}/vaccines/{vaccineId}/availability', [VaccineInventoryController::class, 'checkAvailability']);
Route::get('/hospitals/{hospitalId}/available-vaccines', [VaccineInventoryController::class, 'getAvailableVaccines']);

// ============================================================================
// PROTECTED ROUTES (Authentication Required)
// ============================================================================

Route::middleware('auth:sanctum')->group(function () {

    // Auth endpoints
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // -------------------------------------------------------------------------
    // PATIENT ROUTES
    // -------------------------------------------------------------------------
    Route::prefix('patients')->group(function () {
        Route::get('/', [PatientController::class, 'index']);
        Route::get('/{id}', [PatientController::class, 'show']);
        Route::put('/{id}', [PatientController::class, 'update']);
        Route::delete('/{id}', [PatientController::class, 'destroy']);

        // Patient's related data
        Route::get('/{id}/appointments', [PatientController::class, 'appointments']);
        Route::get('/{id}/covid-tests', [PatientController::class, 'covidTests']);
        Route::get('/{id}/vaccinations', [PatientController::class, 'vaccinations']);
    });

    // -------------------------------------------------------------------------
    // HOSPITAL ROUTES
    // -------------------------------------------------------------------------
    Route::prefix('hospitals')->group(function () {
        Route::put('/{id}', [HospitalController::class, 'update']);
        Route::put('/{id}/status', [HospitalController::class, 'updateStatus']);
        Route::delete('/{id}', [HospitalController::class, 'destroy']);

        // Hospital's related data
        Route::get('/{id}/appointments', [HospitalController::class, 'appointments']);

        // Hospital Inventory Management
        Route::get('/{hospitalId}/inventory', [VaccineInventoryController::class, 'index']);
        Route::put('/{hospitalId}/inventory/{vaccineId}', [VaccineInventoryController::class, 'update']);
        Route::post('/{hospitalId}/inventory/{vaccineId}/refill', [VaccineInventoryController::class, 'refill']);
    });

    // -------------------------------------------------------------------------
    // APPOINTMENT ROUTES
    // -------------------------------------------------------------------------
    Route::prefix('appointments')->group(function () {
        Route::get('/', [AppointmentController::class, 'index']);
        Route::post('/', [AppointmentController::class, 'store']);
        Route::get('/{id}', [AppointmentController::class, 'show']);
        Route::put('/{id}', [AppointmentController::class, 'update']);
        Route::put('/{id}/status', [AppointmentController::class, 'updateStatus']);
        Route::delete('/{id}', [AppointmentController::class, 'destroy']);
    });

    // -------------------------------------------------------------------------
    // COVID TEST ROUTES
    // -------------------------------------------------------------------------
    Route::prefix('covid-tests')->group(function () {
        Route::get('/', [CovidTestController::class, 'index']);
        Route::post('/', [CovidTestController::class, 'store']);
        Route::get('/{id}', [CovidTestController::class, 'show']);
        Route::put('/{id}', [CovidTestController::class, 'update']);
        Route::delete('/{id}', [CovidTestController::class, 'destroy']);
    });

    // -------------------------------------------------------------------------
    // VACCINATION ROUTES
    // -------------------------------------------------------------------------
    Route::prefix('vaccinations')->group(function () {
        Route::get('/', [VaccinationController::class, 'index']);
        Route::post('/', [VaccinationController::class, 'store']);
        Route::get('/{id}', [VaccinationController::class, 'show']);
        Route::put('/{id}', [VaccinationController::class, 'update']);
        Route::delete('/{id}', [VaccinationController::class, 'destroy']);
    });

    // -------------------------------------------------------------------------
    // VACCINE ROUTES (Admin Only - Create/Update/Delete)
    // -------------------------------------------------------------------------
    Route::prefix('vaccines')->group(function () {
        Route::post('/', [VaccineController::class, 'store']);
        Route::put('/{id}', [VaccineController::class, 'update']);
        Route::put('/{id}/availability', [VaccineController::class, 'updateAvailability']);
        Route::delete('/{id}', [VaccineController::class, 'destroy']);
    });

    // -------------------------------------------------------------------------
    // DASHBOARD ROUTES (Admin)
    // -------------------------------------------------------------------------
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/recent-appointments', [DashboardController::class, 'recentAppointments']);
        Route::get('/monthly-stats', [DashboardController::class, 'monthlyStats']);
        Route::get('/city-stats', [DashboardController::class, 'cityStats']);
    });

    // -------------------------------------------------------------------------
    // NEWS ROUTES (Admin Only - Create/Update/Delete)
    // -------------------------------------------------------------------------
    Route::prefix('news')->group(function () {
        Route::get('/', [NewsController::class, 'index']);
        Route::post('/', [NewsController::class, 'store']);
        Route::get('/{id}', [NewsController::class, 'show']);
        Route::put('/{id}', [NewsController::class, 'update']);
        Route::delete('/{id}', [NewsController::class, 'destroy']);
    });

    // -------------------------------------------------------------------------
    // AI CHAT ROUTES
    // -------------------------------------------------------------------------
    Route::post('/ai/chat', [\App\Http\Controllers\Api\AiChatController::class, 'chat']);
    Route::post('/ai/generate-news-content', [\App\Http\Controllers\Api\AiContentController::class, 'generateNewsContent']);

    // -------------------------------------------------------------------------
    // SYSTEM SETTINGS ROUTES (Admin)
    // -------------------------------------------------------------------------
    Route::prefix('system')->group(function () {
        Route::get('/stats', [\App\Http\Controllers\Api\SystemController::class, 'stats']);
        Route::post('/optimize', [\App\Http\Controllers\Api\SystemController::class, 'optimize']);
        Route::post('/clear-logs', [\App\Http\Controllers\Api\SystemController::class, 'clearLogs']);
    });

    // -------------------------------------------------------------------------
    // NOTIFICATION ROUTES
    // -------------------------------------------------------------------------
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::get('/unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
        Route::post('/', [\App\Http\Controllers\Api\NotificationController::class, 'store']);
        Route::put('/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
        Route::put('/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\NotificationController::class, 'destroy']);
    });
});
