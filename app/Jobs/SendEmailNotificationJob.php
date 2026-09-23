<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Core\Queue\JobInterface;

class SendEmailNotificationJob implements JobInterface
{
    public function __construct(
        public readonly string $recipientEmail,
        public readonly string $subject,
        public readonly string $messageBody
    ) {}

    public function handle(): void
    {
        // Simulation / logging of outgoing email dispatch
        error_log(sprintf(
            "[%s] [QUEUE EMAIL DISPATCH] Sent to <%s>: Subject '%s'",
            date('Y-m-d H:i:s'),
            $this->recipientEmail,
            $this->subject
        ));
    }
}
