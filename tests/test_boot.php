<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

// Load .env
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

try {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/login';
    
    $request = \App\Core\Request::capture();
    $router = new \App\Core\Router();

    require_once dirname(__DIR__) . '/routes/web.php';
    require_once dirname(__DIR__) . '/routes/api.php';

    echo "[*] Testing Route Dispatch for /login ...\n";
    ob_start();
    $response = $router->dispatch($request);
    $out = ob_get_clean();

    echo "[+] /login executed successfully! Output length: " . strlen($out) . " bytes\n";
} catch (\Throwable $e) {
    echo "[!] Exception caught: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
