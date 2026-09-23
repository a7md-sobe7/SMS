<?php

declare(strict_types=1);

namespace App\Events;

class CourseCapacityReachedEvent
{
    public function __construct(
        public readonly int $courseId,
        public readonly string $courseCode,
        public readonly int $capacity
    ) {}
}
