<?php

use App\Http\Controllers\AiTriageController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicalController;
use App\Http\Controllers\FinanceAdminController;
use App\Http\Controllers\LookupController;
use App\Http\Controllers\PharmacyLabController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HealthHub REST API (prefix otomatis: /api)
|--------------------------------------------------------------------------
| Semua respons memakai bentuk baku: { success, message, data }.
| Endpoint tulis memakai token Sanctum: Authorization: Bearer <token>.
*/

// ==================== KESEHATAN LAYANAN (publik) ====================
Route::get('/health', fn () => response()->json([
    'success' => true,
    'message' => 'HealthHub API berjalan normal.',
    'data' => [
        'app' => config('app.name'),
        'environment' => app()->environment(),
        'php' => PHP_VERSION,
        'laravel' => app()->version(),
        'time' => now()->toIso8601String(),
    ],
]));

// ==================== AUTENTIKASI ====================
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

// ==================== KATALOG PUBLIK (lookup) ====================
Route::get('/doctors', [LookupController::class, 'doctors']);
Route::get('/doctors/{id}', [LookupController::class, 'doctorDetail'])->whereNumber('id');
Route::get('/polyclinics', [LookupController::class, 'polyclinics']);
Route::get('/specializations', [LookupController::class, 'specializations']);
Route::get('/services', [LookupController::class, 'services']);
Route::get('/schedules', [LookupController::class, 'schedules']);
Route::get('/articles', [LookupController::class, 'articles']);
Route::get('/articles/{slug}', [LookupController::class, 'articleDetail']);
Route::get('/settings', [LookupController::class, 'settings']);

// AI Triage boleh dipakai tamu (belum login) — red-flag emergency guard tetap aktif.
Route::post('/ai/triage', [AiTriageController::class, 'triage']);

// ==================== BUTUH LOGIN (Sanctum token) ====================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // --- Appointment & antrean ---
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::patch('/appointments', [AppointmentController::class, 'update']);
    Route::get('/queues', [AppointmentController::class, 'queues']);
    Route::post('/queues', [AppointmentController::class, 'transitionQueue'])
        ->middleware('role:nurse,doctor,admin,super-admin');

    // --- Rekam medis (EMR) & tanda vital ---
    Route::get('/medical-records', [ClinicalController::class, 'index']);
    Route::post('/medical-records', [ClinicalController::class, 'store'])
        ->middleware('role:doctor,admin,super-admin');
    Route::post('/medical-records/break-glass', [ClinicalController::class, 'breakGlass']);
    Route::get('/vital-signs', [ClinicalController::class, 'vitalSigns']);
    Route::post('/vital-signs', [ClinicalController::class, 'storeVitalSigns'])
        ->middleware('role:nurse,doctor,admin,super-admin');

    // --- Data pasien ---
    Route::get('/patients', [LookupController::class, 'patients'])
        ->middleware('role:admin,doctor,nurse,cashier,lab,pharmacist,super-admin');

    // --- Farmasi ---
    Route::get('/prescriptions', [PharmacyLabController::class, 'prescriptions']);
    Route::patch('/prescriptions', [PharmacyLabController::class, 'updatePrescriptionStatus'])
        ->middleware('role:pharmacist,admin,super-admin');
    Route::post('/prescriptions/dispense', [PharmacyLabController::class, 'dispense'])
        ->middleware('role:pharmacist,admin,super-admin');
    Route::get('/medicines', [PharmacyLabController::class, 'medicines']);
    Route::post('/medicines', [PharmacyLabController::class, 'manageMedicine'])
        ->middleware('role:pharmacist,admin,super-admin');

    // --- Laboratorium ---
    Route::get('/lab-orders', [PharmacyLabController::class, 'labOrders']);
    Route::get('/lab-test-types', [PharmacyLabController::class, 'labTestTypes']);
    Route::post('/lab-orders', [PharmacyLabController::class, 'saveLabResults'])
        ->middleware('role:lab,admin,super-admin');

    // --- Billing & pembayaran ---
    Route::get('/invoices', [FinanceAdminController::class, 'invoices']);
    Route::post('/invoices/pay', [FinanceAdminController::class, 'pay']);

    // --- Rawat inap (bed board) ---
    Route::get('/beds', [FinanceAdminController::class, 'beds']);
    Route::get('/inpatients', [FinanceAdminController::class, 'inpatients']);
    Route::post('/inpatients', [FinanceAdminController::class, 'manageInpatient'])
        ->middleware('role:nurse,admin,super-admin');

    // --- Notifikasi & audit ---
    Route::get('/notifications', [FinanceAdminController::class, 'notifications']);
    Route::post('/notifications', [FinanceAdminController::class, 'readNotifications']);
    Route::get('/audit-logs', [FinanceAdminController::class, 'auditLogs'])
        ->middleware('role:admin,super-admin');

    // --- Bootstrap data demo ---
    Route::post('/seed', [FinanceAdminController::class, 'seed']);
});
