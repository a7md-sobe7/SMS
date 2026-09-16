<?php

declare(strict_types=1);

namespace App\Repositories;

class AttendanceRepository extends BaseRepository
{
    protected string $table = 'attendance';

    public function findRecord(int $studentId, int $courseId, string $date): ?array
    {
        return $this->findOneBy([
            'student_id'      => $studentId,
            'course_id'       => $courseId,
            'attendance_date' => $date
        ]);
    }

    /**
     * Upsert single attendance entry
     */
    public function upsert(int $studentId, int $courseId, string $date, string $status, ?string $notes = null): bool
    {
        $sql = "
            INSERT INTO `attendance` (`student_id`, `course_id`, `attendance_date`, `status`, `notes`)
            VALUES (:student_id, :course_id, :attendance_date, :status, :notes)
            ON DUPLICATE KEY UPDATE 
                `status` = VALUES(`status`),
                `notes` = VALUES(`notes`),
                `updated_at` = CURRENT_TIMESTAMP
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'student_id'      => $studentId,
            'course_id'       => $courseId,
            'attendance_date' => $date,
            'status'          => $status,
            'notes'           => $notes
        ]);
    }

    /**
     * Retrieve course attendance roster for a specific date.
     */
    public function getCourseAttendanceByDate(int $courseId, string $date): array
    {
        $sql = "
            SELECT 
                s.id AS student_id,
                s.student_code,
                s.first_name,
                s.last_name,
                COALESCE(a.status, 'present') AS status,
                a.notes,
                a.id AS attendance_id
            FROM `enrollments` e
            JOIN `students` s ON s.id = e.student_id
            LEFT JOIN `attendance` a ON a.student_id = s.id AND a.course_id = :join_course_id AND a.attendance_date = :attendance_date
            WHERE e.course_id = :where_course_id AND e.status = 'enrolled'
            ORDER BY s.last_name ASC, s.first_name ASC
        ";

        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([
            'join_course_id'  => $courseId,
            'where_course_id' => $courseId,
            'attendance_date' => $date
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Calculate student attendance statistics for a specific course.
     */
    public function getStudentCourseAttendanceStats(int $studentId, int $courseId): array
    {
        $sql = "
            SELECT 
                COUNT(*) AS total_sessions,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS total_present,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS total_late,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS total_absent,
                SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) AS total_excused
            FROM `attendance`
            WHERE student_id = :student_id AND course_id = :course_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'student_id' => $studentId,
            'course_id'  => $courseId
        ]);
        return $stmt->fetch() ?: ['total_sessions' => 0, 'total_present' => 0, 'total_late' => 0, 'total_absent' => 0, 'total_excused' => 0];
    }
}
