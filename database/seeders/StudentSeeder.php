<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

class StudentSeeder
{
    public static function run(PDO $pdo): void
    {
        $getUser = $pdo->prepare("SELECT `id` FROM `users` WHERE `email` = ?");
        $getDept = $pdo->prepare("SELECT `id` FROM `departments` WHERE `code` = ?");

        $getUser->execute(['john.doe@student.sms.edu']);
        $johnUserId = $getUser->fetchColumn();

        $getUser->execute(['jane.smith@student.sms.edu']);
        $janeUserId = $getUser->fetchColumn();

        $getDept->execute(['CS']);
        $csDeptId = $getDept->fetchColumn();

        $getDept->execute(['BA']);
        $baDeptId = $getDept->fetchColumn();

        $students = [
            [
                'user_id'         => $johnUserId ?: null,
                'department_id'   => $csDeptId,
                'student_code'    => 'STU-2026-001',
                'first_name'      => 'John',
                'last_name'       => 'Doe',
                'email'           => 'john.doe@student.sms.edu',
                'phone'           => '+1 (555) 301-4455',
                'date_of_birth'   => '2004-05-14',
                'gender'          => 'male',
                'address'         => '124 Science Hall Road, Cambridge, MA',
                'enrollment_year' => 2024,
                'academic_level'  => 'sophomore',
                'status'          => 'active',
                'profile_image'   => null,
            ],
            [
                'user_id'         => $janeUserId ?: null,
                'department_id'   => $baDeptId,
                'student_code'    => 'STU-2026-002',
                'first_name'      => 'Jane',
                'last_name'       => 'Smith',
                'email'           => 'jane.smith@student.sms.edu',
                'phone'           => '+1 (555) 301-8899',
                'date_of_birth'   => '2003-11-22',
                'gender'          => 'female',
                'address'         => '78 Innovation Way, Boston, MA',
                'enrollment_year' => 2023,
                'academic_level'  => 'junior',
                'status'          => 'active',
                'profile_image'   => null,
            ],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO `students` (
                `user_id`, `department_id`, `student_code`, `first_name`, `last_name`,
                `email`, `phone`, `date_of_birth`, `gender`, `address`,
                `enrollment_year`, `academic_level`, `status`, `profile_image`
            )
            VALUES (
                :user_id, :department_id, :student_code, :first_name, :last_name,
                :email, :phone, :date_of_birth, :gender, :address,
                :enrollment_year, :academic_level, :status, :profile_image
            )
            ON DUPLICATE KEY UPDATE 
                `department_id` = VALUES(`department_id`),
                `first_name` = VALUES(`first_name`),
                `last_name` = VALUES(`last_name`),
                `phone` = VALUES(`phone`),
                `academic_level` = VALUES(`academic_level`),
                `status` = VALUES(`status`)
        ");

        foreach ($students as $student) {
            $stmt->execute($student);
        }
    }
}
