<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\{UpdatePasswordRequest, UpdateProfileRequest};
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @group Profile
 */
class ProfileController extends Controller
{
    /**
     * @authenticated
     */
    public function show(Request $request): JsonResponse
    {
        return $this->success([
            'user' => $request->user('sanctum'),
        ]);
    }

    /**
     * @authenticated
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user('sanctum');

        $user->update($validated);

        return $this->success([
            'user' => $user,
        ], __('Profile updated successfully'), 200);
    }

    /**
     * @authenticated
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user('sanctum');

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        // Revoga todos os tokens para forçar o login novamente com a nova senha
        $user->tokens()->delete();

        return $this->success([], 'Senha atualizada com sucesso', 200);
    }
}
