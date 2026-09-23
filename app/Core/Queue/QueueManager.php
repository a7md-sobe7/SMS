<?php

declare(strict_types=1);

namespace App\Core\Queue;

/**
 * Queue Driver & Dispatch Manager
 * 
 * Enqueues asynchronous background jobs and manages queue persistence.
 */
class QueueManager
{
    private static ?self $instance = null;
    private string $storageDir;

    public function __construct(?string $storageDir = null)
    {
        self::$instance = $this;
        $this->storageDir = $storageDir ?? dirname(__DIR__, 2) . '/storage/framework/queues';

        if (!is_dir($this->storageDir)) {
            @mkdir($this->storageDir, 0777, true);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Push a new job onto the queue.
     */
    public function push(JobInterface $job, string $queue = 'default'): void
    {
        $queueFile = $this->storageDir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $queue) . '.queue';
        $payload = serialize($job) . "\n--JOB_DELIMITER--\n";

        file_put_contents($queueFile, $payload, FILE_APPEND | LOCK_EX);
    }

    /**
     * Pop the next job from the queue.
     */
    public function pop(string $queue = 'default'): ?JobInterface
    {
        $queueFile = $this->storageDir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $queue) . '.queue';

        if (!file_exists($queueFile) || filesize($queueFile) === 0) {
            return null;
        }

        $fp = fopen($queueFile, 'c+');
        if (!$fp) {
            return null;
        }

        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return null;
        }

        $content = stream_get_contents($fp);
        $jobs = explode("\n--JOB_DELIMITER--\n", trim($content));
        $first = array_shift($jobs);

        // Rewrite remaining jobs
        ftruncate($fp, 0);
        rewind($fp);
        if (!empty($jobs)) {
            fwrite($fp, implode("\n--JOB_DELIMITER--\n", $jobs) . "\n--JOB_DELIMITER--\n");
        }
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        if ($first !== null && $first !== '') {
            $unserialized = @unserialize($first);
            if ($unserialized instanceof JobInterface) {
                return $unserialized;
            }
        }

        return null;
    }

    /**
     * Get count of pending jobs.
     */
    public function count(string $queue = 'default'): int
    {
        $queueFile = $this->storageDir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $queue) . '.queue';
        if (!file_exists($queueFile)) {
            return 0;
        }
        $content = trim(file_get_contents($queueFile));
        if ($content === '') {
            return 0;
        }
        return count(explode("\n--JOB_DELIMITER--\n", $content));
    }
}
