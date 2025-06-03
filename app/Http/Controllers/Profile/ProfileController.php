<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\{UpdateProfileRequest};
use App\Models\User;
use Illuminate\Http\{JsonResponse, Request};

/**
 * @group Profile
 *
 * APIs for user profile management
 */
class ProfileController extends Controller
{
    /**
     * Get user profile
     *
     * Get authenticated user profile information
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Profile fetched successfully",
     *   "data": {
     *     "id": 1,
     *     "first_name": "John",
     *     "last_name": "Doe",
     *     "email": "user@example.com",
     *     "email_verified_at": "2024-01-01T00:00:00.000000Z",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   },
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
     */
    public function show(Request $request): JsonResponse
    {
        return $this->success($request->user(), __('Profile fetched successfully'), 200);
    }

    /**
     * Update user profile
     *
     * Update authenticated user profile information
     *
     * @authenticated
     *
     * @bodyParam first_name string User first name. Example: John
     * @bodyParam last_name string User last name. Example: Doe
     *
     * @response 200 {
     *   "status": "success",
     *   "message": "Profile updated successfully",
     *   "data": {
     *     "id": 1,
     *     "first_name": "John",
     *     "last_name": "Doe",
     *     "email": "user@example.com",
     *     "email_verified_at": "2024-01-01T00:00:00.000000Z",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   },
     *   "meta": {
     *     "version": "1.0.0"
     *   }
     * }
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
