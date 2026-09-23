<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Services\DepartmentService;

class DepartmentController extends Controller
{
    public function __construct(
        private DepartmentService $departmentService,
        private Gate $gate
    ) {}

    public function index(Request $request): void
    {
        $this->gate->authorize('viewAny', 'department');
        $departments = $this->departmentService->getWithStatistics();

        $this->render('departments/index', [
            'pageTitle'   => 'Academic Departments',
            'departments' => $departments
        ]);
    }

    public function store(StoreDepartmentRequest $request): void
    {
        $this->departmentService->create($request->toDTO());
        $this->redirectWith('/departments', 'success', 'Department created successfully!');
    }

    public function update(UpdateDepartmentRequest $request): void
    {
        $id = (int)$request->param('id');
        $this->departmentService->update($id, $request->toDTO());
        $this->redirectWith('/departments', 'success', 'Department updated successfully!');
    }
}
