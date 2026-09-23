<?php

declare(strict_types=1);

namespace App\Events;

class UserLoggedInEvent
{
    public function __construct(
        public readonly int $userId,
        public readonly string $username,
        public readonly string $role,
        public readonly ?string $ipAddress = null
    ) {}
}
