<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

class DatabaseSeeder
{
    /**
     * Run all seeders in relational dependency order.
     */
    public static function run(PDO $pdo): void
    {
        echo "[*] Seeding Users...\n";
        UserSeeder::run($pdo);

        echo "[*] Seeding Departments...\n";
        DepartmentSeeder::run($pdo);

        echo "[*] Seeding Instructors...\n";
        InstructorSeeder::run($pdo);

        echo "[*] Seeding Students...\n";
        StudentSeeder::run($pdo);

        echo "[*] Seeding Courses...\n";
        CourseSeeder::run($pdo);

        echo "[*] Seeding Enrollments, Grades & Attendance...\n";
        EnrollmentSeeder::run($pdo);

        echo "[+] Database successfully seeded!\n";
    }
}
