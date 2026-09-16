<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

class UserSeeder
{
    public static function run(PDO $pdo): void
    {
        $password = password_hash('Admin@123456', PASSWORD_BCRYPT, ['cost' => 12]);

        $users = [
            [
                'username'      => 'admin',
                'email'         => 'admin@sms.edu',
                'password_hash' => $password,
                'role'          => 'admin',
                'is_active'     => 1,
            ],
            [
                'username'      => 'registrar',
                'email'         => 'registrar@sms.edu',
                'password_hash' => $password,
                'role'          => 'registrar',
                'is_active'     => 1,
            ],
            [
                'username'      => 'dr.alan',
                'email'         => 'alan.turing@sms.edu',
                'password_hash' => $password,
                'role'          => 'instructor',
                'is_active'     => 1,
            ],
            [
                'username'      => 'dr.ada',
                'email'         => 'ada.lovelace@sms.edu',
                'password_hash' => $password,
                'role'          => 'instructor',
                'is_active'     => 1,
            ],
            [
                'username'      => 'john.doe',
                'email'         => 'john.doe@student.sms.edu',
                'password_hash' => $password,
                'role'          => 'student',
                'is_active'     => 1,
            ],
            [
                'username'      => 'jane.smith',
                'email'         => 'jane.smith@student.sms.edu',
                'password_hash' => $password,
                'role'          => 'student',
                'is_active'     => 1,
            ],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO `users` (`username`, `email`, `password_hash`, `role`, `is_active`)
            VALUES (:username, :email, :password_hash, :role, :is_active)
            ON DUPLICATE KEY UPDATE `password_hash` = VALUES(`password_hash`), `is_active` = VALUES(`is_active`)
        ");

        foreach ($users as $user) {
            $stmt->execute($user);
        }
    }
}
