<?php

declare(strict_types=1);

namespace App\Repositories;

class AuditLogRepository extends BaseRepository
{
    protected string $table = 'audit_logs';

    /**
     * Log a security or data mutation event.
     */
    public function log(
        ?int $userId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        array $details = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): int {
        return $this->create([
            'user_id'     => $userId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'details'     => !empty($details) ? json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
            'ip_address'  => $ipAddress ?? ($_SERVER['REMOTE_ADDR'] ?? null),
            'user_agent'  => $userAgent ?? ($_SERVER['HTTP_USER_AGENT'] ?? null),
        ]);
    }

    /**
     * Retrieve recent system audit logs with user information.
     */
    public function getRecentLogs(int $limit = 50): array
    {
        $sql = "
            SELECT 
                a.*,
                u.username,
                u.role AS user_role,
                u.email AS user_email
            FROM `audit_logs` a
            LEFT JOIN `users` u ON u.id = a.user_id
            ORDER BY a.created_at DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
