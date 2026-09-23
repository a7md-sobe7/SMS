<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Core\Response;
use App\Http\Requests\Instructor\StoreInstructorRequest;
use App\Http\Resources\InstructorResource;
use App\Services\InstructorService;

class ApiInstructorController extends Controller
{
    public function __construct(
        private InstructorService $instructorService,
        private Gate $gate
    ) {}

    public function index(Request $request): Response
    {
        $this->gate->authorize('viewAny', 'instructor');
        $instructors = $this->instructorService->getAllWithDepartments();
        $transformed = InstructorResource::collection($instructors, $request);

        return Response::rawJson([
            'success' => true,
            'message' => 'Instructors retrieved successfully.',
            'data'    => $transformed
        ], 200);
    }

    public function show(Request $request): Response
    {
        $id = (int)$request->param('id');
        $this->gate->authorize('viewAny', 'instructor');

        $instructor = $this->instructorService->getById($id);
        return InstructorResource::make($instructor)->toResponse($request, 200);
    }

    public function store(StoreInstructorRequest $request): Response
    {
        $id = $this->instructorService->create($request->toDTO());
        $instructor = $this->instructorService->getById($id);

        return InstructorResource::make($instructor)->toResponse($request, 201, 'Instructor added successfully.');
    }
}
