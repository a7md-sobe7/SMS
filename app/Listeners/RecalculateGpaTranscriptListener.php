<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Core\Queue\QueueManager;
use App\Events\GradeAssignedEvent;
use App\Jobs\RecalculateStudentGpaJob;

class RecalculateGpaTranscriptListener
{
    private QueueManager $queue;
    private EnrollmentRepositoryInterface $enrollmentRepo;

    public function __construct(QueueManager $queue, EnrollmentRepositoryInterface $enrollmentRepo)
    {
        $this->queue = $queue;
        $this->enrollmentRepo = $enrollmentRepo;
    }

    public function handle(object $event): void
    {
        if ($event instanceof GradeAssignedEvent) {
            $enrollment = $this->enrollmentRepo->find($event->enrollmentId);
            if ($enrollment !== null && isset($enrollment['student_id'])) {
                $job = new RecalculateStudentGpaJob((int)$enrollment['student_id']);
                $this->queue->push($job);
                $job->handle();
            }
        }
    }
}
