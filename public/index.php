<?php

declare(strict_types=1);

/**
 * ==============================================================
 * Student Management System - Front Controller & Application Entrypoint
 * ==============================================================
 */

// 1. Error Reporting Configuration
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Never leak raw stack traces to web clients in production

// 2. Register Native PSR-4 Autoloader & Helpers
require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

// If composer vendor autoloader exists, load it as well
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// 3. Load Environment Configuration
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

// 4. Initialize Secure Session
\App\Core\Session::start();

// 5. Handle Incoming HTTP Request
try {
    $request = \App\Core\Request::capture();
    $router = new \App\Core\Router();

    // Load Route Catalogs
    require_once dirname(__DIR__) . '/routes/web.php';
    require_once dirname(__DIR__) . '/routes/api.php';

    // Dispatch Request through Middleware and Controller Pipeline
    $response = $router->dispatch($request);

    // If a Response object was returned, dispatch it
    if ($response instanceof \App\Core\Response) {
        $response->send();
    }

} catch (\Throwable $e) {
    // Log exception details securely
    error_log(sprintf(
        "[%s] Uncaught Exception in %s:%d - %s\nStack Trace:\n%s",
        date('Y-m-d H:i:s'),
        $e->getFile(),
        $e->getLine(),
        $e->getMessage(),
        $e->getTraceAsString()
    ));

    $isDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

    // If API or AJAX request, return structured JSON error
    if (isset($request) && ($request->isAjax() || str_starts_with($request->getPath(), '/api/'))) {
        json_response(
            $isDebug ? ['exception' => get_class($e), 'trace' => explode("\n", $e->getTraceAsString())] : null,
            500,
            $isDebug ? $e->getMessage() : "An unexpected internal server error occurred."
        );
    }

    // Otherwise, render friendly 500 HTML view
    http_response_code(500);
    $errorFile = dirname(__DIR__) . '/views/errors/500.php';
    if (file_exists($errorFile)) {
        $exception = $e;
        require $errorFile;
    } else {
        echo "<h1>500 Internal Server Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
