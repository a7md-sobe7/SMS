<?php

declare(strict_types=1);

namespace App\Events;

class StudentEnrolledEvent
{
    public function __construct(
        public readonly int $enrollmentId,
        public readonly int $studentId,
        public readonly int $courseId,
        public readonly ?int $performedByUserId = null
    ) {}
}
