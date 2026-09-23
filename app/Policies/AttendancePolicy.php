<?php

declare(strict_types=1);

namespace App\Policies;

class AttendancePolicy
{
    public function viewAny(?array $user): bool
    {
        return $user !== null;
    }

    public function record(?array $user, mixed $attendance = null): bool
    {
        return $user !== null && in_array($user['role'], ['admin', 'instructor'], true);
    }
}
