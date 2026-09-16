<?php

declare(strict_types=1);

namespace App\Repositories;

class GradeRepository extends BaseRepository
{
    protected string $table = 'grades';

    public function findByEnrollmentId(int $enrollmentId): ?array
    {
        return $this->findOneBy(['enrollment_id' => $enrollmentId]);
    }

    /**
     * Upsert Grade (Insert if not exists, Update if already exists)
     */
    public function upsert(array $gradeData): int
    {
        $sql = "
            INSERT INTO `grades` (
                `enrollment_id`, `assignment_grade`, `midterm_grade`,
                `final_grade`, `total_grade`, `letter_grade`, `remarks`
            )
            VALUES (
                :enrollment_id, :assignment_grade, :midterm_grade,
                :final_grade, :total_grade, :letter_grade, :remarks
            )
            ON DUPLICATE KEY UPDATE 
                `assignment_grade` = VALUES(`assignment_grade`),
                `midterm_grade` = VALUES(`midterm_grade`),
                `final_grade` = VALUES(`final_grade`),
                `total_grade` = VALUES(`total_grade`),
                `letter_grade` = VALUES(`letter_grade`),
                `remarks` = VALUES(`remarks`),
                `updated_at` = CURRENT_TIMESTAMP
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($gradeData);

        return (int)($this->db->lastInsertId() ?: $this->findByEnrollmentId((int)$gradeData['enrollment_id'])['id']);
    }

    /**
     * Calculate GPA summary for a specific student across all completed courses.
     */
    public function getStudentGpaSummary(int $studentId): array
    {
        $sql = "
            SELECT 
                COUNT(g.id) AS total_graded_courses,
                COALESCE(AVG(g.total_grade), 0.0) AS average_numerical_grade,
                COALESCE(SUM(c.credit_hours), 0) AS total_credits_earned
            FROM `grades` g
            JOIN `enrollments` e ON e.id = g.enrollment_id
            JOIN `courses` c ON c.id = e.course_id
            WHERE e.student_id = :student_id AND e.status = 'completed'
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetch() ?: ['total_graded_courses' => 0, 'average_numerical_grade' => 0, 'total_credits_earned' => 0];
    }
}
