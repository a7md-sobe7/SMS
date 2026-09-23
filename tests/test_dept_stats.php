<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

$app = \App\Core\Container::getInstance();

$providers = [
    \App\Providers\AppServiceProvider::class,
    \App\Providers\RepositoryServiceProvider::class,
    \App\Providers\AuthServiceProvider::class,
    \App\Providers\EventServiceProvider::class,
];

foreach ($providers as $pClass) {
    $p = new $pClass($app);
    $p->register();
    $p->boot();
}

$deptService = $app->make(\App\Services\DepartmentService::class);
$deptController = $app->make(\App\Controllers\Api\ApiDepartmentController::class);

$req = new \App\Core\Request('GET', '/api/departments');
$response = $deptController->index($req);

$data = json_decode($response->getContent(), true);

echo "[*] Department API response data:\n";
print_r($data);
