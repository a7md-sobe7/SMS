<?php

declare(strict_types=1);

/**
 * ==============================================================
 * Student Management System - Front Controller & Application Entrypoint
 * ==============================================================
 */

// 1. Error Reporting Configuration
error_reporting(E_ALL);
ini_set('display_errors', '0');

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

// 5. Bootstrap Container & Service Providers
$app = \App\Core\Container::getInstance();

$providers = [
    \App\Providers\AppServiceProvider::class,
    \App\Providers\RepositoryServiceProvider::class,
    \App\Providers\AuthServiceProvider::class,
    \App\Providers\EventServiceProvider::class,
];

$providerInstances = [];
foreach ($providers as $providerClass) {
    /** @var \App\Core\ServiceProvider $provider */
    $provider = new $providerClass($app);
    $provider->register();
    $providerInstances[] = $provider;
}

foreach ($providerInstances as $provider) {
    $provider->boot();
}

// 6. Handle Incoming HTTP Request
$request = null;
try {
    $request = \App\Core\Request::capture();
    $router = new \App\Core\Router($app);

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
    /** @var \App\Core\Exceptions\Handler $handler */
    $handler = $app->make(\App\Core\Exceptions\Handler::class);
    $response = $handler->render($e, $request);

    if ($response instanceof \App\Core\Response) {
        $response->send();
    }
}
