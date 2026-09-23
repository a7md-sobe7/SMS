<?php

declare(strict_types=1);

if (!function_exists('env')) {
    /**
     * Retrieve an environment variable with type parsing and default fallback.
     * Searches $_ENV, $_SERVER, and getenv() for maximum compatibility with cloud hosts.
     */
    function env(string $key, mixed $default = null): mixed
    {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($val === false || $val === null || $val === '') {
            return $default;
        }

        if (is_bool($val) || is_numeric($val)) {
            return $val;
        }

        switch (strtolower(trim((string)$val))) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $val;
    }
}
