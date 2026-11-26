<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->username)) {
            $this->merge([
                'username' => trim($this->username),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'max:120',
            ],

            'password' => [
                'required',
                'string',
                'max:72',
                function ($attribute, $value, $fail) {
                    if (
                        is_string($value)
                        && strlen($value) > 72
                    ) {
                        $fail(
                            'The password must not exceed 72 bytes.'
                        );
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' =>
                'Please enter your username.',

            'password.required' =>
                'Please enter your password.',
        ];
    }
}