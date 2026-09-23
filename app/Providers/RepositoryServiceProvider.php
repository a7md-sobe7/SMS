<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Contracts\Repositories\InstructorRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Core\ServiceProvider;
use App\Repositories\AttendanceRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\CourseRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\GradeRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\StudentRepository;
use App\Repositories\UserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Repository Contracts to Concrete Implementations
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->singleton(CourseRepositoryInterface::class, CourseRepository::class);
        $this->app->singleton(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->singleton(InstructorRepositoryInterface::class, InstructorRepository::class);
        $this->app->singleton(EnrollmentRepositoryInterface::class, EnrollmentRepository::class);
        $this->app->singleton(GradeRepositoryInterface::class, GradeRepository::class);
        $this->app->singleton(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->singleton(AuditLogRepositoryInterface::class, AuditLogRepository::class);
    }
}
