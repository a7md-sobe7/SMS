<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

interface GradeRepositoryInterface
{
    public function find(int $id): ?array;
    public function findOneBy(array $criteria): ?array;
    public function findByEnrollmentId(int $enrollmentId): ?array;
    public function upsert(array $gradeData): int;
    public function getStudentGpaSummary(int $studentId): array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function count(array $where = []): int;
}
