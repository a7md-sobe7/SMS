<?php

declare(strict_types=1);

namespace App\Events;

class AttendanceRecordedEvent
{
    public function __construct(
        public readonly int $studentId,
        public readonly int $courseId,
        public readonly string $date,
        public readonly string $status,
        public readonly ?int $performedByUserId = null
    ) {}
}
