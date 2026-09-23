<?php

declare(strict_types=1);

/**
 * Application Core Configuration
 */
return [
    'name'      => env('APP_NAME', 'Student Management System'),
    'env'       => env('APP_ENV', 'production'),
    'debug'     => (bool)env('APP_DEBUG', false),
    'url'       => env('APP_URL', 'http://localhost:8000'),
    'timezone'  => env('APP_TIMEZONE', 'UTC'),
    
    // File upload settings
    'uploads'   => [
        'max_file_size'     => (int)env('MAX_FILE_SIZE_MB', 5) * 1024 * 1024,
        'avatar_path'       => dirname(__DIR__) . '/public/uploads/avatars',
        'allowed_avatars'   => explode(',', (string)env('ALLOWED_AVATAR_EXTENSIONS', 'jpg,jpeg,png,webp')),
        'allowed_mimes'     => [
            'image/jpeg',
            'image/png',
            'image/webp'
        ]
    ]
];
