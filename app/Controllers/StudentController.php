<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Services\DepartmentService;
use App\Services\StudentService;

class StudentController extends Controller
{
    public function __construct(
        private StudentService $studentService,
        private DepartmentService $departmentService,
        private Gate $gate
    ) {}

    public function index(Request $request): void
    {
        $this->gate->authorize('viewAny', 'student');

        $search = $request->input('search') ? (string)$request->input('search') : null;
        $deptId = $request->input('department_id') ? (int)$request->input('department_id') : null;
        $level  = $request->input('level') ? (string)$request->input('level') : null;
        $status = $request->input('status') ? (string)$request->input('status') : null;
        $page   = (int)$request->input('page', 1);

        $studentsData = $this->studentService->searchAndFilter(
            $search,
            $deptId,
            $level,
            $status,
            $page,
            10
        );

        $departments = $this->departmentService->getAll();

        $this->render('students/index', [
            'pageTitle'   => 'Student Directory',
            'students'    => $studentsData['data'],
            'pagination'  => $studentsData['pagination'],
            'departments' => $departments,
            'filters'     => [
                'search'        => $search ?? '',
                'department_id' => $deptId,
                'level'         => $level ?? '',
                'status'        => $status ?? ''
            ]
        ]);
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $data = $this->studentService->getProfileWithTranscript($id);

        $this->gate->authorize('view', $data['student']);

        $this->render('students/show', [
            'pageTitle'   => "Student Profile: {$data['student']['first_name']} {$data['student']['last_name']}",
            'student'     => $data['student'],
            'enrollments' => $data['enrollments'],
            'gpa'         => $data['gpa']
        ]);
    }

    public function create(Request $request): void
    {
        $this->gate->authorize('create', 'student');

        $departments = $this->departmentService->getAll();

        $this->render('students/create', [
            'pageTitle'   => 'Register New Student',
            'departments' => $departments
        ]);
    }

    public function store(StoreStudentRequest $request): void
    {
        $studentId = $this->studentService->createStudent($request->toDTO(), auth_id());
        $this->redirectWith("/students/{$studentId}", 'success', 'Student record successfully created!');
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $student = $this->studentService->getById($id);

        $this->gate->authorize('update', $student);

        $departments = $this->departmentService->getAll();

        $this->render('students/edit', [
            'pageTitle'   => "Edit Student: {$student['first_name']} {$student['last_name']}",
            'student'     => $student,
            'departments' => $departments
        ]);
    }

    public function update(UpdateStudentRequest $request): void
    {
        $id = (int)$request->param('id');
        $this->studentService->updateStudent($id, $request->toDTO(), auth_id());
        $this->redirectWith("/students/{$id}", 'success', 'Student profile updated successfully!');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');
        $this->gate->authorize('delete', 'student');

        $this->studentService->archiveStudent($id, auth_id());
        $this->redirectWith('/students', 'success', 'Student record archived.');
    }
}
