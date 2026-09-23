<?php

declare(strict_types=1);

namespace App\DTOs\Course;

use App\DTOs\BaseDTO;

class CourseDTO extends BaseDTO
{
    public function __construct(
        public readonly string $course_code,
        public readonly string $course_name,
        public readonly int $department_id,
        public readonly int $credit_hours,
        public readonly string $semester,
        public readonly string $academic_year,
        public readonly int $max_capacity = 40,
        public readonly ?int $instructor_id = null,
        public readonly ?string $description = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            course_code: strtoupper(trim((string)($data['course_code'] ?? ''))),
            course_name: trim((string)($data['course_name'] ?? '')),
            department_id: (int)($data['department_id'] ?? 0),
            credit_hours: (int)($data['credit_hours'] ?? 3),
            semester: (string)($data['semester'] ?? 'Fall'),
            academic_year: (string)($data['academic_year'] ?? date('Y') . '-' . (date('Y') + 1)),
            max_capacity: (int)($data['max_capacity'] ?? 40),
            instructor_id: !empty($data['instructor_id']) ? (int)$data['instructor_id'] : null,
            description: !empty($data['description']) ? trim((string)$data['description']) : null
        );
    }
}
