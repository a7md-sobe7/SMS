<?php

declare(strict_types=1);

use App\Controllers\Api\ApiAuthController;
use App\Controllers\Api\ApiStudentController;
use App\Controllers\Api\ApiCourseController;
use App\Controllers\Api\ApiGradeController;
use App\Core\Router;

/** @var Router $router */

$router->group(['prefix' => '/api'], function (Router $r) {
    // API Authentication
    $r->post('/auth/login', [ApiAuthController::class, 'login']);
    $r->post('/auth/logout', [ApiAuthController::class, 'logout']);
    $r->get('/auth/me', [ApiAuthController::class, 'me'], ['AuthMiddleware']);

    // Protected API Endpoints
    $r->group(['middleware' => 'AuthMiddleware'], function (Router $api) {
        // Students API
        $api->get('/students', [ApiStudentController::class, 'index']);
        $api->get('/students/{id}', [ApiStudentController::class, 'show']);
        $api->post('/students', [ApiStudentController::class, 'store'], ['RoleMiddleware:admin,registrar']);
        $api->put('/students/{id}', [ApiStudentController::class, 'update'], ['RoleMiddleware:admin,registrar']);
        $api->delete('/students/{id}', [ApiStudentController::class, 'destroy'], ['RoleMiddleware:admin']);

        // Courses API
        $api->get('/courses', [ApiCourseController::class, 'index']);
        $api->get('/courses/{id}', [ApiCourseController::class, 'show']);
        $api->post('/courses', [ApiCourseController::class, 'store'], ['RoleMiddleware:admin,registrar']);

        // Grades API
        $api->post('/grades', [ApiGradeController::class, 'upsert'], ['RoleMiddleware:admin,instructor']);
    });
});
