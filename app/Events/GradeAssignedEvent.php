<?php

declare(strict_types=1);

namespace App\Events;

class GradeAssignedEvent
{
    public function __construct(
        public readonly int $gradeId,
        public readonly int $enrollmentId,
        public readonly float $totalGrade,
        public readonly string $letterGrade,
        public readonly ?int $performedByUserId = null
    ) {}
}
