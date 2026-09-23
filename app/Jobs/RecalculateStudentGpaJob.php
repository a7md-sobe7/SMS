<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Core\Container;
use App\Core\Queue\JobInterface;

class RecalculateStudentGpaJob implements JobInterface
{
    public function __construct(public readonly int $studentId) {}

    public function handle(): void
    {
        $gradeRepo = Container::getInstance()->make(GradeRepositoryInterface::class);
        $summary = $gradeRepo->getStudentGpaSummary($this->studentId);

        error_log(sprintf(
            "[%s] [GPA RECALCULATION] Student #%d recalculated: Avg Numerical Grade: %s, Credits: %s",
            date('Y-m-d H:i:s'),
            $this->studentId,
            $summary['average_numerical_grade'] ?? '0.0',
            $summary['total_credits_earned'] ?? '0'
        ));
    }
}
