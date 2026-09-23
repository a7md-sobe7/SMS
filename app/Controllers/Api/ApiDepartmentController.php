<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Core\Response;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;

class ApiDepartmentController extends Controller
{
    public function __construct(
        private DepartmentService $departmentService,
        private Gate $gate
    ) {}

    public function index(Request $request): Response
    {
        $this->gate->authorize('viewAny', 'department');
        $departments = $this->departmentService->getWithStatistics();
        $transformed = DepartmentResource::collection($departments, $request);

        return Response::rawJson([
            'success' => true,
            'message' => 'Departments retrieved successfully.',
            'data'    => $transformed
        ], 200);
    }

    public function show(Request $request): Response
    {
        $id = (int)$request->param('id');
        $dept = $this->departmentService->getById($id);

        return DepartmentResource::make($dept)->toResponse($request, 200);
    }

    public function store(StoreDepartmentRequest $request): Response
    {
        $id = $this->departmentService->create($request->toDTO());
        $dept = $this->departmentService->getById($id);

        return DepartmentResource::make($dept)->toResponse($request, 201, 'Department created successfully.');
    }

    public function update(UpdateDepartmentRequest $request): Response
    {
        $id = (int)$request->param('id');
        $this->departmentService->update($id, $request->toDTO());
        $dept = $this->departmentService->getById($id);

        return DepartmentResource::make($dept)->toResponse($request, 200, 'Department updated successfully.');
    }
}
