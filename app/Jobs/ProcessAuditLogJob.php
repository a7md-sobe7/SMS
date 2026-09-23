<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Core\Container;
use App\Core\Queue\JobInterface;

class ProcessAuditLogJob implements JobInterface
{
    public function __construct(
        public readonly ?int $userId,
        public readonly string $action,
        public readonly ?string $entityType = null,
        public readonly ?int $entityId = null,
        public readonly array $details = [],
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null
    ) {}

    public function handle(): void
    {
        $auditRepo = Container::getInstance()->make(AuditLogRepositoryInterface::class);
        $auditRepo->log(
            $this->userId,
            $this->action,
            $this->entityType,
            $this->entityId,
            $this->details,
            $this->ipAddress,
            $this->userAgent
        );
    }
}
