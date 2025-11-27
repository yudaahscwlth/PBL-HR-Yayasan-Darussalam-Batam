<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LupaPasswordController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TempatKerjaController;
use App\Http\Controllers\Api\SosialMediaController;
use App\Http\Controllers\Api\JabatanController;
use App\Http\Controllers\Api\DepartemenController;
use App\Http\Controllers\Api\KategoriEvaluasiController as KategoriEvaluasiController;
use App\Http\Controllers\Api\TahunAjaranController;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Route;
use App\Models\User;

// Public routes
Route::get('/csrf-cookie', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'message' => 'CSRF token retrieved successfully'
    ]);
});
Route::post('/auth/login', [AuthController::class, 'login']);

// Lupa Password routes (public)
Route::prefix('lupa-password')->group(function () {
    Route::post('/request-otp', [LupaPasswordController::class, 'requestOtp']);
    Route::post('/verify-otp', [LupaPasswordController::class, 'verifyOtp']);
    Route::post('/reset-password', [LupaPasswordController::class, 'resetPassword']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // User routes
    Route::apiResource('users', UserController::class);

    // Profile routes
    Route::prefix('profile')->group(function () {
        Route::get('/complete', [ProfileController::class, 'getCompleteProfile']);
        Route::get('/sosial-media-platforms', [ProfileController::class, 'getSosialMediaPlatforms']);
        Route::put('/update', [ProfileController::class, 'updateProfile']);
        Route::get('/personal', [ProfileController::class, 'getPersonal']);
        Route::put('/personal', [ProfileController::class, 'updatePersonal']);
        Route::get('/work', [ProfileController::class, 'getWork']);
        Route::put('/work', [ProfileController::class, 'updateWork']);
    });

    // Attendance routes
    Route::prefix('attendance')->group(function () {
        Route::post('/checkin', [AttendanceController::class, 'checkIn']);
        Route::post('/checkout', [AttendanceController::class, 'checkOut']);
        Route::post('/manual', [AttendanceController::class, 'createManual']);
        Route::get('/today', [AttendanceController::class, 'getToday']);
        Route::get('/today-all', [AttendanceController::class, 'getTodayAll']);
        Route::get('/history', [AttendanceController::class, 'getHistory']);
        Route::get('/{id}/log', [AttendanceController::class, 'getLog']);
    });

    // Leave routes
    Route::apiResource('leave', LeaveController::class);
    Route::post('/leave/{id}/approve', [LeaveController::class, 'approve']);
    Route::post('/leave/{id}/reject', [LeaveController::class, 'reject']);
    Route::post('/leave/{id}/approve-kepsek', [LeaveController::class, 'approveKepsek']);
    Route::post('/leave/{id}/reject-kepsek', [LeaveController::class, 'rejectKepsek']);
    Route::post('/leave/{id}/approve-dirpen', [LeaveController::class, 'approveDirpen']);
    Route::post('/leave/{id}/reject-dirpen', [LeaveController::class, 'rejectDirpen']);

    // Evaluation routes
    Route::get('/evaluation/personal', [EvaluationController::class, 'getPersonal']);
    Route::post('/evaluation/check-exists', [EvaluationController::class, 'checkExists']);
    Route::apiResource('evaluation', EvaluationController::class);

    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getDashboardStats']);
        Route::get('/personal', [DashboardController::class, 'getPersonalDashboardStats']);
        Route::get('/attendance-summary', [DashboardController::class, 'getTodayAttendanceSummary']);
        Route::get('/users', [DashboardController::class, 'getUsersWithJobAndDepartment']);
        Route::get('/job-titles', [DashboardController::class, 'getJobTitles']);
        Route::get('/departments', [DashboardController::class, 'getDepartments']);
    });

    // Tempat Kerja routes
    Route::apiResource('tempat-kerja', TempatKerjaController::class);

    // Sosial Media routes
    Route::apiResource('sosial-media', SosialMediaController::class);

    // Jabatan routes
    Route::apiResource('jabatan', JabatanController::class);

    // Departemen routes
    Route::prefix('departemen')->group(function () {
        Route::get('/', [DepartemenController::class, 'index']);
        Route::get('/{id}', [DepartemenController::class, 'show']);
        Route::post('/', [DepartemenController::class, 'store']);
        Route::put('/{id}', [DepartemenController::class, 'update']);
        Route::delete('/{id}', [DepartemenController::class, 'destroy']);
        Route::get('/users/list', [DepartemenController::class, 'getUsers']);
    });

    // Kategori Evaluasi routes
    Route::get('/kategori-evaluasi', [KategoriEvaluasiController::class, 'index']);
    Route::post('/kategori-evaluasi', [KategoriEvaluasiController::class, 'store']);
    Route::put('/kategori-evaluasi/{kategoriEvaluasi}', [KategoriEvaluasiController::class, 'update']);
    Route::delete('/kategori-evaluasi/{kategoriEvaluasi}', [KategoriEvaluasiController::class, 'destroy']);

    // Tahun Ajaran routes
    Route::get('/tahun-ajaran', [\App\Http\Controllers\Api\TahunAjaranController::class, 'index']);
    Route::post('/tahun-ajaran', [\App\Http\Controllers\Api\TahunAjaranController::class, 'store']);
    Route::get('/tahun-ajaran/{tahunAjaran}', [\App\Http\Controllers\Api\TahunAjaranController::class, 'show']);
    Route::put('/tahun-ajaran/{tahunAjaran}', [\App\Http\Controllers\Api\TahunAjaranController::class, 'update']);
    Route::delete('/tahun-ajaran/{tahunAjaran}', [\App\Http\Controllers\Api\TahunAjaranController::class, 'destroy']);
    Route::apiResource('tahun-ajaran', TahunAjaranController::class);
});


// Legacy route (keep for backward compatibility)
Route::get('/users', function () {
    return UserResource::collection(User::all());
});
