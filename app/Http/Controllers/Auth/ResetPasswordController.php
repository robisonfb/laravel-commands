<?php

declare(strict_types = 1);

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\v1\{EmailRequest, ResetPasswordRequest};
use Illuminate\Http\JsonResponse;

/**
 * @group Reset password
 */
class ResetPasswordController extends Controller
{
    public function notify(EmailRequest $request): JsonResponse
    {

    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {

    }
}
