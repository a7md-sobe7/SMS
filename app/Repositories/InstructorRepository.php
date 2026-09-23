<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\InstructorRepositoryInterface;

class InstructorRepository extends BaseRepository implements InstructorRepositoryInterface
{
    protected string $table = 'instructors';

    public function findByUserId(int $userId): ?array
    {
        return $this->findOneBy(['user_id' => $userId]);
    }

    public function findByEmployeeCode(string $code): ?array
    {
        return $this->findOneBy(['employee_code' => $code]);
    }

    /**
     * Retrieve instructor with department info.
     */
    public function findWithDepartment(int $id): ?array
    {
        $sql = "
            SELECT 
                i.*,
                d.name AS department_name,
                d.code AS department_code
            FROM `instructors` i
            JOIN `departments` d ON d.id = i.department_id
            WHERE i.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res !== false ? $res : null;
    }

    /**
     * Retrieve all instructors with department names.
     */
    public function findAllWithDepartments(): array
    {
        $sql = "
            SELECT 
                i.*,
                d.name AS department_name,
                d.code AS department_code
            FROM `instructors` i
            JOIN `departments` d ON d.id = i.department_id
            ORDER BY i.last_name ASC, i.first_name ASC
        ";

        return $this->db->query($sql)->fetchAll();
    }
}
