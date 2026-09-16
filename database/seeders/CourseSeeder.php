<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

class CourseSeeder
{
    public static function run(PDO $pdo): void
    {
        $getDept = $pdo->prepare("SELECT `id` FROM `departments` WHERE `code` = ?");
        $getInst = $pdo->prepare("SELECT `id` FROM `instructors` WHERE `employee_code` = ?");

        $getDept->execute(['CS']);
        $csDeptId = $getDept->fetchColumn();

        $getDept->execute(['MATH']);
        $mathDeptId = $getDept->fetchColumn();

        $getInst->execute(['INS-2026-001']);
        $alanInstId = $getInst->fetchColumn();

        $getInst->execute(['INS-2026-002']);
        $adaInstId = $getInst->fetchColumn();

        $courses = [
            [
                'department_id' => $csDeptId,
                'instructor_id' => $alanInstId ?: null,
                'course_code'   => 'CS101',
                'course_name'   => 'Introduction to Computer Science & Algorithms',
                'description'   => 'Fundamental principles of computation, algorithmic problem solving, and modern OOP paradigms.',
                'credit_hours'  => 4,
                'semester'      => 'Fall',
                'academic_year' => 2026,
                'capacity'      => 45,
            ],
            [
                'department_id' => $csDeptId,
                'instructor_id' => $alanInstId ?: null,
                'course_code'   => 'CS202',
                'course_name'   => 'Relational Database Design & SQL Engineering',
                'description'   => 'Schema normalization, indexing, transaction ACID compliance, and query performance optimization.',
                'credit_hours'  => 3,
                'semester'      => 'Fall',
                'academic_year' => 2026,
                'capacity'      => 35,
            ],
            [
                'department_id' => $mathDeptId,
                'instructor_id' => $adaInstId ?: null,
                'course_code'   => 'MATH301',
                'course_name'   => 'Discrete Mathematics & Computational Logic',
                'description'   => 'Set theory, propositional logic, graph theory, combinatorics, and proof structures.',
                'credit_hours'  => 3,
                'semester'      => 'Fall',
                'academic_year' => 2026,
                'capacity'      => 30,
            ],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO `courses` (
                `department_id`, `instructor_id`, `course_code`, `course_name`,
                `description`, `credit_hours`, `semester`, `academic_year`, `capacity`
            )
            VALUES (
                :department_id, :instructor_id, :course_code, :course_name,
                :description, :credit_hours, :semester, :academic_year, :capacity
            )
            ON DUPLICATE KEY UPDATE 
                `instructor_id` = VALUES(`instructor_id`),
                `course_name` = VALUES(`course_name`),
                `capacity` = VALUES(`capacity`),
                `credit_hours` = VALUES(`credit_hours`)
        ");

        foreach ($courses as $course) {
            $stmt->execute($course);
        }
    }
}
