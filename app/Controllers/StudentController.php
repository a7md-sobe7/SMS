<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\StudentRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\GradeRepository;
use App\Repositories\AuditLogRepository;

class StudentController extends Controller
{
    private StudentRepository $studentRepo;
    private DepartmentRepository $deptRepo;
    private EnrollmentRepository $enrollmentRepo;
    private GradeRepository $gradeRepo;
    private AuditLogRepository $auditRepo;

    public function __construct()
    {
        $this->studentRepo = new StudentRepository();
        $this->deptRepo = new DepartmentRepository();
        $this->enrollmentRepo = new EnrollmentRepository();
        $this->gradeRepo = new GradeRepository();
        $this->auditRepo = new AuditLogRepository();
    }

    public function index(Request $request): void
    {
        $search     = (string)$request->input('search', '');
        $deptId     = $request->input('department_id') ? (int)$request->input('department_id') : null;
        $level      = (string)$request->input('level', '');
        $status     = (string)$request->input('status', '');
        $page       = (int)$request->input('page', 1);

        $studentsData = $this->studentRepo->searchAndFilter(
            $search,
            $deptId,
            $level ?: null,
            $status ?: null,
            $page,
            10
        );

        $departments = $this->deptRepo->findAll('name', 'ASC');

        $this->render('students/index', [
            'pageTitle'   => 'Student Directory',
            'students'    => $studentsData['data'],
            'pagination'  => $studentsData['pagination'],
            'departments' => $departments,
            'filters'     => [
                'search'        => $search,
                'department_id' => $deptId,
                'level'         => $level,
                'status'        => $status
            ]
        ]);
    }

    public function show(Request $request): void
    {
        $id = (int)$request->param('id');
        $student = $this->studentRepo->findWithDepartment($id);

        if (!$student) {
            http_response_code(404);
            view('errors/404', ['message' => 'Student record not found.'], 'main');
            return;
        }

        $enrollments = $this->enrollmentRepo->getStudentEnrollments($id);
        $gpaSummary  = $this->gradeRepo->getStudentGpaSummary($id);

        $this->render('students/show', [
            'pageTitle'   => "Student Profile: {$student['first_name']} {$student['last_name']}",
            'student'     => $student,
            'enrollments' => $enrollments,
            'gpa'         => $gpaSummary
        ]);
    }

    public function create(Request $request): void
    {
        $departments = $this->deptRepo->findAll('name', 'ASC');

        $this->render('students/create', [
            'pageTitle'   => 'Register New Student',
            'departments' => $departments
        ]);
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
            Session::flash('errors', $validator->errors());
            Session::flash('_old_input', $data);
            Session::flash('error', 'Please correct the highlighted form errors.');
            redirect('/students/create');
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

        $this->auditRepo->log(
            auth_id(),
            'STUDENT_CREATED',
            'Student',
            $studentId,
            ['student_code' => $data['student_code'], 'name' => "{$data['first_name']} {$data['last_name']}"]
        );

        $this->redirectWith("/students/{$studentId}", 'success', 'Student record successfully created!');
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->param('id');
        $student = $this->studentRepo->find($id);

        if (!$student) {
            http_response_code(404);
            view('errors/404', ['message' => 'Student record not found.'], 'main');
            return;
        }

        $departments = $this->deptRepo->findAll('name', 'ASC');

        $this->render('students/edit', [
            'pageTitle'   => "Edit Student: {$student['first_name']} {$student['last_name']}",
            'student'     => $student,
            'departments' => $departments
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $student = $this->studentRepo->find($id);

        if (!$student) {
            $this->redirectWith('/students', 'error', 'Student not found.');
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
            Session::flash('errors', $validator->errors());
            Session::flash('_old_input', $data);
            Session::flash('error', 'Please resolve the highlighted errors.');
            redirect("/students/{$id}/edit");
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

        $this->auditRepo->log(
            auth_id(),
            'STUDENT_UPDATED',
            'Student',
            $id,
            ['student_code' => $data['student_code']]
        );

        $this->redirectWith("/students/{$id}", 'success', 'Student profile updated successfully!');
    }

    public function destroy(Request $request): void
    {
        $id = (int)$request->param('id');
        $student = $this->studentRepo->find($id);

        if (!$student) {
            $this->redirectWith('/students', 'error', 'Student record not found.');
        }

        // Soft archive or delete
        $this->studentRepo->update($id, ['status' => 'withdrawn']);

        $this->auditRepo->log(
            auth_id(),
            'STUDENT_ARCHIVED',
            'Student',
            $id,
            ['student_code' => $student['student_code']]
        );

        $this->redirectWith('/students', 'success', 'Student record archived.');
    }
}
