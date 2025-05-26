<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use App\Http\Requests\Auth\{VerifyEmailRequest};
use App\Models\User;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\Redirect;

class VerifyEmailController extends Controller
{
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

    public function verifyEmailAddress(VerifyEmailRequest $request): RedirectResponse
    {
        /** @var string $redirectBase */
        $redirectBase = rtrim(config('app.frontend_url'), '/') . '/verify-email';

        /** @var User $user */
        $user = $request->user();

        if (!$user) {
            return Redirect::to($redirectBase . '?status=error&message=' . urlencode(__('User not found')));
        }

        if ($user->hasVerifiedEmail()) {
            return Redirect::to($redirectBase . '?status=error&message=' . urlencode(__('Email already verified')));
        }

        if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return Redirect::to($redirectBase . '?status=error&message=' . urlencode(__('Invalid or expired verification link')));
        }

        if ($user->markEmailAsVerified()) {

            $user->sendEmailWelcomeNotification($user->email);

            return Redirect::to($redirectBase . '?status=success&message=' . urlencode(__('Email verified successfully')));
        }

        return Redirect::to($redirectBase . '?status=error&message=' . urlencode(__('Unable to verify email')));

    }
}
