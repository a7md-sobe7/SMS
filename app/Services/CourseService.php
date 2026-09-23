<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Core\Events\EventDispatcher;
use App\DTOs\Course\CourseDTO;
use App\Exceptions\ModelNotFoundException;

class CourseService
{
    public function __construct(
        private CourseRepositoryInterface $courseRepo,
        private EnrollmentRepositoryInterface $enrollmentRepo,
        private ?EventDispatcher $dispatcher = null
    ) {
        $this->dispatcher = $dispatcher ?? EventDispatcher::getInstance();
    }

    public function listCourses(?int $departmentId = null, ?int $instructorId = null): array
    {
        return $this->courseRepo->findAllWithDetails($departmentId, $instructorId);
    }

    public function getCourseDetails(int $id): array
    {
        $course = $this->courseRepo->findWithDetails($id);
        if (!$course) {
            throw new ModelNotFoundException('Course', [$id]);
        }

        $roster = $this->enrollmentRepo->getCourseRoster($id);

        return [
            'course' => $course,
            'roster' => $roster
        ];
    }

    public function getById(int $id): array
    {
        $course = $this->courseRepo->find($id);
        if (!$course) {
            throw new ModelNotFoundException('Course', [$id]);
        }
        return $course;
    }

    public function createCourse(CourseDTO $dto, ?int $performedBy = null): int
    {
        $data = $dto->toFilteredArray();
        return $this->courseRepo->create($data);
    }

    public function updateCourse(int $id, CourseDTO $dto, ?int $performedBy = null): bool
    {
        $this->getById($id);
        $data = $dto->toFilteredArray();
        return $this->courseRepo->update($id, $data);
    }

    public function deleteCourse(int $id): bool
    {
        $this->getById($id);
        return $this->courseRepo->delete($id);
    }
}
