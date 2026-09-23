<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Core\Events\EventDispatcher;
use App\DTOs\Student\StudentDTO;
use App\Events\StudentArchivedEvent;
use App\Events\StudentRegisteredEvent;
use App\Events\StudentUpdatedEvent;
use App\Exceptions\ModelNotFoundException;

class StudentService
{
    public function __construct(
        private StudentRepositoryInterface $studentRepo,
        private EnrollmentRepositoryInterface $enrollmentRepo,
        private GradeRepositoryInterface $gradeRepo,
        private ?EventDispatcher $dispatcher = null
    ) {
        $this->dispatcher = $dispatcher ?? EventDispatcher::getInstance();
    }

    public function searchAndFilter(
        ?string $search = null,
        ?int $departmentId = null,
        ?string $academicLevel = null,
        ?string $status = null,
        int $page = 1,
        int $perPage = 10
    ): array {
        return $this->studentRepo->searchAndFilter(
            $search,
            $departmentId,
            $academicLevel,
            $status,
            $page,
            $perPage
        );
    }

    public function getProfileWithTranscript(int $id): array
    {
        $student = $this->studentRepo->findWithDepartment($id);
        if (!$student) {
            throw new ModelNotFoundException('Student', [$id], "Student record with ID [{$id}] does not exist.");
        }

        $enrollments = $this->enrollmentRepo->getStudentEnrollments($id);
        $gpaSummary = $this->gradeRepo->getStudentGpaSummary($id);

        return [
            'student'     => $student,
            'enrollments' => $enrollments,
            'gpa'         => $gpaSummary
        ];
    }

    public function getById(int $id): array
    {
        $student = $this->studentRepo->find($id);
        if (!$student) {
            throw new ModelNotFoundException('Student', [$id]);
        }
        return $student;
    }

    public function createStudent(StudentDTO $dto, ?int $performedBy = null): int
    {
        $data = $dto->toFilteredArray();

        $studentId = $this->studentRepo->create($data);

        $this->dispatcher->dispatch(new StudentRegisteredEvent(
            studentId: $studentId,
            studentCode: $dto->student_code,
            name: "{$dto->first_name} {$dto->last_name}",
            email: $dto->email,
            performedByUserId: $performedBy
        ));

        return $studentId;
    }

    public function updateStudent(int $id, StudentDTO $dto, ?int $performedBy = null): bool
    {
        $this->getById($id);

        $data = $dto->toFilteredArray();
        $updated = $this->studentRepo->update($id, $data);

        if ($updated) {
            $this->dispatcher->dispatch(new StudentUpdatedEvent(
                studentId: $id,
                studentCode: $dto->student_code,
                changes: $data,
                performedByUserId: $performedBy
            ));
        }

        return $updated;
    }

    public function archiveStudent(int $id, ?int $performedBy = null): bool
    {
        $student = $this->getById($id);

        $updated = $this->studentRepo->update($id, ['status' => 'withdrawn']);

        if ($updated) {
            $this->dispatcher->dispatch(new StudentArchivedEvent(
                studentId: $id,
                studentCode: $student['student_code'],
                performedByUserId: $performedBy
            ));
        }

        return $updated;
    }
}
