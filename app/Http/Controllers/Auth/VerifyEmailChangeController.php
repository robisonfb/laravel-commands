<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{Redirect, Validator};

/**
* @group Profile
 *
 */
class VerifyEmailChangeController extends Controller
{
    /**
     *  Send verification email for email change
     *
     * Send verification link to update user's email address with the new one
     *
     *
     * **Email Verification Process:**
     * After sending, a verification email is automatically sent. When the user
     * clicks the verification link, they will be redirected to the frontend with different
     * status depending on the result:
     *
     * - **Success**: `{FRONTEND_URL}/verify-email?status=success&message=Email+verified+successfully`
     * - **Error - Invalid ID**: `{FRONTEND_URL}/verify-email?status=error&message=Invalid+user+ID`
     * - **Error - User not found**: `{FRONTEND_URL}/verify-email?status=error&message=User+not+found`
     * - **Error - Invalid/expired link**: `{FRONTEND_URL}/verify-email?status=error&message=Invalid+or+expired+verification+link`
     *
     * **Important:** The backend manages the entire email verification process,
     * including redirects. The frontend must be prepared to receive the
     * `status` and `message` parameters in the URL and display appropriate messages.
     *
     * @authenticated
     * @bodyParam new_email string required New email address (max 255 characters, must be valid email format and unique). Must be a valid email format as per RFC standards and not already exist in the system. Example: newuser@example.com
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Verification link sent to new email.",
     *   "data": [],
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     *
     * @response 400 {
     *     "status": "error",
     *      "message": "Invalid or missing data",
     *      "data": {
     *          "new_email": [
     *              "The new email field must be a valid email address."
     *          ]
     *      }
     *  }
     *
     * @response 401 {
     *     "status": "unauthorized",
     *     "message": "Unauthorized access. Invalid or expired token.",
     *     "data": [],
     *     "meta": {
     *         "version": "1.0.0"
     *     }
     * }
     *
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'new_email' => ['required', 'email', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            return $this->error(
                $validator->errors(),
                'Invalid or missing data',
                400
            );
        }

        $user = $request->user();

        $user->sendEmailChangeNotification($request->new_email);

        return $this->success([], __('Verification link sent to new email.'));
    }

    /**
    * @hideFromAPIDocumentation
    */
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
