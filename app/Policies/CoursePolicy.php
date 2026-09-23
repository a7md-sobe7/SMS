<?php

declare(strict_types=1);

namespace App\Policies;

class CoursePolicy
{
    public function viewAny(?array $user): bool
    {
        return $user !== null;
    }

    public function view(?array $user, mixed $course = null): bool
    {
        return $user !== null;
    }

    public function create(?array $user): bool
    {
        return $user !== null && in_array($user['role'], ['admin', 'registrar'], true);
    }

    public function update(?array $user, mixed $course = null): bool
    {
        return $user !== null && in_array($user['role'], ['admin', 'registrar'], true);
    }

    public function delete(?array $user, mixed $course = null): bool
    {
        return $user !== null && $user['role'] === 'admin';
    }
}
