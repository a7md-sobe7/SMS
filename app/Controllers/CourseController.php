<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\CourseRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\InstructorRepository;
use App\Repositories\AuditLogRepository;

class CourseController extends Controller
{
    private CourseRepository $courseRepo;
    private DepartmentRepository $deptRepo;
    private InstructorRepository $instructorRepo;
    private AuditLogRepository $auditRepo;

    public function __construct()
    {
        $this->courseRepo = new CourseRepository();
        $this->deptRepo = new DepartmentRepository();
        $this->instructorRepo = new InstructorRepository();
        $this->auditRepo = new AuditLogRepository();
    }

    public function index(Request $request): void
    {
        $deptId = $request->input('department_id') ? (int)$request->input('department_id') : null;
        $courses = $this->courseRepo->findAllWithDetails($deptId);
        $departments = $this->deptRepo->findAll('name', 'ASC');
        $instructors = $this->instructorRepo->findAll('last_name', 'ASC');

        $this->render('courses/index', [
            'pageTitle'   => 'Course Catalog & Offerings',
            'courses'     => $courses,
            'departments' => $departments,
            'instructors' => $instructors,
            'deptId'      => $deptId
        ]);
    }

    public function store(Request $request): void
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'course_code'   => 'required|min:3|max:20',
            'course_name'   => 'required|min:3|max:100',
            'department_id' => 'required|numeric',
            'credit_hours'  => 'required|numeric|min:1|max:6',
            'semester'      => 'required|in:Fall,Spring,Summer',
            'academic_year' => 'required|numeric',
            'capacity'      => 'required|numeric|min:1|max:500'
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('_old_input', $data);
            Session::flash('error', 'Please resolve course form errors.');
            redirect('/courses');
        }

        $id = $this->courseRepo->create([
            'course_code'   => strtoupper(trim($data['course_code'])),
            'course_name'   => trim($data['course_name']),
            'description'   => trim($data['description'] ?? ''),
            'department_id' => (int)$data['department_id'],
            'instructor_id' => !empty($data['instructor_id']) ? (int)$data['instructor_id'] : null,
            'credit_hours'  => (int)$data['credit_hours'],
            'semester'      => $data['semester'],
            'academic_year' => (int)$data['academic_year'],
            'capacity'      => (int)$data['capacity'],
        ]);

        $this->auditRepo->log(auth_id(), 'COURSE_CREATED', 'Course', $id, ['code' => $data['course_code']]);
        $this->redirectWith('/courses', 'success', 'Course created successfully!');
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $course = $this->courseRepo->find($id);

        if (!$course) {
            $this->redirectWith('/courses', 'error', 'Course not found.');
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'course_name'   => 'required|min:3|max:100',
            'department_id' => 'required|numeric',
            'credit_hours'  => 'required|numeric|min:1|max:6',
            'capacity'      => 'required|numeric|min:1|max:500'
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('error', 'Please resolve course update errors.');
            redirect('/courses');
        }

        $this->courseRepo->update($id, [
            'course_name'   => trim($data['course_name']),
            'description'   => trim($data['description'] ?? ''),
            'department_id' => (int)$data['department_id'],
            'instructor_id' => !empty($data['instructor_id']) ? (int)$data['instructor_id'] : null,
            'credit_hours'  => (int)$data['credit_hours'],
            'capacity'      => (int)$data['capacity'],
        ]);

        $this->auditRepo->log(auth_id(), 'COURSE_UPDATED', 'Course', $id, ['name' => $data['course_name']]);
        $this->redirectWith('/courses', 'success', 'Course updated successfully!');
    }
}
