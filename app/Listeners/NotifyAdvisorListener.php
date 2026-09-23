<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Core\Queue\QueueManager;
use App\Events\CourseCapacityReachedEvent;
use App\Events\StudentEnrolledEvent;
use App\Jobs\SendEmailNotificationJob;

class NotifyAdvisorListener
{
    private QueueManager $queue;

    public function __construct(QueueManager $queue)
    {
        $this->queue = $queue;
    }

    public function handle(object $event): void
    {
        if ($event instanceof CourseCapacityReachedEvent) {
            $job = new SendEmailNotificationJob(
                recipientEmail: 'registrar@sms.edu',
                subject: "ALERT: Course {$event->courseCode} has reached max capacity ({$event->capacity})",
                messageBody: "Course ID #{$event->courseId} ({$event->courseCode}) is now completely filled."
            );
            $this->queue->push($job);
            $job->handle();
        }
    }
}
