<?php

declare(strict_types=1);

namespace App\Core\Queue;

/**
 * Interface for Background Queueable Jobs
 */
interface JobInterface
{
    /**
     * Execute the job logic.
     */
    public function handle(): void;
}
