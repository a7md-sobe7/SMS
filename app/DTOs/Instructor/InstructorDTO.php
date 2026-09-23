<?php

declare(strict_types=1);

namespace App\DTOs\Instructor;

use App\DTOs\BaseDTO;

class InstructorDTO extends BaseDTO
{
    public function __construct(
        public readonly string $employee_code,
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $email,
        public readonly int $department_id,
        public readonly string $hire_date,
        public readonly ?string $phone = null,
        public readonly ?string $office_location = null,
        public readonly ?string $specialization = null,
        public readonly ?int $user_id = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            employee_code: strtoupper(trim((string)($data['employee_code'] ?? ''))),
            first_name: trim((string)($data['first_name'] ?? '')),
            last_name: trim((string)($data['last_name'] ?? '')),
            email: strtolower(trim((string)($data['email'] ?? ''))),
            department_id: (int)($data['department_id'] ?? 0),
            hire_date: (string)($data['hire_date'] ?? date('Y-m-d')),
            phone: !empty($data['phone']) ? trim((string)$data['phone']) : null,
            office_location: !empty($data['office_location']) ? trim((string)$data['office_location']) : null,
            specialization: !empty($data['specialization']) ? trim((string)$data['specialization']) : null,
            user_id: !empty($data['user_id']) ? (int)$data['user_id'] : null
        );
    }
}
