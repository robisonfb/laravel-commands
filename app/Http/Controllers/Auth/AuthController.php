<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\{LoginUserRequest, RegisterUserRequest};
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\{Auth, Hash};

/**
 * @group Authentication
 */
class AuthController extends Controller
{
    public function login(LoginUserRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return $this->error('', 'Invalid credentials', 401);
        }

        $user = User::where('email', $credentials['email'])->first();

        return $this->success([
            'user'         => $user,
            'access_token' => $user->createToken("Token of " . $user->name)->plainTextToken,
        ]);
    }

    public function register(RegisterUserRequest $request)
    {
        $request->validated($request->all());

        $user = User::create([
            "name"     => $request->name,
            "email"    => $request->email,
            "password" => Hash::make($request->password),
        ]);

        // Dispara o evento Registered que enviará o email de verificação
        event(new Registered($user));

        return $this->success([
            "user"         => $user,
            "access_token" => $user->createToken("Token of " . $user->name)->plainTextToken,
            "message"      => __('A verification email has been sent to your email address.'),
        ], "Registered!", 200);
    }

    /**
     * @authenticated
     */
    public function logout()
    {
        /** @var User $user */
        $user = Auth::user();
        $user->tokens()->delete();

        return $this->success([], 'User logged out successfully');
    }
}
