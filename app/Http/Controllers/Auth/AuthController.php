<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\{ForgotPasswordRequest, ResetPasswordRequest};
use App\Http\Requests\Auth\{LoginUserRequest, RegisterUserRequest};
use App\Http\Resources\Auth\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\{PasswordReset, Registered};
use Illuminate\Http\{JsonResponse};
use Illuminate\Support\Facades\{Auth, Hash, Password};
use Illuminate\Support\Str;

/**
 * @group Authentication
 *
 * APIs for user authentication
 */
class AuthController extends Controller
{
    /**
     * Login user
     *
     * Authenticate a user and return an access token.
     *
     * @bodyParam email string required User email address (max 255 characters, must be valid email format). Must be a valid email format as per RFC standards. Example: user@example.com
     * @bodyParam password string required User password (8-50 characters, must contain: uppercase letter, lowercase letter, number, and symbol). Password must include mixed case letters, numbers, and special symbols for security. Example: Password@123
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "User logged in successfully",
     *   "data": {
     *     "id": 1,
     *     "first_name": "John",
     *     "last_name": "Doe",
     *     "email": "user@example.com",
     *     "access_token": "1|token_string"
     *   },
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     *
     * @response 401 {
     *   "status": "error",
     *   "message": "Invalid credentials",
     *   "data": [],
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     */
    public function login(LoginUserRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return $this->error([], __('Invalid credentials'), 401);
        }

        $user = User::where('email', $credentials['email'])->first();

        $user->access_token = $user->createToken("Token of " . $user->first_name)->plainTextToken;

        return $this->success(
            new UserResource($user),
            __('Successfully logged in'),
            200
        );
    }

    /**
     * Register user
     *
     * Create a new user account and initiate the email verification process.
     *
     * **Registration Process Flow:**
     * 1. User provides registration data (first name, last name, email, password)
     * 2. Data is validated according to established rules
     * 3. User account is created in the system
     * 4. A verification email is automatically sent to the provided address
     * 5. User receives an access token for immediate API use
     * 6. User must verify their email by clicking the received link
     *
     * **Email Verification Process:**
     * After registration, a verification email is automatically sent. When the user
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
     * @bodyParam first_name string required User first name (3-50 characters, only letters and spaces allowed). Example: John
     * @bodyParam last_name string required User last name (3-50 characters, only letters and spaces allowed). Example: Doe
     * @bodyParam email string required User email address (max 255 characters, must be valid email format and unique). Must be a valid email format as per RFC standards. Example: user@example.com
     * @bodyParam password string required User password (8-50 characters, must contain: uppercase letter, lowercase letter, number, and symbol). Password must include mixed case letters, numbers, and special symbols for security. Example: Password@123
     * @bodyParam password_confirmation string required Password confirmation (must match the password field exactly). Example: Password@123
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "A verification email has been sent to your email address.",
     *   "data": {
     *     "id": 1,
     *     "first_name": "John",
     *     "last_name": "Doe",
     *     "email": "user@example.com",
     *     "access_token": "1|token_string"
     *   },
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     *
     * @response 400 {
     *   "status": "error",
     *   "message": "Invalid or missing data",
     *   "data": {
     *     "first_name": [
     *       "The first name field is required."
     *     ],
     *     "last_name": [
     *       "The last name field is required."
     *     ],
     *     "email": [
     *       "The email field is required."
     *     ],
     *     "password": [
     *       "The password field is required."
     *     ]
     *   },
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     *
     * @response 400 {
     *   "status": "error",
     *   "message": "Invalid or missing data",
     *   "data": {
     *     "email": [
     *       "The email has already been taken."
     *     ]
     *   },
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     */

    public function register(RegisterUserRequest $request)
    {
        $request->validated($request->all());

        $user = User::create([
            "first_name" => $request->first_name,
            "last_name"  => $request->last_name,
            "email"      => $request->email,
            "password"   => Hash::make($request->password),
        ]);

        // Dispara o evento que enviará o email de verificação
        event(new Registered($user));

        $user->access_token = $user->createToken("Token of " . $user->first_name)->plainTextToken;

        return $this->success(
            new UserResource($user),
            __('A verification email has been sent to your email address.'),
            200
        );
    }

    /**
     * Send reset link
     *
     * Send a password reset link to the user's email address. This endpoint initiates the password reset process
     * by generating a secure token and sending it via email to the user. The token is valid for a limited time
     * and can be used with the ```api/v1/auth/reset-password``` endpoint to complete the password reset process.
     *
     *
     * @bodyParam email string required User email address (max 255 characters, must be valid email format and unique). Must be a valid email format as per RFC standards. Example: user@example.com
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Password reset link sent successfully",
     *   "data": [],
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     *
     * @response 404 {
     *       "status": "error",
     *       "message": "User not found",
     *       "data": [],
     *       "meta": {
     *           "version": "1.0.0"
     *       }
     *   }
     *
     * @response 422 {
     *      "status": "error",
     *      "message": "Invalid or missing data",
     *      "data": {
     *          "email": [
     *              "The email field is required."
     *          ]
     *      },
     *      "meta": {
     *          "version": "1.0.0"
     *      }
     *   }
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $request->validated($request->all());

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success([], __('Password reset link sent successfully'), 200);
        }

        return $this->error([], __('User not found'), 404);
    }

    /**
     * Reset password
     *
     * Reset the user's password using the token received via email.
     *
     * **Complete password reset flow:**
     * 1. User requests reset through ``api/v1/auth/forgot-password`` route by providing only email
     * 2. System sends an email containing a link with reset token and user email
     * 3. User accesses the link and uses this route to set new password
     * 4. This route requires the token (sent in email), email and new password to complete the process
     *
     * @bodyParam token string required Password reset token received via email from ```api/v1/auth/forgot-password``` endpoint. This token is automatically included in the reset link sent to the user's email. Example: abc123token456def
     * @bodyParam email string required User email address (max 255 characters, must be valid email format and match the email used in ```api/v1/auth/forgot-password```). This email is automatically included in the reset link sent from forgotPassword endpoint. Must be a valid email format as per RFC standards. Example: user@example.com
     * @bodyParam password string required New user password (8-255 characters, must contain: uppercase letter, lowercase letter, number, and symbol). Password must include mixed case letters, numbers, and special symbols for security. Example: NewPassword@123
     * @bodyParam password_confirmation string required Password confirmation (must match the password field exactly). Example: NewPassword@123
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Password reset successfully",
     *   "data": null,
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     *
     * @response 400 {
     *   "status": "error",
     *   "message": "Invalid or missing data",
     *   "data": {
     *     "password": [
     *       "The password field is required."
     *     ]
     *   },
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     *
     * @response 400 {
     *   "status": "error",
     *   "message": "Invalid token",
     *   "data": null,
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     *
     * @response 404 {
     *   "status": "error",
     *   "message": "User not found",
     *   "data": null,
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $request->validated($request->all());

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success([], __($status));
        }

        return $this->error([], __($status), 400);
    }

    /**
     * Logout user
     *
     * Logout authenticated user and revoke tokens
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "User logged out successfully",
     *   "data": [],
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
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
    public function logout()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->tokens()->delete();

        return $this->success([], __('User logged out successfully'));
    }
}
