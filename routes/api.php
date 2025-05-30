<?php

use App\Http\Controllers\Auth\{AuthController, VerifyEmailChangeController, VerifyEmailController};
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

// Health Check Route
Route::get('/', function () {
    return response()->json([
        'status' => 200,
        'message' => 'Welcome to the ' . config('app.name') . ' API, it is working!',
        'version' => config('app.version'),
        'clientName' => strtolower(str_replace(' ', '', config('app.name'))),
    ]);
});

// API Version 1
Route::prefix('v1')->group(function () {

    // System Routes
    if (config('app.log_viewer')) {
        Route::get('logs', [LogViewerController::class, 'index']);
    }

    // Authentication Routes (Guest only)
    Route::prefix('auth')->middleware('guest')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:6,1');
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:6,1');

        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
    });

    // Logout Route (Authenticated only)
    Route::post('auth/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');

    // Email Verification Routes
    Route::prefix('email')->group(function () {
        // Guest routes (signed URLs)
        Route::middleware('signed')->group(function () {
            Route::get('verify/{id}/{hash}', [VerifyEmailController::class, 'verifyEmailAddress'])->name('verification.verify');
            Route::get('change/verify/{id}/{hash}', [VerifyEmailChangeController::class, 'verifyChangeEmail'])->name('email.change.verify');
        });

        // Authenticated routes
        Route::middleware(['auth:sanctum', 'throttle:6,1'])->group(function () {
            Route::post('resend-verification', [VerifyEmailController::class, 'resendVerificationEmail'])->name('verification.send');
            Route::post('change', [VerifyEmailChangeController::class, 'sendVerificationEmail'])->name('email.change');
        });
    });

    // Profile Routes (Authenticated only)
    Route::prefix('profile')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::patch('update', [ProfileController::class, 'update']);
        Route::patch('update-password', [ProfileController::class, 'updatePassword']);
    });

});
