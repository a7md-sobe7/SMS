<?php

declare(strict_types=1);

/**
 * Session Security Configuration
 * 
 * Enforces secure cookie attributes and session lifetime defaults.
 */
return [
    'name'           => 'SMS_SESSION_ID',
    'lifetime'       => (int)($_ENV['SESSION_LIFETIME'] ?? 7200), // 2 hours
    'path'           => '/',
    'domain'         => '',
    'secure'         => filter_var($_ENV['SESSION_SECURE_COOKIE'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'httponly'       => filter_var($_ENV['SESSION_HTTPONLY'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'samesite'       => $_ENV['SESSION_SAMESITE'] ?? 'Strict',
    'gc_maxlifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
];
