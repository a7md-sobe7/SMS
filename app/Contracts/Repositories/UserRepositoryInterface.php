<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

interface UserRepositoryInterface
{
    public function find(int $id): ?array;
    public function findOneBy(array $criteria): ?array;
    public function findByEmail(string $email): ?array;
    public function findByUsername(string $username): ?array;
    public function findByUsernameOrEmail(string $identifier): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function updatePassword(int $userId, string $passwordHash): bool;
    public function delete(int $id): bool;
    public function count(array $where = []): int;
}
