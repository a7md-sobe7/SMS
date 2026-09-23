<?php

declare(strict_types=1);

use App\Controllers\Api\ApiAuthController;
use App\Controllers\Api\ApiDashboardController;
use App\Controllers\Api\ApiStudentController;
use App\Controllers\Api\ApiCourseController;
use App\Controllers\Api\ApiGradeController;
use App\Controllers\Api\ApiDepartmentController;
use App\Controllers\Api\ApiInstructorController;
use App\Controllers\Api\ApiEnrollmentController;
use App\Controllers\Api\ApiAttendanceController;
use App\Core\Router;

/** @var Router $router */

$router->group(['prefix' => '/api'], function (Router $r) {
    // API Authentication & Public Resources
    $r->post('/auth/login', [ApiAuthController::class, 'login']);
    $r->post('/auth/register', [ApiAuthController::class, 'register']);
    $r->post('/auth/logout', [ApiAuthController::class, 'logout']);
    $r->get('/auth/me', [ApiAuthController::class, 'me'], ['AuthMiddleware']);
    $r->get('/departments/public', [ApiDepartmentController::class, 'index']);

    // Protected API Endpoints
    $r->group(['middleware' => 'AuthMiddleware'], function (Router $api) {
        // Dashboard Stats
        $api->get('/dashboard/stats', [ApiDashboardController::class, 'stats']);

        // Students API
        $api->get('/students', [ApiStudentController::class, 'index']);
        $api->get('/students/{id}', [ApiStudentController::class, 'show']);
        $api->post('/students', [ApiStudentController::class, 'store'], ['RoleMiddleware:admin,registrar']);
        $api->put('/students/{id}', [ApiStudentController::class, 'update'], ['RoleMiddleware:admin,registrar']);
        $api->delete('/students/{id}', [ApiStudentController::class, 'destroy'], ['RoleMiddleware:admin']);

        // Departments API
        $api->get('/departments', [ApiDepartmentController::class, 'index']);
        $api->get('/departments/{id}', [ApiDepartmentController::class, 'show']);
        $api->post('/departments', [ApiDepartmentController::class, 'store'], ['RoleMiddleware:admin']);
        $api->put('/departments/{id}', [ApiDepartmentController::class, 'update'], ['RoleMiddleware:admin']);

        // Instructors API
        $api->get('/instructors', [ApiInstructorController::class, 'index']);
        $api->get('/instructors/{id}', [ApiInstructorController::class, 'show']);
        $api->post('/instructors', [ApiInstructorController::class, 'store'], ['RoleMiddleware:admin']);

        // Courses API
        $api->get('/courses', [ApiCourseController::class, 'index']);
        $api->get('/courses/{id}', [ApiCourseController::class, 'show']);
        $api->post('/courses', [ApiCourseController::class, 'store'], ['RoleMiddleware:admin,registrar']);
        $api->put('/courses/{id}', [ApiCourseController::class, 'update'], ['RoleMiddleware:admin,registrar']);
        $api->delete('/courses/{id}', [ApiCourseController::class, 'destroy'], ['RoleMiddleware:admin']);

        // Enrollments API
        $api->get('/enrollments', [ApiEnrollmentController::class, 'index']);
        $api->post('/enrollments', [ApiEnrollmentController::class, 'store'], ['RoleMiddleware:admin,registrar,student']);
        $api->post('/enrollments/{id}/drop', [ApiEnrollmentController::class, 'drop'], ['RoleMiddleware:admin,registrar,student']);

        // Grades API
        $api->get('/grades', [ApiGradeController::class, 'index']);
        $api->post('/grades', [ApiGradeController::class, 'upsert'], ['RoleMiddleware:admin,instructor']);

        // Attendance API
        $api->get('/attendance', [ApiAttendanceController::class, 'index']);
        $api->post('/attendance/record', [ApiAttendanceController::class, 'record'], ['RoleMiddleware:admin,instructor']);
    });
});
