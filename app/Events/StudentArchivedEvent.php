<?php

declare(strict_types=1);

namespace App\Events;

class StudentArchivedEvent
{
    public function __construct(
        public readonly int $studentId,
        public readonly string $studentCode,
        public readonly ?int $performedByUserId = null
    ) {}
}
