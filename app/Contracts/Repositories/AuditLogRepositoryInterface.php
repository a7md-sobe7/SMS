<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

interface AuditLogRepositoryInterface
{
    public function find(int $id): ?array;
    public function log(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null, array $details = []): int;
    public function getRecent(int $limit = 20): array;
    public function paginate(int $page = 1, int $perPage = 15, array $where = [], string $orderBy = 'id', string $direction = 'DESC'): array;
}
