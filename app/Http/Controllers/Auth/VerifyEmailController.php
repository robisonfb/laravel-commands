<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\{VerifyEmailRequest};
use App\Models\User;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\Redirect;

/**
 * @group Authentication
 *
 */
class VerifyEmailController extends Controller
{
    /**
     * Resend verification email
     *
     * Resends the email verification link to the authenticated user who has not yet verified their email address.
     * This endpoint allows users to request a new verification email if they didn't receive the original one,
     * if it expired, or if they need it sent to their registered email address again.
     *
     * **Process Flow:**
     * 1. Validates that the user is authenticated
     * 2. Checks if the email is already verified
     * 3. If not verified, sends a new verification email with a signed URL
     * 4. The verification email contains a link that redirects to the frontend with status parameters
     *
     * **Important:** This endpoint is rate-limited to prevent abuse (6 requests per minute).
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Verification link sent!",
     *   "data": [],
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     *
     * @response 400 {
     *   "status": "error",
     *   "message": "Email already verified",
     *   "data": [],
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     *
     * @response 401 {
     *     "status": "error",
     *     "message": "Unauthenticated. Please login to access this resource.",
     *     "data": null,
     *     "meta": {
     *         "version": "1.0.0"
     *     }
     * }
     *
     * @response 429 {
     *     "status": "error",
     *     "message": "Too many requests. Please try again later.",
     *     "data": null,
     *     "meta": {
     *         "version": "1.0.0"
     *     }
     * }
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
    * @hideFromAPIDocumentation
    */
    public function verifyEmailAddress(VerifyEmailRequest $request): RedirectResponse
    {
        /** @var string $redirectBase */
        $redirectBase = rtrim(config('app.frontend_url'), '/') . '/verify-email';

        $userId = $request->route('id');

        if (!is_numeric($userId)) {
            return Redirect::to($redirectBase . '?status=error&message=' . urlencode(__('Invalid user ID')));
        }

        /** @var User|null $user */
        $user = User::find($userId);

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
