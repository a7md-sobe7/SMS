<?php

declare(strict_types=1);

namespace App\DTOs\Student;

use App\DTOs\BaseDTO;

class StudentDTO extends BaseDTO
{
    public function __construct(
        public readonly string $student_code,
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $email,
        public readonly int $department_id,
        public readonly string $date_of_birth,
        public readonly string $gender,
        public readonly int $enrollment_year,
        public readonly string $academic_level,
        public readonly string $status = 'active',
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
        public readonly ?int $user_id = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            student_code: strtoupper(trim((string)($data['student_code'] ?? ''))),
            first_name: trim((string)($data['first_name'] ?? '')),
            last_name: trim((string)($data['last_name'] ?? '')),
            email: strtolower(trim((string)($data['email'] ?? ''))),
            department_id: (int)($data['department_id'] ?? 0),
            date_of_birth: (string)($data['date_of_birth'] ?? ''),
            gender: (string)($data['gender'] ?? 'male'),
            enrollment_year: (int)($data['enrollment_year'] ?? date('Y')),
            academic_level: (string)($data['academic_level'] ?? 'freshman'),
            status: (string)($data['status'] ?? 'active'),
            phone: !empty($data['phone']) ? trim((string)$data['phone']) : null,
            address: !empty($data['address']) ? trim((string)$data['address']) : null,
            user_id: !empty($data['user_id']) ? (int)$data['user_id'] : null
        );
    }
}
