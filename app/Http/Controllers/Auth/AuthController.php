<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\{LoginUserRequest, RegisterUserRequest};
use App\Models\User;
use Illuminate\Auth\Events\{PasswordReset, Registered};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Auth, Hash, Password};
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(LoginUserRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return $this->error('', __('Invalid credentials'), 401);
        }

        $user = User::where('email', $credentials['email'])->first();

        $user->access_token = $user->createToken("Token of " . $user->first_name)->plainTextToken;

        return $this->success($user, __('Successfully logged in'));

    }

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

        return $this->success($user, __('A verification email has been sent to your email address.'), 200);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success([], __($status), 200);
        }

        return $this->error([], __($status), 400);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

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

    public function logout()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->tokens()->delete();

        return $this->success([], __('User logged out successfully'));
    }
}
