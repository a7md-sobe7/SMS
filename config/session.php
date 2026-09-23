<?php

declare(strict_types=1);

/**
 * Session Security Configuration
 */
return [
    'name'           => 'SMS_SESSION_ID',
    'lifetime'       => (int)env('SESSION_LIFETIME', 7200),
    'path'           => '/',
    'domain'         => '',
    'secure'         => (bool)env('SESSION_SECURE_COOKIE', false),
    'httponly'       => (bool)env('SESSION_HTTPONLY', true),
    'samesite'       => (string)env('SESSION_SAMESITE', 'Lax'),
    'gc_maxlifetime' => (int)env('SESSION_LIFETIME', 7200),
];
