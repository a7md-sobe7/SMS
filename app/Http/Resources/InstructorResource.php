<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Core\Request;

class InstructorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $inst = (array)$this->resource;

        return [
            'id'              => (int)($inst['id'] ?? 0),
            'employee_code'   => (string)($inst['employee_code'] ?? ''),
            'first_name'      => (string)($inst['first_name'] ?? ''),
            'last_name'       => (string)($inst['last_name'] ?? ''),
            'full_name'       => trim(($inst['first_name'] ?? '') . ' ' . ($inst['last_name'] ?? '')),
            'email'           => (string)($inst['email'] ?? ''),
            'phone'           => $inst['phone'] ?? null,
            'department_id'   => (int)($inst['department_id'] ?? 0),
            'department_name' => $inst['department_name'] ?? null,
            'department_code' => $inst['department_code'] ?? null,
            'hire_date'       => (string)($inst['hire_date'] ?? ''),
            'office_location' => $inst['office_location'] ?? null,
            'specialization'  => $inst['specialization'] ?? null,
        ];
    }
}
