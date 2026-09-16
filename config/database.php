<?php

declare(strict_types=1);

/**
 * Database Configuration (PDO / MySQL)
 * 
 * Returns database credentials and strict PDO connection options.
 */
return [
    'driver'    => $_ENV['DB_CONNECTION'] ?? 'mysql',
    'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port'      => (int)($_ENV['DB_PORT'] ?? 3306),
    'database'  => $_ENV['DB_DATABASE'] ?? 'student_management',
    'username'  => $_ENV['DB_USERNAME'] ?? 'root',
    'password'  => $_ENV['DB_PASSWORD'] ?? '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    
    // Strict PDO Options for enterprise security & reliable error handling
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,        // Throw PDOException on failure
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,              // Return associative arrays by default
        PDO::ATTR_EMULATE_PREPARES   => false,                         // Use native prepared statements (prevents SQL injection)
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]
];
