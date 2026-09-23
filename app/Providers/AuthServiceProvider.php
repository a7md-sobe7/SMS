<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Gate;
use App\Core\ServiceProvider;
use App\Policies\AttendancePolicy;
use App\Policies\CoursePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\EnrollmentPolicy;
use App\Policies\GradePolicy;
use App\Policies\InstructorPolicy;
use App\Policies\StudentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Map of resources/entities to policy classes.
     * @var array<string, string>
     */
    protected array $policies = [
        'student'    => StudentPolicy::class,
        'course'     => CoursePolicy::class,
        'grade'      => GradePolicy::class,
        'attendance' => AttendancePolicy::class,
        'enrollment' => EnrollmentPolicy::class,
        'department' => DepartmentPolicy::class,
        'instructor' => InstructorPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $gate = $this->app->make(Gate::class);

        foreach ($this->policies as $resource => $policyClass) {
            $gate->policy($resource, $policyClass);
        }
    }
}
