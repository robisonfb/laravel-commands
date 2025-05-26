<?php

use App\Http\Controllers\Auth\{AuthController, VerifyEmailChangeController, VerifyEmailController};
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

Route::prefix('/v1')
    ->group(function () {

        if (config('app.log_viewer')) {
            Route::get('/logs', [LogViewerController::class, 'index']);
        }

        // ROTA: Auth
        Route::post('/auth/login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:6,1']);
        Route::post('/auth/register', [AuthController::class, 'register'])->middleware(['guest', 'throttle:6,1']);
        Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

        Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('guest');
        Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset')->middleware('guest');

        // ROTA: e-mail
        Route::post('/email/resend-verification', [VerifyEmailController::class, 'resendVerificationEmail'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('verification.send');

        Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verifyEmailAddress'])
            ->middleware(['signed'])
            ->name('verification.verify');

        Route::post('/email/change', [VerifyEmailChangeController::class, 'sendVerification'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('email.change');

        Route::get('/email/change/verify/{id}/{hash}', [VerifyEmailChangeController::class, 'verifyChangeEmail'])
            ->middleware('signed')
            ->name('email.change.verify');

        // ROTA: Profile
        Route::patch('/profile/update', [ProfileController::class, 'update'])->middleware('auth:sanctum');
        Route::patch('/profile/update-password', [ProfileController::class, 'updatePassword'])->middleware('auth:sanctum');
        Route::get('/profile', [ProfileController::class, 'show'])->middleware('auth:sanctum');

    });
