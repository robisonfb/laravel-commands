<?php

use App\Http\Controllers\Auth\{AuthController, VerifyEmailController};
use App\Http\Controllers\Profile\ProfileController;

use Illuminate\Support\Facades\Route;

Route::prefix('/v1')
    ->group(function () {

        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

        Route::patch('/profile/update', [ProfileController::class, 'update'])->middleware('auth:sanctum');
        Route::patch('/profile/update-password', [ProfileController::class, 'updatePassword'])->middleware('auth:sanctum');
        Route::get('/profile', [ProfileController::class, 'show'])->middleware('auth:sanctum');

        Route::post('/email/resend-verification', [VerifyEmailController::class, 'resendVerificationEmail'])
            ->middleware(['auth:sanctum', 'throttle:6,1'])
            ->name('verification.send');

        Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verifyEmailAddress'])
            ->middleware(['signed'])
            ->name('verification.verify');

    });
