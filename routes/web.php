<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\StudentController;
use App\Controllers\DepartmentController;
use App\Controllers\InstructorController;
use App\Controllers\CourseController;
use App\Controllers\EnrollmentController;
use App\Controllers\GradeController;
use App\Controllers\AttendanceController;
use App\Core\Router;

/** @var Router $router */

// -------------------------------------------------------------
// PUBLIC AUTHENTICATION ROUTES
// -------------------------------------------------------------
$router->get('/login', [AuthController::class, 'showLoginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// -------------------------------------------------------------
// PROTECTED WEB APPLICATION ROUTES (Requires AuthMiddleware)
// -------------------------------------------------------------
$router->group(['middleware' => 'AuthMiddleware'], function (Router $r) {
    // Universal Dashboard (Routes dynamically based on user role)
    $r->get('/', [DashboardController::class, 'index']);
    $r->get('/dashboard', [DashboardController::class, 'index']);

    // ---------------------------------------------------------
    // STUDENT MANAGEMENT
    // ---------------------------------------------------------
    $r->get('/students', [StudentController::class, 'index'], ['RoleMiddleware:admin,registrar,instructor']);
    $r->get('/students/create', [StudentController::class, 'create'], ['RoleMiddleware:admin,registrar']);
    $r->post('/students', [StudentController::class, 'store'], ['RoleMiddleware:admin,registrar']);
    $r->get('/students/{id}', [StudentController::class, 'show']);
    $r->get('/students/{id}/edit', [StudentController::class, 'edit'], ['RoleMiddleware:admin,registrar']);
    $r->post('/students/{id}', [StudentController::class, 'update'], ['RoleMiddleware:admin,registrar']);
    $r->post('/students/{id}/delete', [StudentController::class, 'destroy'], ['RoleMiddleware:admin']);

    // ---------------------------------------------------------
    // DEPARTMENT MANAGEMENT
    // ---------------------------------------------------------
    $r->get('/departments', [DepartmentController::class, 'index']);
    $r->post('/departments', [DepartmentController::class, 'store'], ['RoleMiddleware:admin']);
    $r->post('/departments/{id}', [DepartmentController::class, 'update'], ['RoleMiddleware:admin']);

    // ---------------------------------------------------------
    // INSTRUCTOR MANAGEMENT
    // ---------------------------------------------------------
    $r->get('/instructors', [InstructorController::class, 'index']);
    $r->post('/instructors', [InstructorController::class, 'store'], ['RoleMiddleware:admin']);

    // ---------------------------------------------------------
    // COURSE MANAGEMENT
    // ---------------------------------------------------------
    $r->get('/courses', [CourseController::class, 'index']);
    $r->post('/courses', [CourseController::class, 'store'], ['RoleMiddleware:admin,registrar']);
    $r->post('/courses/{id}', [CourseController::class, 'update'], ['RoleMiddleware:admin,registrar']);

    // ---------------------------------------------------------
    // ENROLLMENTS
    // ---------------------------------------------------------
    $r->get('/enrollments', [EnrollmentController::class, 'index'], ['RoleMiddleware:admin,registrar']);
    $r->post('/enrollments', [EnrollmentController::class, 'store'], ['RoleMiddleware:admin,registrar,student']);
    $r->post('/enrollments/{id}/drop', [EnrollmentController::class, 'drop'], ['RoleMiddleware:admin,registrar,student']);

    // ---------------------------------------------------------
    // GRADING SYSTEM
    // ---------------------------------------------------------
    $r->get('/grades', [GradeController::class, 'index']);
    $r->post('/grades/upsert', [GradeController::class, 'upsert'], ['RoleMiddleware:admin,instructor']);

    // ---------------------------------------------------------
    // ATTENDANCE SYSTEM
    // ---------------------------------------------------------
    $r->get('/attendance', [AttendanceController::class, 'index']);
    $r->post('/attendance/record', [AttendanceController::class, 'record'], ['RoleMiddleware:admin,instructor']);
});
