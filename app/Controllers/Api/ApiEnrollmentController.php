<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Core\Response;
use App\Http\Requests\Enrollment\StoreEnrollmentRequest;
use App\Http\Resources\EnrollmentResource;
use App\Services\EnrollmentService;

class ApiEnrollmentController extends Controller
{
    public function __construct(
        private EnrollmentService $enrollmentService,
        private Gate $gate
    ) {}

    public function index(Request $request): Response
    {
        $this->gate->authorize('viewAny', 'enrollment');

        $studentId = $request->input('student_id') ? (int)$request->input('student_id') : null;
        $courseId  = $request->input('course_id') ? (int)$request->input('course_id') : null;

        if ($courseId) {
            $roster = $this->enrollmentService->getCourseRoster($courseId);
            $transformed = EnrollmentResource::collection($roster, $request);
        } elseif ($studentId) {
            $enrollments = $this->enrollmentService->getStudentEnrollments($studentId);
            $transformed = EnrollmentResource::collection($enrollments, $request);
        } else {
            $transformed = [];
        }

        return Response::rawJson([
            'success' => true,
            'message' => 'Enrollments retrieved.',
            'data'    => $transformed
        ], 200);
    }

    public function store(StoreEnrollmentRequest $request): Response
    {
        $enrollmentId = $this->enrollmentService->enrollStudent($request->toDTO(), auth_id());

        return Response::rawJson([
            'success'       => true,
            'message'       => 'Student enrolled successfully.',
            'data'          => ['enrollment_id' => $enrollmentId],
            'enrollment_id' => $enrollmentId
        ], 201);
    }

    public function drop(Request $request): Response
    {
        $id = (int)$request->param('id');
        $this->gate->authorize('drop', 'enrollment');

        $this->enrollmentService->dropEnrollment($id, auth_id());

        return Response::rawJson([
            'success' => true,
            'message' => 'Enrollment dropped successfully.'
        ], 200);
    }
}
