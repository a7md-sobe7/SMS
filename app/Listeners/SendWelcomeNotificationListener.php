<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Core\Queue\QueueManager;
use App\Events\StudentRegisteredEvent;
use App\Jobs\SendEmailNotificationJob;

class SendWelcomeNotificationListener
{
    private QueueManager $queue;

    public function __construct(QueueManager $queue)
    {
        $this->queue = $queue;
    }

    public function handle(object $event): void
    {
        if ($event instanceof StudentRegisteredEvent) {
            $job = new SendEmailNotificationJob(
                recipientEmail: $event->email,
                subject: "Welcome to Student Management System, {$event->name}!",
                messageBody: "Your student registration (#{$event->studentCode}) has been confirmed."
            );

            $this->queue->push($job);
            $job->handle();
        }
    }
}
