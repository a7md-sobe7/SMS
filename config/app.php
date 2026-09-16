<?php

declare(strict_types=1);

/**
 * Application Core Configuration
 * 
 * Returns array of general application settings parsed from environment variables.
 */
return [
    'name'      => $_ENV['APP_NAME'] ?? 'Student Management System',
    'env'       => $_ENV['APP_ENV'] ?? 'production',
    'debug'     => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'       => $_ENV['APP_URL'] ?? 'http://localhost:8000',
    'timezone'  => $_ENV['APP_TIMEZONE'] ?? 'UTC',
    
    // File upload settings
    'uploads'   => [
        'max_file_size'     => (int)($_ENV['MAX_FILE_SIZE_MB'] ?? 5) * 1024 * 1024, // In Bytes
        'avatar_path'       => dirname(__DIR__) . '/public/uploads/avatars',
        'allowed_avatars'   => explode(',', $_ENV['ALLOWED_AVATAR_EXTENSIONS'] ?? 'jpg,jpeg,png,webp'),
        'allowed_mimes'     => [
            'image/jpeg',
            'image/png',
            'image/webp'
        ]
    ]
];
