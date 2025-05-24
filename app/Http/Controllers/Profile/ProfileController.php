<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\{UpdateProfileRequest};
use App\Models\User;
use Illuminate\Http\{JsonResponse, Request};

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
        return $this->success($request->user());
    }

    /**
     * @authenticated
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $user->update($validated);

        return $this->success($user, __('Profile updated successfully'), 200);
    }

}
