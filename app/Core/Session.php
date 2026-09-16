<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Secure Session Manager
 * 
 * Enforces secure cookie policies, flash data storage, and session fixation prevention.
 */
class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $configPath = dirname(__DIR__, 2) . '/config/session.php';
        $config = file_exists($configPath) ? require $configPath : [];

        $lifetime = $config['lifetime'] ?? 7200;
        $path     = $config['path'] ?? '/';
        $domain   = $config['domain'] ?? '';
        $secure   = $config['secure'] ?? false;
        $httponly = $config['httponly'] ?? true;
        $samesite = $config['samesite'] ?? 'Strict';

        ini_set('session.gc_maxlifetime', (string)$lifetime);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);

        if (isset($config['name'])) {
            session_name($config['name']);
        }

        session_start();
        self::$started = true;

        // Manage flash data lifecycle
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
        if (!isset($_SESSION['_flash_next'])) {
            $_SESSION['_flash_next'] = [];
        }

        // Rotate flash arrays for the current request
        $_SESSION['_flash'] = $_SESSION['_flash_next'];
        $_SESSION['_flash_next'] = [];
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash_next'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION['_flash'][$key] ?? $default;
    }

    /**
     * Regenerate Session ID to mitigate Session Fixation attacks upon login.
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            session_destroy();
            self::$started = false;
        }
    }
}
