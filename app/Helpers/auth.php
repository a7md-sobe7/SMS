<?php

declare(strict_types=1);

use App\Core\Session;

if (!function_exists('auth_user')) {
    /**
     * Retrieve the currently authenticated user array or null.
     */
    function auth_user(): ?array
    {
        return Session::get('user');
    }
}

if (!function_exists('auth_id')) {
    /**
     * Retrieve the ID of the currently logged-in user.
     */
    function auth_id(): ?int
    {
        $user = auth_user();
        return isset($user['id']) ? (int)$user['id'] : null;
    }
}

if (!function_exists('auth_check')) {
    /**
     * Determine if the current visitor is authenticated.
     */
    function auth_check(): bool
    {
        return Session::has('user') && !empty(Session::get('user')['id']);
    }
}

if (!function_exists('auth_role')) {
    /**
     * Retrieve the role string of the current user ('admin', 'registrar', 'instructor', 'student').
     */
    function auth_role(): ?string
    {
        $user = auth_user();
        return $user['role'] ?? null;
    }
}

if (!function_exists('has_role')) {
    /**
     * Check if the authenticated user has one of the allowed roles.
     * 
     * @param string|array $roles Single role or array of allowed roles.
     */
    function has_role(string|array $roles): bool
    {
        if (!auth_check()) {
            return false;
        }

        $userRole = auth_role();
        if (is_array($roles)) {
            return in_array($userRole, $roles, true);
        }

        return $userRole === $roles;
    }
}
