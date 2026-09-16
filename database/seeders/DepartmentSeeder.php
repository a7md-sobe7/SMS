<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

class DepartmentSeeder
{
    public static function run(PDO $pdo): void
    {
        $departments = [
            [
                'code'        => 'CS',
                'name'        => 'Computer Science & Software Engineering',
                'description' => 'Department responsible for computing, algorithms, database systems, and software engineering.',
            ],
            [
                'code'        => 'EE',
                'name'        => 'Electrical and Electronics Engineering',
                'description' => 'Focuses on circuit design, embedded systems, microprocessors, and robotics.',
            ],
            [
                'code'        => 'BA',
                'name'        => 'Business Administration & Management',
                'description' => 'Covers finance, marketing, organizational leadership, and international commerce.',
            ],
            [
                'code'        => 'MATH',
                'name'        => 'Mathematics and Applied Statistics',
                'description' => 'Theoretical mathematics, linear algebra, calculus, and computational data modeling.',
            ],
        ];

        $stmt = $pdo->prepare("
            INSERT INTO `departments` (`code`, `name`, `description`)
            VALUES (:code, :name, :description)
            ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`)
        ");

        foreach ($departments as $dept) {
            $stmt->execute($dept);
        }
    }
}
