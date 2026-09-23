<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\EnrollmentRepositoryInterface;

class EnrollmentRepository extends BaseRepository implements EnrollmentRepositoryInterface
{
    protected string $table = 'enrollments';

    public function findByStudentAndCourse(int $studentId, int $courseId): ?array
    {
        return $this->findOneBy([
            'student_id' => $studentId,
            'course_id'  => $courseId
        ]);
    }

    /**
     * Retrieve all enrollments for a specific student with course & grade info.
     */
    public function getStudentEnrollments(int $studentId): array
    {
        $sql = "
            SELECT 
                e.*,
                c.course_code,
                c.course_name,
                c.credit_hours,
                c.semester,
                c.academic_year,
                CONCAT(i.first_name, ' ', i.last_name) AS instructor_name,
                g.assignment_grade,
                g.midterm_grade,
                g.final_grade,
                g.total_grade,
                g.letter_grade,
                g.remarks
            FROM `enrollments` e
            JOIN `courses` c ON c.id = e.course_id
            LEFT JOIN `instructors` i ON i.id = c.instructor_id
            LEFT JOIN `grades` g ON g.enrollment_id = e.id
            WHERE e.student_id = :student_id
            ORDER BY c.academic_year DESC, c.semester DESC, c.course_code ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    /**
     * Retrieve all students enrolled in a specific course.
     */
    public function getCourseRoster(int $courseId): array
    {
        $sql = "
            SELECT 
                e.id AS enrollment_id,
                e.enrollment_date,
                e.status AS enrollment_status,
                s.id AS student_id,
                s.student_code,
                s.first_name,
                s.last_name,
                s.email,
                s.academic_level,
                g.assignment_grade,
                g.midterm_grade,
                g.final_grade,
                g.total_grade,
                g.letter_grade
            FROM `enrollments` e
            JOIN `students` s ON s.id = e.student_id
            LEFT JOIN `grades` g ON g.enrollment_id = e.id
            WHERE e.course_id = :course_id
            ORDER BY s.last_name ASC, s.first_name ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['course_id' => $courseId]);
        return $stmt->fetchAll();
    }

    /**
     * Count active enrolled students for a course.
     */
    public function countActiveEnrollments(int $courseId): int
    {
        $sql = "SELECT COUNT(*) FROM `enrollments` WHERE `course_id` = :course_id AND `status` = 'enrolled'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['course_id' => $courseId]);
        return (int)$stmt->fetchColumn();
    }
}
