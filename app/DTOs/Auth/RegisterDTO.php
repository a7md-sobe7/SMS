<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\DTOs\BaseDTO;

class RegisterDTO extends BaseDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $password,
        public readonly string $role = 'student',
        public readonly string $status = 'active'
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            username: trim((string)($data['username'] ?? '')),
            email: strtolower(trim((string)($data['email'] ?? ''))),
            password: (string)($data['password'] ?? ''),
            role: (string)($data['role'] ?? 'student'),
            status: (string)($data['status'] ?? 'active')
        );
    }
}
