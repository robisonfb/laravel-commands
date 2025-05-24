<?php

declare(strict_types = 1);

namespace App\Http\Requests\v1;

use App\Enums\HttpResponseStatus;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token'    => 'required',
            'email'    => ['required', 'email'],
            'password' => ['required', 'max:255', Password::min(8)->mixedCase()->letters()->numbers()->symbols()],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => HttpResponseStatus::ERROR,
                'message' => 'Invalid or missing data',
                'errors'  => $validator->errors()->toArray(),
            ], 400)
        );
    }
}
