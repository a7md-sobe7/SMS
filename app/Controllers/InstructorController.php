<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\InstructorRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\AuditLogRepository;

class InstructorController extends Controller
{
    private InstructorRepository $instructorRepo;
    private DepartmentRepository $deptRepo;
    private AuditLogRepository $auditRepo;

    public function __construct()
    {
        $this->instructorRepo = new InstructorRepository();
        $this->deptRepo = new DepartmentRepository();
        $this->auditRepo = new AuditLogRepository();
    }

    public function index(Request $request): void
    {
        $instructors = $this->instructorRepo->findAllWithDepartments();
        $departments = $this->deptRepo->findAll('name', 'ASC');

        $this->render('instructors/index', [
            'pageTitle'   => 'Faculty & Instructors Directory',
            'instructors' => $instructors,
            'departments' => $departments
        ]);
    }

    public function store(Request $request): void
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'employee_code' => 'required|unique:instructors,employee_code',
            'first_name'    => 'required|min:2|max:50',
            'last_name'     => 'required|min:2|max:50',
            'email'         => 'required|email|unique:instructors,email',
            'department_id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('_old_input', $data);
            Session::flash('error', 'Please resolve instructor form errors.');
            redirect('/instructors');
        }

        $id = $this->instructorRepo->create([
            'employee_code' => strtoupper(trim($data['employee_code'])),
            'first_name'    => trim($data['first_name']),
            'last_name'     => trim($data['last_name']),
            'email'         => strtolower(trim($data['email'])),
            'phone'         => trim($data['phone'] ?? ''),
            'department_id' => (int)$data['department_id']
        ]);

        $this->auditRepo->log(auth_id(), 'INSTRUCTOR_CREATED', 'Instructor', $id, ['code' => $data['employee_code']]);
        $this->redirectWith('/instructors', 'success', 'Instructor successfully added.');
    }
}
