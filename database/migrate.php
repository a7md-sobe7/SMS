<?php

declare(strict_types=1);

/**
 * CLI Database Migration & Seeding Runner
 * Compatible with Local Environments, Docker, and Cloud Hosts (Laravel Cloud / AWS / Heroku)
 * 
 * Usage:
 *   php database/migrate.php
 */

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

// Load Environment Configuration from .env if present (Local), otherwise uses Cloud OS getenv()
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
    }
}

$dbConfig = require dirname(__DIR__) . '/config/database.php';

$host   = $dbConfig['host'];
$port   = $dbConfig['port'];
$dbName = $dbConfig['database'];
$user   = $dbConfig['username'];
$pass   = $dbConfig['password'];

echo "==============================================================\n";
echo " Student Management System - Database Migration Runner\n";
echo "==============================================================\n";

try {
    // 1. Attempt direct connection to target database (Standard for Cloud DBs)
    echo "[*] Connecting to database `{$dbName}` on {$host}:{$port}...\n";
    $targetDsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
    
    try {
        $pdo = new PDO($targetDsn, $user, $pass, $dbConfig['options']);
    } catch (PDOException $e) {
        // Fallback for local development if database does not exist yet
        if ($e->getCode() == 1049 || str_contains($e->getMessage(), 'Unknown database')) {
            echo "[*] Database `{$dbName}` does not exist. Attempting creation...\n";
            $rootDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $rootPdo = new PDO($rootDsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $pdo = new PDO($targetDsn, $user, $pass, $dbConfig['options']);
        } else {
            throw $e;
        }
    }

    // 2. Execute Schema SQL
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new RuntimeException("Schema file not found at: {$schemaFile}");
    }

    echo "[*] Executing schema DDL from schema.sql...\n";
    $sql = file_get_contents($schemaFile);
    $pdo->exec($sql);
    echo "[+] Database schema created successfully!\n";

    // 3. Run Seeders
    echo "[*] Running Database Seeders...\n";
    \Database\Seeders\DatabaseSeeder::run($pdo);

    echo "\n==============================================================\n";
    echo " [SUCCESS] Migration & Seeding completed successfully!\n";
    echo "==============================================================\n";

} catch (PDOException $e) {
    echo "\n[ERROR] Database Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Throwable $e) {
    echo "\n[ERROR] System Error: " . $e->getMessage() . "\n";
    exit(1);
}
