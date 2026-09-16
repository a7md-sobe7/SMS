<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Thread-Safe PDO Connection Manager & Transaction Coordinator
 * 
 * Implements the Singleton pattern to guarantee a single open database connection per request.
 * Encapsulates safe transaction management with automated rollback on unhandled exceptions.
 */
class Database
{
    private static ?PDO $instance = null;
    private static int $transactionDepth = 0;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {}

    /**
     * Prevent cloning of the singleton instance.
     */
    private function __clone() {}

    /**
     * Prevent unserializing of the singleton instance.
     */
    public function __wakeup(): void
    {
        throw new RuntimeException("Cannot unserialize singleton Database connection.");
    }

    /**
     * Retrieve the shared PDO database connection instance.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $configPath = dirname(__DIR__, 2) . '/config/database.php';
            if (!file_exists($configPath)) {
                throw new RuntimeException("Database configuration file not found at: {$configPath}");
            }

            $config = require $configPath;

            $driver   = $config['driver'] ?? 'mysql';
            $host     = $config['host'] ?? '127.0.0.1';
            $port     = $config['port'] ?? 3306;
            $database = $config['database'] ?? 'student_management';
            $charset  = $config['charset'] ?? 'utf8mb4';
            $user     = $config['username'] ?? 'root';
            $password = $config['password'] ?? '';
            $options  = $config['options'] ?? [];

            $dsn = "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";

            try {
                self::$instance = new PDO($dsn, $user, $password, $options);
            } catch (PDOException $e) {
                // Log technical error securely, never expose database credentials to the user
                error_log("[Database Connection Error] " . $e->getMessage());
                throw new RuntimeException("Could not connect to the database. Please check your system configuration.");
            }
        }

        return self::$instance;
    }

    /**
     * Execute a callback inside an atomic database transaction.
     * Automatically commits on success, and rolls back if an exception is thrown.
     * 
     * @template T
     * @param callable(PDO): T $callback
     * @return T
     * @throws Throwable
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::getConnection();

        if (self::$transactionDepth === 0) {
            $pdo->beginTransaction();
        }
        self::$transactionDepth++;

        try {
            $result = $callback($pdo);

            self::$transactionDepth--;
            if (self::$transactionDepth === 0) {
                $pdo->commit();
            }

            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            self::$transactionDepth = 0;
            throw $e;
        }
    }
}
