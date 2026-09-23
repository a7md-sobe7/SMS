<?php

declare(strict_types=1);

namespace App\Policies;

class DepartmentPolicy
{
    public function viewAny(?array $user): bool
    {
        return true;
    }

    public function create(?array $user): bool
    {
        return $user !== null && $user['role'] === 'admin';
    }

    public function update(?array $user, mixed $dept = null): bool
    {
        return $user !== null && $user['role'] === 'admin';
    }

    public function delete(?array $user, mixed $dept = null): bool
    {
        return $user !== null && $user['role'] === 'admin';
    }
}
