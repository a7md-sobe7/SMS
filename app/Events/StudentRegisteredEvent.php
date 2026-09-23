<?php

declare(strict_types=1);

namespace App\Events;

class StudentRegisteredEvent
{
    public function __construct(
        public readonly int $studentId,
        public readonly string $studentCode,
        public readonly string $name,
        public readonly string $email,
        public readonly ?int $performedByUserId = null
    ) {}
}
