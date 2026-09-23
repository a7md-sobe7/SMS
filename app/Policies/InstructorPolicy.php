<?php

declare(strict_types=1);

namespace App\Policies;

class InstructorPolicy
{
    public function viewAny(?array $user): bool
    {
        return $user !== null;
    }

    public function create(?array $user): bool
    {
        return $user !== null && $user['role'] === 'admin';
    }

    public function update(?array $user, mixed $instructor = null): bool
    {
        return $user !== null && $user['role'] === 'admin';
    }
}
