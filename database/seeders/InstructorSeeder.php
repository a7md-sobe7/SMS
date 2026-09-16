<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

class InstructorSeeder
{
    public static function run(PDO $pdo): void
    {
        // Lookup User IDs and Department IDs
        $getUser = $pdo->prepare("SELECT `id` FROM `users` WHERE `email` = ?");
        $getDept = $pdo->prepare("SELECT `id` FROM `departments` WHERE `code` = ?");

        $getUser->execute(['alan.turing@sms.edu']);
        $alanUserId = $getUser->fetchColumn();

        $getUser->execute(['ada.lovelace@sms.edu']);
        $adaUserId = $getUser->fetchColumn();

        $getDept->execute(['CS']);
        $csDeptId = $getDept->fetchColumn();

        $getDept->execute(['MATH']);
        $mathDeptId = $getDept->fetchColumn();

        $instructors = [
            [
                'user_id'       => $alanUserId ?: null,
                'department_id' => $csDeptId,
                'employee_code' => 'INS-2026-001',
                'first_name'    => 'Alan',
                'last_name'     => 'Turing',
                'email'         => 'alan.turing@sms.edu',
                'phone'         => '+1 (555) 019-2831',
            ],
            [
                'user_id'       => $adaUserId ?: null,
                'department_id' => $mathDeptId,
                'employee_code' => 'INS-2026-002',
                'first_name'    => 'Ada',
                'last_name'     => 'Lovelace',
                'email'         => 'ada.lovelace@sms.edu',
                'phone'         => '+1 (555) 019-2832',
            ],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO `instructors` (`user_id`, `department_id`, `employee_code`, `first_name`, `last_name`, `email`, `phone`)
            VALUES (:user_id, :department_id, :employee_code, :first_name, :last_name, :email, :phone)
            ON DUPLICATE KEY UPDATE 
                `department_id` = VALUES(`department_id`),
                `first_name` = VALUES(`first_name`),
                `last_name` = VALUES(`last_name`),
                `phone` = VALUES(`phone`)
        ");

        foreach ($instructors as $inst) {
            $stmt->execute($inst);
        }
    }
}
