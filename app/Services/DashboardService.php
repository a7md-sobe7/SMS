<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Contracts\Repositories\InstructorRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;

class DashboardService
{
    public function __construct(
        private StudentRepositoryInterface $studentRepo,
        private CourseRepositoryInterface $courseRepo,
        private InstructorRepositoryInterface $instructorRepo,
        private DepartmentRepositoryInterface $deptRepo,
        private EnrollmentRepositoryInterface $enrollmentRepo,
        private GradeRepositoryInterface $gradeRepo,
        private AuditLogRepositoryInterface $auditRepo,
        private UserRepositoryInterface $userRepo
    ) {}

    public function getAdminStats(): array
    {
        return [
            'total_students'    => $this->studentRepo->count(),
            'active_students'   => $this->studentRepo->count(['status' => 'active']),
            'total_courses'     => $this->courseRepo->count(),
            'total_instructors' => $this->instructorRepo->count(),
            'total_departments' => $this->deptRepo->count(),
            'total_enrollments' => $this->enrollmentRepo->count(['status' => 'enrolled']),
            'recent_logs'       => $this->auditRepo->getRecent(10),
            'recent_students'   => $this->studentRepo->paginate(1, 5)['data']
        ];
    }

    public function getInstructorStats(int $userId): array
    {
        $instructor = $this->instructorRepo->findByUserId($userId);
        $courses = [];

        if ($instructor) {
            $courses = $this->courseRepo->findAllWithDetails(null, (int)$instructor['id']);
        }

        return [
            'instructor'    => $instructor,
            'courses'       => $courses,
            'total_courses' => count($courses),
        ];
    }

    public function getStudentStats(int $userId): array
    {
        $student = $this->studentRepo->findByUserId($userId);
        $enrollments = [];
        $gpa = ['total_graded_courses' => 0, 'average_numerical_grade' => 0, 'total_credits_earned' => 0];

        if ($student) {
            $enrollments = $this->enrollmentRepo->getStudentEnrollments((int)$student['id']);
            $gpa = $this->gradeRepo->getStudentGpaSummary((int)$student['id']);
        }

        return [
            'student'           => $student,
            'enrollments'       => $enrollments,
            'total_enrollments' => count($enrollments),
            'gpa'               => $gpa
        ];
    }
}
