<?php

declare(strict_types=1);

namespace App\DTOs\Grade;

use App\DTOs\BaseDTO;

class GradeDTO extends BaseDTO
{
    public function __construct(
        public readonly int $enrollment_id,
        public readonly float $assignment_grade = 0.0,
        public readonly float $midterm_grade = 0.0,
        public readonly float $final_grade = 0.0,
        public readonly ?float $total_grade = null,
        public readonly ?string $letter_grade = null,
        public readonly ?string $remarks = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enrollment_id: (int)($data['enrollment_id'] ?? 0),
            assignment_grade: (float)($data['assignment_grade'] ?? 0.0),
            midterm_grade: (float)($data['midterm_grade'] ?? 0.0),
            final_grade: (float)($data['final_grade'] ?? 0.0),
            total_grade: isset($data['total_grade']) ? (float)$data['total_grade'] : null,
            letter_grade: !empty($data['letter_grade']) ? (string)$data['letter_grade'] : null,
            remarks: !empty($data['remarks']) ? trim((string)$data['remarks']) : null
        );
    }
}
