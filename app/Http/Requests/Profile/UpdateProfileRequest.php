<?php

namespace App\Http\Requests\Profile;

use App\Trait\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    use HttpResponses;

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'max:255'],
            'last_name'  => ['sometimes', 'max:255'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponses(
            $this->error($validator->errors()->toArray(), __('Invalid or missing data'), 400)
        );
    }
}
