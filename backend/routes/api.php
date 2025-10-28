<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\EvaluationController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TempatKerjaController;
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
        Route::get('/personal', [ProfileController::class, 'getPersonal']);
        Route::put('/personal', [ProfileController::class, 'updatePersonal']);
        Route::get('/work', [ProfileController::class, 'getWork']);
        Route::put('/work', [ProfileController::class, 'updateWork']);
    });

    // Attendance routes
    Route::prefix('attendance')->group(function () {
        Route::post('/checkin', [AttendanceController::class, 'checkIn']);
        Route::post('/checkout', [AttendanceController::class, 'checkOut']);
        Route::get('/today', [AttendanceController::class, 'getToday']);
        Route::get('/history', [AttendanceController::class, 'getHistory']);
    });

    // Leave routes
    Route::apiResource('leave', LeaveController::class);
    Route::post('/leave/{id}/approve', [LeaveController::class, 'approve']);
    Route::post('/leave/{id}/reject', [LeaveController::class, 'reject']);

    // Evaluation routes
    Route::apiResource('evaluation', EvaluationController::class);
    Route::get('/evaluation/personal', [EvaluationController::class, 'getPersonal']);

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
});


// Legacy route (keep for backward compatibility)
Route::get('/users', function () {
    return UserResource::collection(User::all());
});
