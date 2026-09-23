<?php

declare(strict_types=1);

/**
 * Database Configuration (PDO / MySQL)
 * 
 * Returns database credentials and strict PDO connection options.
 * Uses env() helper for seamless compatibility with cloud hosting environments.
 */
return [
    'driver'    => env('DB_CONNECTION', 'mysql'),
    'host'      => env('DB_HOST', '127.0.0.1'),
    'port'      => (int)env('DB_PORT', 3306),
    'database'  => env('DB_DATABASE', 'student_management'),
    'username'  => env('DB_USERNAME', 'root'),
    'password'  => (string)env('DB_PASSWORD', ''),
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    
    // Strict PDO Options for enterprise security & reliable error handling
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]
];
