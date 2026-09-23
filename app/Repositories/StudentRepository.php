<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\StudentRepositoryInterface;

class StudentRepository extends BaseRepository implements StudentRepositoryInterface
{
    protected string $table = 'students';

    public function findByUserId(int $userId): ?array
    {
        return $this->findOneBy(['user_id' => $userId]);
    }

    public function findByStudentCode(string $code): ?array
    {
        return $this->findOneBy(['student_code' => $code]);
    }

    public function findByCode(string $studentCode): ?array
    {
        return $this->findByStudentCode($studentCode);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Find detailed student profile with department information.
     */
    public function findWithDepartment(int $id): ?array
    {
        $sql = "
            SELECT 
                s.*,
                d.name AS department_name,
                d.code AS department_code
            FROM `students` s
            JOIN `departments` d ON d.id = s.department_id
            WHERE s.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res !== false ? $res : null;
    }

    /**
     * Advanced Search, Filter and Paginate Students
     */
    public function searchAndFilter(
        ?string $search = null,
        ?int $departmentId = null,
        ?string $academicLevel = null,
        ?string $status = null,
        int $page = 1,
        int $perPage = 15,
        string $sortBy = 'created_at',
        string $sortDir = 'DESC'
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $allowedSorts = ['student_code', 'first_name', 'last_name', 'email', 'enrollment_year', 'academic_level', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $allowedSorts, true) ? $sortBy : 'created_at';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $where = [];
        $params = [];

        if (!empty($search)) {
            $where[] = "(s.student_code LIKE :search1 OR s.first_name LIKE :search2 OR s.last_name LIKE :search3 OR s.email LIKE :search4)";
            $params['search1'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
            $params['search4'] = "%{$search}%";
        }

        if ($departmentId !== null && $departmentId > 0) {
            $where[] = "s.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }

        if (!empty($academicLevel)) {
            $where[] = "s.academic_level = :level";
            $params['level'] = $academicLevel;
        }

        if (!empty($status)) {
            $where[] = "s.status = :status";
            $params['status'] = $status;
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Count Total
        $countSql = "SELECT COUNT(*) FROM `students` s {$whereSql}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Fetch Data with Department Name
        $sql = "
            SELECT 
                s.*,
                d.name AS department_name,
                d.code AS department_code
            FROM `students` s
            JOIN `departments` d ON d.id = s.department_id
            {$whereSql}
            ORDER BY s.{$sortBy} {$sortDir}
            LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        return [
            'data'       => $data,
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
}
