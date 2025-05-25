<?php

namespace App\Http\Requests\Auth;

use App\Trait\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    use HttpResponses;

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
            $this->error($validator->errors()->toArray(), __('Invalid or missing data'), 400)
        );
    }
}
