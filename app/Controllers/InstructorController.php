<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Http\Requests\Instructor\StoreInstructorRequest;
use App\Services\DepartmentService;
use App\Services\InstructorService;

class InstructorController extends Controller
{
    public function __construct(
        private InstructorService $instructorService,
        private DepartmentService $deptService,
        private Gate $gate
    ) {}

    public function index(Request $request): void
    {
        $this->gate->authorize('viewAny', 'instructor');

        $instructors = $this->instructorService->getAllWithDepartments();
        $departments = $this->deptService->getAll();

        $this->render('instructors/index', [
            'pageTitle'   => 'Faculty & Instructors Directory',
            'instructors' => $instructors,
            'departments' => $departments
        ]);
    }

    public function store(StoreInstructorRequest $request): void
    {
        $this->instructorService->create($request->toDTO());
        $this->redirectWith('/instructors', 'success', 'Instructor successfully added.');
    }
}
