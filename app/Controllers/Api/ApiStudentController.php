<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Repositories\StudentRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\GradeRepository;
use App\Repositories\AuditLogRepository;

class ApiStudentController extends Controller
{
    private StudentRepository $studentRepo;
    private EnrollmentRepository $enrollmentRepo;
    private GradeRepository $gradeRepo;
    private AuditLogRepository $auditRepo;

    public function __construct()
    {
        $this->studentRepo = new StudentRepository();
        $this->enrollmentRepo = new EnrollmentRepository();
        $this->gradeRepo = new GradeRepository();
        $this->auditRepo = new AuditLogRepository();
    }

    public function index(Request $request): void
    {
        $search = (string)$request->input('search', '');
        $deptId = $request->input('department_id') ? (int)$request->input('department_id') : null;
        $level  = (string)$request->input('level', '');
        $status = (string)$request->input('status', '');
        $page   = (int)$request->input('page', 1);

        $result = $this->studentRepo->searchAndFilter(
            $search,
            $deptId,
            $level ?: null,
            $status ?: null,
            $page,
            15
        );

        json_response($result['data'], 200, 'Students retrieved successfully.', [], $result['pagination']);
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $student = $this->studentRepo->findWithDepartment($id);

        if (!$student) {
            json_response(null, 404, 'Student record not found.');
        }

        $student['enrollments'] = $this->enrollmentRepo->getStudentEnrollments($id);
        $student['gpa_summary'] = $this->gradeRepo->getStudentGpaSummary($id);

        json_response($student, 200);
    }

    public function store(Request $request): void
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'student_code'    => 'required|unique:students,student_code',
            'first_name'      => 'required|min:2|max:50',
            'last_name'       => 'required|min:2|max:50',
            'email'           => 'required|email|unique:students,email',
            'department_id'   => 'required|numeric',
            'date_of_birth'   => 'required|date',
            'gender'          => 'required|in:male,female,other',
            'enrollment_year' => 'required|numeric',
            'academic_level'  => 'required|in:freshman,sophomore,junior,senior,graduate',
            'status'          => 'required|in:active,suspended,graduated,withdrawn'
        ]);

        if ($validator->fails()) {
            json_response(null, 422, 'Validation errors occurred.', $validator->errors());
        }

        $studentId = $this->studentRepo->create([
            'student_code'    => strtoupper(trim($data['student_code'])),
            'first_name'      => trim($data['first_name']),
            'last_name'       => trim($data['last_name']),
            'email'           => strtolower(trim($data['email'])),
            'phone'           => trim($data['phone'] ?? ''),
            'department_id'   => (int)$data['department_id'],
            'date_of_birth'   => $data['date_of_birth'],
            'gender'          => $data['gender'],
            'address'         => trim($data['address'] ?? ''),
            'enrollment_year' => (int)$data['enrollment_year'],
            'academic_level'  => $data['academic_level'],
            'status'          => $data['status'],
        ]);

        $this->auditRepo->log(auth_id(), 'API_STUDENT_CREATED', 'Student', $studentId);

        $created = $this->studentRepo->findWithDepartment($studentId);
        json_response($created, 201, 'Student created successfully.');
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $student = $this->studentRepo->find($id);

        if (!$student) {
            json_response(null, 404, 'Student not found.');
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'student_code'    => "required|unique:students,student_code,{$id},id",
            'first_name'      => 'required|min:2|max:50',
            'last_name'       => 'required|min:2|max:50',
            'email'           => "required|email|unique:students,email,{$id},id",
            'department_id'   => 'required|numeric',
            'date_of_birth'   => 'required|date',
            'gender'          => 'required|in:male,female,other',
            'enrollment_year' => 'required|numeric',
            'academic_level'  => 'required|in:freshman,sophomore,junior,senior,graduate',
            'status'          => 'required|in:active,suspended,graduated,withdrawn'
        ]);

        if ($validator->fails()) {
            json_response(null, 422, 'Validation errors occurred.', $validator->errors());
        }

        $this->studentRepo->update($id, [
            'student_code'    => strtoupper(trim($data['student_code'])),
            'first_name'      => trim($data['first_name']),
            'last_name'       => trim($data['last_name']),
            'email'           => strtolower(trim($data['email'])),
            'phone'           => trim($data['phone'] ?? ''),
            'department_id'   => (int)$data['department_id'],
            'date_of_birth'   => $data['date_of_birth'],
            'gender'          => $data['gender'],
            'address'         => trim($data['address'] ?? ''),
            'enrollment_year' => (int)$data['enrollment_year'],
            'academic_level'  => $data['academic_level'],
            'status'          => $data['status'],
        ]);

        $this->auditRepo->log(auth_id(), 'API_STUDENT_UPDATED', 'Student', $id);

        $updated = $this->studentRepo->findWithDepartment($id);
        json_response($updated, 200, 'Student updated successfully.');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');
        $student = $this->studentRepo->find($id);

        if (!$student) {
            json_response(null, 404, 'Student not found.');
        }

        $this->studentRepo->update($id, ['status' => 'withdrawn']);
        $this->auditRepo->log(auth_id(), 'API_STUDENT_ARCHIVED', 'Student', $id);

        json_response(null, 200, 'Student archived successfully.');
    }
}
