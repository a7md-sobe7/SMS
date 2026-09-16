<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Repositories\StudentRepository;
use App\Repositories\CourseRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\AuditLogRepository;

class DashboardController extends Controller
{
    private StudentRepository $studentRepo;
    private CourseRepository $courseRepo;
    private DepartmentRepository $deptRepo;
    private InstructorRepository $instructorRepo;
    private EnrollmentRepository $enrollmentRepo;
    private AuditLogRepository $auditRepo;

    public function __construct()
    {
        $this->studentRepo = new StudentRepository();
        $this->courseRepo = new CourseRepository();
        $this->deptRepo = new DepartmentRepository();
        $this->instructorRepo = new InstructorRepository();
        $this->enrollmentRepo = new EnrollmentRepository();
        $this->auditRepo = new AuditLogRepository();
    }

    public function index(Request $request): void
    {
        $role = auth_role();

        $stats = [
            'total_students'    => $this->studentRepo->count(),
            'total_courses'     => $this->courseRepo->count(),
            'total_departments' => $this->deptRepo->count(),
            'total_instructors' => $this->instructorRepo->count(),
            'recent_logs'       => $this->auditRepo->getRecentLogs(10),
            'departments'       => $this->deptRepo->getWithStatistics()
        ];

        $this->render('dashboard/admin', [
            'pageTitle' => 'Academic Dashboard',
            'stats'     => $stats,
            'user'      => auth_user(),
            'role'      => $role
        ]);
    }
}
