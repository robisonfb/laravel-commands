<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Http\Requests\Auth\{VerifyEmailRequest};
use App\Models\User;
use Illuminate\Http\{JsonResponse, Request};

/**
 * @group Email verification
 */
class VerifyEmailController extends Controller
{
    /**
     * Reenvia o email de verificação para o usuário autenticado
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->error([], __('Email already verified'), 400);
        }

        $user->sendEmailVerificationNotification();

        return $this->success([], __('Verification link sent!'), 200);
    }

    /**
     * Marca o email do usuário como verificado
     */
    public function verifyEmailAddress(VerifyEmailRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->success([], __('Email already verified'), 400);
        }

        $user->markEmailAsVerified();

        return $this->success([], __('Email verified successfully'), 200);
    }
}
