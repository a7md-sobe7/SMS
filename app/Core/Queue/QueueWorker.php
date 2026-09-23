<?php

declare(strict_types=1);

namespace App\Core\Queue;

use Throwable;

/**
 * Queue Worker CLI / Background Runner
 * 
 * Consumes and executes serialized jobs from queue queues.
 */
class QueueWorker
{
    private QueueManager $manager;

    public function __construct(?QueueManager $manager = null)
    {
        $this->manager = $manager ?? QueueManager::getInstance();
    }

    /**
     * Process a single job from the specified queue.
     */
    public function processNext(string $queue = 'default'): bool
    {
        $job = $this->manager->pop($queue);

        if ($job === null) {
            return false;
        }

        try {
            $job->handle();
            return true;
        } catch (Throwable $e) {
            error_log(sprintf(
                "[%s] Queue Job [%s] Failed: %s in %s:%d",
                date('Y-m-d H:i:s'),
                get_class($job),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
            return false;
        }
    }

    /**
     * Drain and execute all pending jobs in the queue.
     */
    public function drain(string $queue = 'default', int $maxJobs = 100): int
    {
        $count = 0;
        while ($count < $maxJobs && $this->processNext($queue)) {
            $count++;
        }
        return $count;
    }
}
