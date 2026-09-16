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
    echo "[*] Testing AuthService attempt for 'admin'...\n";
    $authService = new \App\Services\AuthService();
    $result = $authService->attempt('admin', 'Admin@123456');

    if ($result) {
        echo "[+] Authentication SUCCESS! User payload:\n";
        print_r(\App\Core\Session::get('user'));
    } else {
        echo "[-] Authentication failed!\n";
    }
} catch (\Throwable $e) {
    echo "[!] Exception caught: " . $e->getMessage() . "\n";
    echo "Location: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
