<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

interface AttendanceRepositoryInterface
{
    public function find(int $id): ?array;
    public function findOneBy(array $criteria): ?array;
    public function findRecord(int $studentId, int $courseId, string $date): ?array;
    public function upsert(int $studentId, int $courseId, string $date, string $status, ?string $notes = null): bool;
    public function getCourseAttendanceByDate(int $courseId, string $date): array;
    public function getStudentCourseAttendanceStats(int $studentId, int $courseId): array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function count(array $where = []): int;
}
