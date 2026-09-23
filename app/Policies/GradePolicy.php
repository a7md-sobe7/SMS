<?php

declare(strict_types=1);

namespace App\Policies;

class GradePolicy
{
    public function viewAny(?array $user): bool
    {
        return $user !== null;
    }

    public function upsert(?array $user, mixed $grade = null): bool
    {
        return $user !== null && in_array($user['role'], ['admin', 'instructor'], true);
    }
}
