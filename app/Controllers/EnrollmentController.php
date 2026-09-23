<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Http\Requests\Enrollment\StoreEnrollmentRequest;
use App\Services\CourseService;
use App\Services\EnrollmentService;
use App\Services\StudentService;

class EnrollmentController extends Controller
{
    public function __construct(
        private EnrollmentService $enrollmentService,
        private CourseService $courseService,
        private StudentService $studentService,
        private Gate $gate
    ) {}

    public function index(Request $request): void
    {
        $this->gate->authorize('viewAny', 'enrollment');

        $courseId = $request->input('course_id') ? (int)$request->input('course_id') : null;
        $courses = $this->courseService->listCourses();
        $studentsData = $this->studentService->searchAndFilter(null, null, null, 'active', 1, 100);

        $roster = [];
        $selectedCourse = null;

        if ($courseId) {
            $details = $this->courseService->getCourseDetails($courseId);
            $selectedCourse = $details['course'];
            $roster = $details['roster'];
        }

        $this->render('enrollments/index', [
            'pageTitle'      => 'Course Enrollment & Rosters',
            'courses'        => $courses,
            'students'       => $studentsData['data'],
            'selectedCourse' => $selectedCourse,
            'roster'         => $roster,
            'courseId'       => $courseId
        ]);
    }

    public function store(StoreEnrollmentRequest $request): void
    {
        $dto = $request->toDTO();
        $this->enrollmentService->enrollStudent($dto, auth_id());
        $this->redirectWith("/enrollments?course_id={$dto->course_id}", 'success', 'Student successfully enrolled!');
    }

    public function drop(Request $request): void
    {
        $id = (int)$request->param('id');
        $this->gate->authorize('drop', 'enrollment');

        $this->enrollmentService->dropEnrollment($id, auth_id());
        $this->redirectWith('/enrollments', 'success', 'Enrollment dropped.');
    }
}
