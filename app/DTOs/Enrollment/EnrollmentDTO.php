<?php

declare(strict_types=1);

namespace App\DTOs\Enrollment;

use App\DTOs\BaseDTO;

class EnrollmentDTO extends BaseDTO
{
    public function __construct(
        public readonly int $student_id,
        public readonly int $course_id,
        public readonly string $enrollment_date,
        public readonly string $status = 'enrolled'
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            student_id: (int)($data['student_id'] ?? 0),
            course_id: (int)($data['course_id'] ?? 0),
            enrollment_date: (string)($data['enrollment_date'] ?? date('Y-m-d')),
            status: (string)($data['status'] ?? 'enrolled')
        );
    }
}
