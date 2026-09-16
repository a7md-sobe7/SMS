<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Repositories\CourseRepository;
use App\Repositories\AuditLogRepository;

class ApiCourseController extends Controller
{
    private CourseRepository $courseRepo;
    private AuditLogRepository $auditRepo;

    public function __construct()
    {
        $this->courseRepo = new CourseRepository();
        $this->auditRepo = new AuditLogRepository();
    }

    public function index(Request $request): void
    {
        $deptId = $request->input('department_id') ? (int)$request->input('department_id') : null;
        $courses = $this->courseRepo->findAllWithDetails($deptId);
        json_response($courses, 200);
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $course = $this->courseRepo->findWithDetails($id);

        if (!$course) {
            json_response(null, 404, 'Course not found.');
        }

        json_response($course, 200);
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
            json_response(null, 422, 'Validation failed.', $validator->errors());
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

        $this->auditRepo->log(auth_id(), 'API_COURSE_CREATED', 'Course', $id);
        $course = $this->courseRepo->findWithDetails($id);

        json_response($course, 201, 'Course created successfully.');
    }
}
