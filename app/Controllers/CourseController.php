<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Gate;
use App\Core\Request;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Services\CourseService;
use App\Services\DepartmentService;
use App\Services\InstructorService;

class CourseController extends Controller
{
    public function __construct(
        private CourseService $courseService,
        private DepartmentService $deptService,
        private InstructorService $instructorService,
        private Gate $gate
    ) {}

    public function index(Request $request): void
    {
        $this->gate->authorize('viewAny', 'course');

        $deptId = $request->input('department_id') ? (int)$request->input('department_id') : null;
        $courses = $this->courseService->listCourses($deptId);
        $departments = $this->deptService->getAll();
        $instructors = $this->instructorService->getAllWithDepartments();

        $this->render('courses/index', [
            'pageTitle'   => 'Course Catalog & Offerings',
            'courses'     => $courses,
            'departments' => $departments,
            'instructors' => $instructors,
            'deptId'      => $deptId
        ]);
    }

    public function store(StoreCourseRequest $request): void
    {
        $this->courseService->createCourse($request->toDTO(), auth_id());
        $this->redirectWith('/courses', 'success', 'Course created successfully!');
    }

    public function update(UpdateCourseRequest $request): void
    {
        $id = (int)$request->param('id');
        $this->courseService->updateCourse($id, $request->toDTO(), auth_id());
        $this->redirectWith('/courses', 'success', 'Course updated successfully!');
    }
}
