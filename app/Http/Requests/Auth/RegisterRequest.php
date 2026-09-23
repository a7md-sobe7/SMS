<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Core\FormRequest;
use App\DTOs\Auth\RegisterDTO;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|min:3|max:50|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role'     => 'in:student,instructor,registrar,admin'
        ];
    }

    public function toDTO(): RegisterDTO
    {
        return RegisterDTO::fromArray($this->validated());
    }
}
