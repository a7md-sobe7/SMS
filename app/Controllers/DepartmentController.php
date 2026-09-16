<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\DepartmentRepository;
use App\Repositories\AuditLogRepository;

class DepartmentController extends Controller
{
    private DepartmentRepository $deptRepo;
    private AuditLogRepository $auditRepo;

    public function __construct()
    {
        $this->deptRepo = new DepartmentRepository();
        $this->auditRepo = new AuditLogRepository();
    }

    public function index(Request $request): void
    {
        $departments = $this->deptRepo->getWithStatistics();

        $this->render('departments/index', [
            'pageTitle'   => 'Academic Departments',
            'departments' => $departments
        ]);
    }

    public function store(Request $request): void
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'code' => 'required|min:2|max:10|unique:departments,code',
            'name' => 'required|min:3|max:100',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('_old_input', $data);
            Session::flash('error', 'Please resolve department form errors.');
            redirect('/departments');
        }

        $id = $this->deptRepo->create([
            'code'        => strtoupper(trim($data['code'])),
            'name'        => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
        ]);

        $this->auditRepo->log(auth_id(), 'DEPARTMENT_CREATED', 'Department', $id, ['code' => $data['code']]);
        $this->redirectWith('/departments', 'success', 'Department created successfully!');
    }

    public function update(Request $request): void
    {
        $id = (int)$request->param('id');
        $dept = $this->deptRepo->find($id);

        if (!$dept) {
            $this->redirectWith('/departments', 'error', 'Department not found.');
        }

        $data = $request->all();

        $validator = Validator::make($data, [
            'code' => "required|min:2|max:10|unique:departments,code,{$id},id",
            'name' => 'required|min:3|max:100',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('error', 'Please resolve department update errors.');
            redirect('/departments');
        }

        $this->deptRepo->update($id, [
            'code'        => strtoupper(trim($data['code'])),
            'name'        => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
        ]);

        $this->auditRepo->log(auth_id(), 'DEPARTMENT_UPDATED', 'Department', $id, ['code' => $data['code']]);
        $this->redirectWith('/departments', 'success', 'Department updated successfully!');
    }
}
