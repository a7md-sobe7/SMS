<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

/**
 * Abstract Base Repository with Lazy Connection Loading
 * 
 * Provides generic, secure CRUD and pagination methods using PDO prepared statements.
 * Defers database connection initialization until the exact moment a query is executed.
 */
abstract class BaseRepository
{
    private ?PDO $pdo = null;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * Retrieve the active PDO instance on demand (Lazy Loading).
     */
    protected function getDb(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = Database::getConnection();
        }
        return $this->pdo;
    }

    /**
     * Magic getter to allow $this->db property access with lazy connection instantiation.
     */
    public function __get(string $name): mixed
    {
        if ($name === 'db') {
            return $this->getDb();
        }
        return null;
    }

    /**
     * Find a single record by primary key.
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * Find a single record matching specific criteria.
     */
    public function findOneBy(array $criteria): ?array
    {
        $whereClauses = [];
        $params = [];

        foreach ($criteria as $column => $value) {
            $whereClauses[] = "`{$column}` = :{$column}";
            $params[$column] = $value;
        }

        $whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
        $sql = "SELECT * FROM `{$this->table}` {$whereSql} LIMIT 1";

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return $result !== false ? $result : null;
    }

    /**
     * Retrieve all records from the table.
     */
    public function findAll(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$direction}";
        $stmt = $this->getDb()->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Paginate results with total count and metadata.
     */
    public function paginate(int $page = 1, int $perPage = 15, array $where = [], string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $whereClauses = [];
        $params = [];

        foreach ($where as $column => $value) {
            $whereClauses[] = "`{$column}` = :{$column}";
            $params[$column] = $value;
        }

        $whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

        // Count total matching records
        $countSql = "SELECT COUNT(*) FROM `{$this->table}` {$whereSql}";
        $countStmt = $this->getDb()->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Fetch paginated chunk
        $sql = "SELECT * FROM `{$this->table}` {$whereSql} ORDER BY `{$orderBy}` {$direction} LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'total_pages'  => (int)ceil($total / $perPage),
                'has_prev'     => $page > 1,
                'has_next'     => $page < ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Insert a new record into the database table.
     */
    public function create(array $data): int
    {
        $columns = array_keys($data);
        $escapedColumns = array_map(fn($col) => "`{$col}`", $columns);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $this->table,
            implode(', ', $escapedColumns),
            implode(', ', $placeholders)
        );

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($data);

        return (int)$this->getDb()->lastInsertId();
    }

    /**
     * Update an existing record by primary key.
     */
    public function update(int $id, array $data): bool
    {
        $setClauses = [];
        $params = ['id' => $id];

        foreach ($data as $column => $value) {
            $setClauses[] = "`{$column}` = :{$column}";
            $params[$column] = $value;
        }

        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = :id",
            $this->table,
            implode(', ', $setClauses),
            $this->primaryKey
        );

        $stmt = $this->getDb()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a record by primary key.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->getDb()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Count total records matching criteria.
     */
    public function count(array $where = []): int
    {
        $whereClauses = [];
        $params = [];

        foreach ($where as $column => $value) {
            $whereClauses[] = "`{$column}` = :{$column}";
            $params[$column] = $value;
        }

        $whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
        $sql = "SELECT COUNT(*) FROM `{$this->table}` {$whereSql}";

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
