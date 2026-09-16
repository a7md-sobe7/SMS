<?php

declare(strict_types=1);

namespace App\Repositories;

class DepartmentRepository extends BaseRepository
{
    protected string $table = 'departments';

    public function findByCode(string $code): ?array
    {
        return $this->findOneBy(['code' => strtoupper($code)]);
    }

    /**
     * Retrieve department with student and course count statistics.
     */
    public function getWithStatistics(): array
    {
        $sql = "
            SELECT 
                d.*,
                COUNT(DISTINCT s.id) AS total_students,
                COUNT(DISTINCT c.id) AS total_courses,
                COUNT(DISTINCT i.id) AS total_instructors
            FROM `departments` d
            LEFT JOIN `students` s ON s.department_id = d.id
            LEFT JOIN `courses` c ON c.department_id = d.id
            LEFT JOIN `instructors` i ON i.department_id = d.id
            GROUP BY d.id
            ORDER BY d.name ASC
        ";

        return $this->db->query($sql)->fetchAll();
    }
}
