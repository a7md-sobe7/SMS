<?php

declare(strict_types=1);

namespace App\Core;

/**
 * PSR-4 Compliant Native Autoloader Fallback
 * 
 * Provides native class autoloading following the PSR-4 standard.
 * Enables zero-dependency bootstrapping if composer dump-autoload has not yet been executed.
 */
class Autoloader
{
    /**
     * An associative array where key is namespace prefix and value is base directory.
     * @var array<string, string>
     */
    private static array $prefixes = [];

    /**
     * Register the autoloader with SPL autoloader stack.
     */
    public static function register(): void
    {
        spl_autoload_register([self::class, 'loadClass']);

        // Default application namespace mappings
        self::addNamespace('App\\', dirname(__DIR__));
        self::addNamespace('Database\\Seeders\\', dirname(__DIR__, 2) . '/database/seeders');
        self::addNamespace('Tests\\', dirname(__DIR__, 2) . '/tests');

        // Load procedural helper functions
        self::loadHelpers();
    }

    /**
     * Add a base directory for a namespace prefix.
     */
    public static function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR . '/') . '/';

        self::$prefixes[$prefix] = $baseDir;
    }

    /**
     * Autoload callback invoked by PHP engine when encountering an undefined class.
     */
    public static function loadClass(string $class): bool
    {
        foreach (self::$prefixes as $prefix => $baseDir) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require $file;
                return true;
            }
        }

        return false;
    }

    /**
     * Manually include procedural helper scripts.
     */
    private static function loadHelpers(): void
    {
        $helpers = [
            dirname(__DIR__) . '/Helpers/env.php',
            dirname(__DIR__) . '/Helpers/auth.php',
            dirname(__DIR__) . '/Helpers/response.php',
            dirname(__DIR__) . '/Helpers/sanitize.php',
        ];

        foreach ($helpers as $helper) {
            if (file_exists($helper)) {
                require_once $helper;
            }
        }
    }
}
