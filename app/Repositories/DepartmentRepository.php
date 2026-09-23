<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\DepartmentRepositoryInterface;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    protected string $table = 'departments';

    public function findByCode(string $code): ?array
    {
        return $this->findOneBy(['code' => strtoupper($code)]);
    }

    /**
     * Retrieve department with student, course, and instructor count statistics.
     */
    public function getWithStatistics(): array
    {
        $sql = "
            SELECT 
                d.*,
                COUNT(DISTINCT s.id) AS student_count,
                COUNT(DISTINCT s.id) AS total_students,
                COUNT(DISTINCT c.id) AS course_count,
                COUNT(DISTINCT c.id) AS total_courses,
                COUNT(DISTINCT i.id) AS instructor_count,
                COUNT(DISTINCT i.id) AS total_instructors
            FROM `departments` d
            LEFT JOIN `students` s ON s.department_id = d.id AND s.status != 'withdrawn'
            LEFT JOIN `courses` c ON c.department_id = d.id
            LEFT JOIN `instructors` i ON i.department_id = d.id
            GROUP BY d.id
            ORDER BY d.name ASC
        ";

        return $this->getDb()->query($sql)->fetchAll();
    }
}
