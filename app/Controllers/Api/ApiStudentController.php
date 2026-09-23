<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Core\Response;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Services\StudentService;

class ApiStudentController extends Controller
{
    public function __construct(
        private StudentService $studentService,
        private Gate $gate
    ) {}

    public function index(Request $request): Response
    {
        $this->gate->authorize('viewAny', 'student');

        $search = $request->input('search') ? (string)$request->input('search') : null;
        $deptId = $request->input('department_id') ? (int)$request->input('department_id') : null;
        $level  = $request->input('level') ? (string)$request->input('level') : null;
        $status = $request->input('status') ? (string)$request->input('status') : null;
        $page   = (int)$request->input('page', 1);

        $result = $this->studentService->searchAndFilter(
            $search,
            $deptId,
            $level,
            $status,
            $page,
            15
        );

        $transformed = StudentResource::collection($result['data'], $request);

        return Response::rawJson([
            'success' => true,
            'message' => 'Students retrieved successfully.',
            'data'    => $transformed,
            'meta'    => [
                'pagination' => $result['pagination']
            ]
        ], 200);
    }

    public function show(Request $request): Response
    {
        $id = (int)$request->param('id');
        $profile = $this->studentService->getProfileWithTranscript($id);

        $this->gate->authorize('view', $profile['student']);

        $transformed = StudentResource::make($profile['student'])->toArray($request);
        $transformed['enrollments'] = $profile['enrollments'];
        $transformed['gpa_summary'] = $profile['gpa'];

        return Response::rawJson([
            'success' => true,
            'message' => 'Student details retrieved.',
            'data'    => $transformed
        ], 200);
    }

    public function store(StoreStudentRequest $request): Response
    {
        $studentId = $this->studentService->createStudent($request->toDTO(), auth_id());
        $created = $this->studentService->getById($studentId);

        return StudentResource::make($created)->toResponse($request, 201, 'Student created successfully.');
    }

    public function update(UpdateStudentRequest $request): Response
    {
        $id = (int)$request->param('id');
        $this->studentService->updateStudent($id, $request->toDTO(), auth_id());
        $updated = $this->studentService->getById($id);

        return StudentResource::make($updated)->toResponse($request, 200, 'Student updated successfully.');
    }

    public function destroy(Request $request): Response
    {
        $id = (int)$request->param('id');
        $this->gate->authorize('delete', 'student');

        $this->studentService->archiveStudent($id, auth_id());

        return Response::rawJson([
            'success' => true,
            'message' => 'Student archived successfully.'
        ], 200);
    }
}
