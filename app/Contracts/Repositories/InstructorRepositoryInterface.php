<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

interface InstructorRepositoryInterface
{
    public function find(int $id): ?array;
    public function findOneBy(array $criteria): ?array;
    public function findAll(string $orderBy = 'id', string $direction = 'ASC'): array;
    public function paginate(int $page = 1, int $perPage = 15, array $where = [], string $orderBy = 'id', string $direction = 'DESC'): array;
    public function findWithDepartment(int $id): ?array;
    public function findAllWithDepartments(): array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function count(array $where = []): int;
}
