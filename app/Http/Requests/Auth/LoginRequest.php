<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Core\FormRequest;
use App\DTOs\Auth\LoginDTO;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public route
    }

    public function rules(): array
    {
        return [
            'username' => 'required|min:3',
            'password' => 'required|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Please enter your username or email address.',
            'password.required' => 'Please provide your account password.',
        ];
    }

    public function toDTO(): LoginDTO
    {
        return LoginDTO::fromArray($this->validated());
    }
}
