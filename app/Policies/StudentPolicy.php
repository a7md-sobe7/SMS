<?php

declare(strict_types=1);

namespace App\Policies;

class StudentPolicy
{
    public function viewAny(?array $user): bool
    {
        return $user !== null && in_array($user['role'], ['admin', 'registrar', 'instructor'], true);
    }

    public function view(?array $user, mixed $student = null): bool
    {
        if ($user === null) {
            return false;
        }

        if (in_array($user['role'], ['admin', 'registrar', 'instructor'], true)) {
            return true;
        }

        // A student can view their own profile
        if ($user['role'] === 'student' && is_array($student) && isset($student['user_id'])) {
            return (int)$student['user_id'] === (int)$user['id'];
        }

        return $user['role'] === 'student';
    }

    public function create(?array $user): bool
    {
        return $user !== null && in_array($user['role'], ['admin', 'registrar'], true);
    }

    public function update(?array $user, mixed $student = null): bool
    {
        return $user !== null && in_array($user['role'], ['admin', 'registrar'], true);
    }

    public function delete(?array $user, mixed $student = null): bool
    {
        return $user !== null && $user['role'] === 'admin';
    }
}
