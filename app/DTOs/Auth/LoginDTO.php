<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\DTOs\BaseDTO;

class LoginDTO extends BaseDTO
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
        public readonly bool $remember = false
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            username: trim((string)($data['username'] ?? '')),
            password: (string)($data['password'] ?? ''),
            remember: (bool)($data['remember'] ?? false)
        );
    }
}
