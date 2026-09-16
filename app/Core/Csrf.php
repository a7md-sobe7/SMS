<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Cross-Site Request Forgery (CSRF) Guard
 */
class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Retrieve or generate the cryptographically secure CSRF token.
     */
    public static function getToken(): string
    {
        Session::start();
        $token = Session::get(self::TOKEN_KEY);

        if (!$token || !is_string($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::TOKEN_KEY, $token);
        }

        return $token;
    }

    /**
     * Validate an incoming token against the session token.
     */
    public static function validate(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $sessionToken = Session::get(self::TOKEN_KEY);
        if (!$sessionToken || !is_string($sessionToken)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}
