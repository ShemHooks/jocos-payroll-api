<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $byteLimit = function ($attribute, $value, $fail) {
            if (is_string($value) && strlen($value) > 72) {
                $fail(
                    'The password must not exceed 72 bytes.'
                );
            }
        };

        return [
            'current_password' => [
                'required',
                'string',
                'max:72',
                $byteLimit,
            ],

            'new_password' => [
                'required',
                'string',
                'max:72',
                'different:current_password',

                Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),

                $byteLimit,
            ],

            'confirm_password' => [
                'required',
                'string',
                'same:new_password',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' =>
                'Please enter your current password.',

            'new_password.required' =>
                'Please enter your new password.',

            'new_password.different' =>
                'Your new password must be different from your current password.',

            'confirm_password.required' =>
                'Please confirm your new password.',

            'confirm_password.same' =>
                'Password confirmation does not match.',
        ];
    }
}