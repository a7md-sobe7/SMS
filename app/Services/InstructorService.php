<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\InstructorRepositoryInterface;
use App\DTOs\Instructor\InstructorDTO;
use App\Exceptions\ModelNotFoundException;

class InstructorService
{
    public function __construct(private InstructorRepositoryInterface $instructorRepo) {}

    public function getAllWithDepartments(): array
    {
        return $this->instructorRepo->findAllWithDepartments();
    }

    public function getById(int $id): array
    {
        $instructor = $this->instructorRepo->findWithDepartment($id);
        if (!$instructor) {
            throw new ModelNotFoundException('Instructor', [$id]);
        }
        return $instructor;
    }

    public function create(InstructorDTO $dto): int
    {
        return $this->instructorRepo->create($dto->toFilteredArray());
    }

    public function update(int $id, InstructorDTO $dto): bool
    {
        $this->getById($id);
        return $this->instructorRepo->update($id, $dto->toFilteredArray());
    }
}
