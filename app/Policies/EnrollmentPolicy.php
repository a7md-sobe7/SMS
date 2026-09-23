<?php

declare(strict_types=1);

namespace App\Policies;

class EnrollmentPolicy
{
    public function viewAny(?array $user): bool
    {
        return $user !== null;
    }

    public function enroll(?array $user, mixed $enrollment = null): bool
    {
        return $user !== null && in_array($user['role'], ['admin', 'registrar', 'student'], true);
    }

    public function drop(?array $user, mixed $enrollment = null): bool
    {
        return $user !== null && in_array($user['role'], ['admin', 'registrar', 'student'], true);
    }
}
