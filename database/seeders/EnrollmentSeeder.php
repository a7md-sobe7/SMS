<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

class EnrollmentSeeder
{
    public static function run(PDO $pdo): void
    {
        $getStu = $pdo->prepare("SELECT `id` FROM `students` WHERE `student_code` = ?");
        $getCourse = $pdo->prepare("SELECT `id` FROM `courses` WHERE `course_code` = ?");

        $getStu->execute(['STU-2026-001']);
        $johnId = $getStu->fetchColumn();

        $getStu->execute(['STU-2026-002']);
        $janeId = $getStu->fetchColumn();

        $getCourse->execute(['CS101']);
        $cs101Id = $getCourse->fetchColumn();

        $getCourse->execute(['CS202']);
        $cs202Id = $getCourse->fetchColumn();

        if (!$johnId || !$cs101Id) {
            return;
        }

        // 1. Enroll John in CS101 and CS202
        $enrollStmt = $pdo->prepare("
            INSERT INTO `enrollments` (`student_id`, `course_id`, `enrollment_date`, `status`)
            VALUES (:student_id, :course_id, :enrollment_date, :status)
            ON DUPLICATE KEY UPDATE `status` = VALUES(`status`)
        ");

        $enrollStmt->execute([
            'student_id'      => $johnId,
            'course_id'       => $cs101Id,
            'enrollment_date' => '2026-09-01',
            'status'          => 'enrolled',
        ]);

        $johnCs101EnrollmentId = $pdo->lastInsertId() ?: null;
        if (!$johnCs101EnrollmentId) {
            $getEnroll = $pdo->prepare("SELECT `id` FROM `enrollments` WHERE `student_id` = ? AND `course_id` = ?");
            $getEnroll->execute([$johnId, $cs101Id]);
            $johnCs101EnrollmentId = $getEnroll->fetchColumn();
        }

        // 2. Insert Grade Record for John in CS101
        if ($johnCs101EnrollmentId) {
            $gradeStmt = $pdo->prepare("
                INSERT INTO `grades` (
                    `enrollment_id`, `assignment_grade`, `midterm_grade`, `final_grade`,
                    `total_grade`, `letter_grade`, `remarks`
                )
                VALUES (
                    :enrollment_id, :assignment, :midterm, :final,
                    :total, :letter, :remarks
                )
                ON DUPLICATE KEY UPDATE 
                    `assignment_grade` = VALUES(`assignment_grade`),
                    `midterm_grade` = VALUES(`midterm_grade`),
                    `final_grade` = VALUES(`final_grade`),
                    `total_grade` = VALUES(`total_grade`),
                    `letter_grade` = VALUES(`letter_grade`)
            ");

            // Weights: Assignment 20%, Midterm 30%, Final 50%
            // 95*0.2 (19) + 88*0.3 (26.4) + 92*0.5 (46) = 91.4 -> 'A'
            $gradeStmt->execute([
                'enrollment_id' => $johnCs101EnrollmentId,
                'assignment'    => 95.00,
                'midterm'       => 88.00,
                'final'         => 92.00,
                'total'         => 91.40,
                'letter'        => 'A',
                'remarks'       => 'Outstanding academic performance and project engagement.',
            ]);
        }

        // 3. Insert Attendance Record
        $attStmt = $pdo->prepare("
            INSERT INTO `attendance` (`student_id`, `course_id`, `attendance_date`, `status`, `notes`)
            VALUES (:student_id, :course_id, :date, :status, :notes)
            ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `notes` = VALUES(`notes`)
        ");

        $attStmt->execute([
            'student_id' => $johnId,
            'course_id'  => $cs101Id,
            'date'       => '2026-09-10',
            'status'     => 'present',
            'notes'      => 'Attended lecture and lab session.',
        ]);
    }
}
