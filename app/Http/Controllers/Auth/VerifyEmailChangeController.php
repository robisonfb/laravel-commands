<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Trait\HttpResponses;
use Illuminate\Http\{JsonResponse, Request};

class VerifyEmailChangeController extends Controller
{
    use HttpResponses;

    public function verify(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->id);

        if (!hash_equals(sha1($request->new_email), $request->hash)) {
            return $this->error([], __('Invalid verification URL.'), 403);
        }

        $user->email             = $request->new_email;
        $user->email_verified_at = now();
        $user->save();

        return $this->success([], __('Email changed successfully.'));
    }

    public function sendVerification(Request $request): JsonResponse
    {
        $request->validate([
            'new_email' => ['required', 'email', 'unique:users,email'],
        ]);

        $user = $request->user();
        $user->sendEmailChangeNotification($request->new_email);

        return $this->success([], __('Verification link sent to new email.'));
    }
}
