<?php

declare(strict_types=1);

if (!function_exists('e')) {
    /**
     * Escape HTML special characters to prevent Cross-Site Scripting (XSS).
     * 
     * @param string|null $value
     * @return string
     */
    function e(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Retrieve the active CSRF token for forms.
     */
    function csrf_token(): string
    {
        return \App\Core\Csrf::getToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate an HTML hidden input containing the CSRF token.
     */
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_csrf_token" value="' . e($token) . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve flashed previous input value after a failed form submission.
     */
    function old(string $key, mixed $default = ''): mixed
    {
        $oldInputs = \App\Core\Session::getFlash('_old_input') ?? [];
        return $oldInputs[$key] ?? $default;
    }
}
