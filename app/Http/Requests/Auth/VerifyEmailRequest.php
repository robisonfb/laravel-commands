<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Trait\HttpResponses;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyEmailRequest extends FormRequest
{
    use HttpResponses;

    public function authorize(): bool
    {
        // Find the user by ID from the route parameter
        $user = User::find($this->route('id'));

        if (!$user) {
            return false;
        }

        // Store the user in the request for later use
        $this->setUserResolver(function () use ($user) {
            return $user;
        });

        // Verify the hash matches
        if (!hash_equals((string) $this->route('hash'), sha1($user->getEmailForVerification()))) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            //
        ];
    }

    protected function fulfill(): void
    {
        if (!$this->user()->hasVerifiedEmail()) {
            $this->user()->markEmailAsVerified();
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->error($validator->errors()->toArray(), __('Invalid or missing data'), 400)
        );
    }
}
