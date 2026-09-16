<?php

declare(strict_types=1);

namespace App\Repositories;

class CourseRepository extends BaseRepository
{
    protected string $table = 'courses';

    public function findByCode(string $code): ?array
    {
        return $this->findOneBy(['course_code' => $code]);
    }

    /**
     * Retrieve course with department and instructor details.
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "
            SELECT 
                c.*,
                d.name AS department_name,
                d.code AS department_code,
                CONCAT(i.first_name, ' ', i.last_name) AS instructor_name,
                i.email AS instructor_email,
                (SELECT COUNT(*) FROM `enrollments` e WHERE e.course_id = c.id AND e.status = 'enrolled') AS enrolled_count
            FROM `courses` c
            JOIN `departments` d ON d.id = c.department_id
            LEFT JOIN `instructors` i ON i.id = c.instructor_id
            WHERE c.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res !== false ? $res : null;
    }

    /**
     * Retrieve all courses with department and instructor summaries.
     */
    public function findAllWithDetails(?int $departmentId = null, ?int $instructorId = null): array
    {
        $where = [];
        $params = [];

        if ($departmentId !== null && $departmentId > 0) {
            $where[] = "c.department_id = :dept_id";
            $params['dept_id'] = $departmentId;
        }

        if ($instructorId !== null && $instructorId > 0) {
            $where[] = "c.instructor_id = :inst_id";
            $params['inst_id'] = $instructorId;
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT 
                c.*,
                d.name AS department_name,
                d.code AS department_code,
                CONCAT(i.first_name, ' ', i.last_name) AS instructor_name,
                (SELECT COUNT(*) FROM `enrollments` e WHERE e.course_id = c.id AND e.status = 'enrolled') AS enrolled_count
            FROM `courses` c
            JOIN `departments` d ON d.id = c.department_id
            LEFT JOIN `instructors` i ON i.id = c.instructor_id
            {$whereSql}
            ORDER BY c.course_code ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
