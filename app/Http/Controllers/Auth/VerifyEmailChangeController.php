<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\Redirect;

class VerifyEmailChangeController extends Controller
{

    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $request->validate([
            'new_email' => ['required', 'email', 'unique:users,email'],
        ]);

        $user = $request->user();

        $user->sendEmailChangeNotification($request->new_email);

        return $this->success([], __('Verification link sent to new email.'));
    }

    public function verifyChangeEmail(Request $request): RedirectResponse
    {

        /** @var string $redirectBase */
        $redirectBase = rtrim(config('app.frontend_url'), '/') . '/verify-change-email';

        $userId = $request->route('id');
        if (!is_numeric($userId)) {
            return Redirect::to($redirectBase . '?status=error&message=' . urlencode(__('Invalid user ID')));
        }

        /** @var User|null $user */
        $user = User::find($userId);

        if (!$user) {
            return Redirect::to($redirectBase . '?status=error&message=' . urlencode(__('User not found')));
        }

        if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return Redirect::to($redirectBase . '?status=error&message=' . urlencode(__('Invalid or expired verification link')));
        }

        $user->email             = $request->new_email;
        $user->email_verified_at = now();
        $user->save();

        return Redirect::to($redirectBase . '?status=success&message=' . urlencode(__('Email changed successfully.')));

    }

}
