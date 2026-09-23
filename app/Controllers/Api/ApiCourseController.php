<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Core\Response;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Services\CourseService;

class ApiCourseController extends Controller
{
    public function __construct(
        private CourseService $courseService,
        private Gate $gate
    ) {}

    public function index(Request $request): Response
    {
        $this->gate->authorize('viewAny', 'course');

        $deptId = $request->input('department_id') ? (int)$request->input('department_id') : null;
        $instructorId = $request->input('instructor_id') ? (int)$request->input('instructor_id') : null;

        $courses = $this->courseService->listCourses($deptId, $instructorId);
        $transformed = CourseResource::collection($courses, $request);

        return Response::rawJson([
            'success' => true,
            'message' => 'Courses retrieved successfully.',
            'data'    => $transformed
        ], 200);
    }

    public function show(Request $request): Response
    {
        $id = (int)$request->param('id');
        $this->gate->authorize('view', 'course');

        $details = $this->courseService->getCourseDetails($id);
        $transformed = CourseResource::make($details['course'])->toArray($request);
        $transformed['roster'] = $details['roster'];

        return Response::rawJson([
            'success' => true,
            'message' => 'Course details retrieved.',
            'data'    => $transformed
        ], 200);
    }

    public function store(StoreCourseRequest $request): Response
    {
        $id = $this->courseService->createCourse($request->toDTO(), auth_id());
        $course = $this->courseService->getById($id);

        return CourseResource::make($course)->toResponse($request, 201, 'Course created successfully.');
    }

    public function update(UpdateCourseRequest $request): Response
    {
        $id = (int)$request->param('id');
        $this->courseService->updateCourse($id, $request->toDTO(), auth_id());
        $course = $this->courseService->getById($id);

        return CourseResource::make($course)->toResponse($request, 200, 'Course updated successfully.');
    }

    public function destroy(Request $request): Response
    {
        $id = (int)$request->param('id');
        $this->gate->authorize('delete', 'course');

        $this->courseService->deleteCourse($id);

        return Response::rawJson([
            'success' => true,
            'message' => 'Course deleted successfully.'
        ], 200);
    }
}
