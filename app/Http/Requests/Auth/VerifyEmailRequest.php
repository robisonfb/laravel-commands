<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class VerifyEmailRequest extends FormRequest
{
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
}
