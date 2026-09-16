<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\EnrollmentRepository;
use App\Repositories\CourseRepository;
use App\Repositories\StudentRepository;
use App\Repositories\AuditLogRepository;

class EnrollmentController extends Controller
{
    private EnrollmentRepository $enrollmentRepo;
    private CourseRepository $courseRepo;
    private StudentRepository $studentRepo;
    private AuditLogRepository $auditRepo;

    public function __construct()
    {
        $this->enrollmentRepo = new EnrollmentRepository();
        $this->courseRepo = new CourseRepository();
        $this->studentRepo = new StudentRepository();
        $this->auditRepo = new AuditLogRepository();
    }

    public function index(Request $request): void
    {
        $courseId = $request->input('course_id') ? (int)$request->input('course_id') : null;
        $courses = $this->courseRepo->findAllWithDetails();
        $students = $this->studentRepo->findAll('last_name', 'ASC');

        $roster = [];
        $selectedCourse = null;

        if ($courseId) {
            $selectedCourse = $this->courseRepo->findWithDetails($courseId);
            $roster = $this->enrollmentRepo->getCourseRoster($courseId);
        }

        $this->render('enrollments/index', [
            'pageTitle'      => 'Course Enrollment & Rosters',
            'courses'        => $courses,
            'students'       => $students,
            'selectedCourse' => $selectedCourse,
            'roster'         => $roster,
            'courseId'       => $courseId
        ]);
    }

    public function store(Request $request): void
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'student_id' => 'required|numeric',
            'course_id'  => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Session::flash('error', 'Student and Course selections are required.');
            redirect('/enrollments');
        }

        $studentId = (int)$data['student_id'];
        $courseId  = (int)$data['course_id'];

        // Enforce Transaction & Business Rules
        try {
            Database::transaction(function () use ($studentId, $courseId) {
                $course = $this->courseRepo->find($courseId);
                if (!$course) {
                    throw new \RuntimeException("The selected course does not exist.");
                }

                // Check duplicate enrollment
                $existing = $this->enrollmentRepo->findByStudentAndCourse($studentId, $courseId);
                if ($existing && $existing['status'] === 'enrolled') {
                    throw new \RuntimeException("This student is already actively enrolled in this course.");
                }

                // Check course capacity
                $currentEnrolled = $this->enrollmentRepo->countActiveEnrollments($courseId);
                if ($currentEnrolled >= (int)$course['capacity']) {
                    throw new \RuntimeException("Cannot enroll student: Course has reached its maximum capacity of {$course['capacity']}.");
                }

                if ($existing) {
                    $this->enrollmentRepo->update((int)$existing['id'], [
                        'status'          => 'enrolled',
                        'enrollment_date' => date('Y-m-d')
                    ]);
                    $enrollId = (int)$existing['id'];
                } else {
                    $enrollId = $this->enrollmentRepo->create([
                        'student_id'      => $studentId,
                        'course_id'       => $courseId,
                        'enrollment_date' => date('Y-m-d'),
                        'status'          => 'enrolled'
                    ]);
                }

                $this->auditRepo->log(
                    auth_id(),
                    'STUDENT_ENROLLED',
                    'Enrollment',
                    $enrollId,
                    ['student_id' => $studentId, 'course_id' => $courseId]
                );
            });

            $this->redirectWith("/enrollments?course_id={$courseId}", 'success', 'Student successfully enrolled!');
        } catch (\Throwable $e) {
            $this->redirectWith("/enrollments?course_id={$courseId}", 'error', $e->getMessage());
        }
    }

    public function drop(Request $request): void
    {
        $id = (int)$request->param('id');
        $enrollment = $this->enrollmentRepo->find($id);

        if (!$enrollment) {
            $this->redirectWith('/enrollments', 'error', 'Enrollment record not found.');
        }

        $this->enrollmentRepo->update($id, ['status' => 'dropped']);
        $this->auditRepo->log(auth_id(), 'ENROLLMENT_DROPPED', 'Enrollment', $id);

        $this->redirectWith("/enrollments?course_id={$enrollment['course_id']}", 'success', 'Enrollment dropped.');
    }
}
