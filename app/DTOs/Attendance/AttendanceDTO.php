<?php

declare(strict_types=1);

namespace App\DTOs\Attendance;

use App\DTOs\BaseDTO;

class AttendanceDTO extends BaseDTO
{
    public function __construct(
        public readonly int $student_id,
        public readonly int $course_id,
        public readonly string $attendance_date,
        public readonly string $status = 'present',
        public readonly ?string $notes = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            student_id: (int)($data['student_id'] ?? 0),
            course_id: (int)($data['course_id'] ?? 0),
            attendance_date: (string)($data['attendance_date'] ?? date('Y-m-d')),
            status: (string)($data['status'] ?? 'present'),
            notes: !empty($data['notes']) ? trim((string)$data['notes']) : null
        );
    }
}
