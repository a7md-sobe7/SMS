<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\DTOs\Department\DepartmentDTO;
use App\Exceptions\ModelNotFoundException;

class DepartmentService
{
    public function __construct(private DepartmentRepositoryInterface $deptRepo) {}

    public function getAll(): array
    {
        return $this->deptRepo->findAll('name', 'ASC');
    }

    public function getWithStatistics(): array
    {
        return $this->deptRepo->getWithStatistics();
    }

    public function getById(int $id): array
    {
        $dept = $this->deptRepo->find($id);
        if (!$dept) {
            throw new ModelNotFoundException('Department', [$id]);
        }
        return $dept;
    }

    public function create(DepartmentDTO $dto): int
    {
        return $this->deptRepo->create($dto->toFilteredArray());
    }

    public function update(int $id, DepartmentDTO $dto): bool
    {
        $this->getById($id);
        return $this->deptRepo->update($id, $dto->toFilteredArray());
    }
}
