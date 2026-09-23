<?php

declare(strict_types=1);

namespace App\Events;

class StudentUpdatedEvent
{
    public function __construct(
        public readonly int $studentId,
        public readonly string $studentCode,
        public readonly array $changes = [],
        public readonly ?int $performedByUserId = null
    ) {}
}
