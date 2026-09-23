<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Core\Request;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = (array)$this->resource;

        return [
            'id'              => (int)($student['id'] ?? 0),
            'student_code'    => (string)($student['student_code'] ?? ''),
            'first_name'      => (string)($student['first_name'] ?? ''),
            'last_name'       => (string)($student['last_name'] ?? ''),
            'full_name'       => trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')),
            'email'           => (string)($student['email'] ?? ''),
            'phone'           => $student['phone'] ?? null,
            'department_id'   => (int)($student['department_id'] ?? 0),
            'department_name' => $student['department_name'] ?? null,
            'department_code' => $student['department_code'] ?? null,
            'date_of_birth'   => (string)($student['date_of_birth'] ?? ''),
            'gender'          => (string)($student['gender'] ?? ''),
            'address'         => $student['address'] ?? null,
            'enrollment_year' => (int)($student['enrollment_year'] ?? 0),
            'academic_level'  => (string)($student['academic_level'] ?? ''),
            'status'          => (string)($student['status'] ?? 'active'),
            'created_at'      => (string)($student['created_at'] ?? ''),
        ];
    }
}
