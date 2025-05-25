<?php

namespace App\Http\Requests\Auth;

use App\Trait\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    use HttpResponses;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:5', 'max:50', 'regex:/^[a-zA-Z\s]+$/'],
            'last_name'  => ['required', 'string', 'min:5', 'max:50', 'regex:/^[a-zA-Z\s]+$/'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'   => ['required', 'max:255', Password::min(8)->mixedCase()->letters()->numbers()->symbols()],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->error($validator->errors()->toArray(), __('Invalid or missing data'), 400)
        );
    }
}
