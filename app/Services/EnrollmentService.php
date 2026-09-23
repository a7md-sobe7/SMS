<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Core\Events\EventDispatcher;
use App\DTOs\Enrollment\EnrollmentDTO;
use App\Events\CourseCapacityReachedEvent;
use App\Events\StudentEnrolledEvent;
use App\Exceptions\BusinessException;
use App\Exceptions\CapacityExceededException;
use App\Exceptions\ModelNotFoundException;

class EnrollmentService
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollmentRepo,
        private CourseRepositoryInterface $courseRepo,
        private StudentRepositoryInterface $studentRepo,
        private ?EventDispatcher $dispatcher = null
    ) {
        $this->dispatcher = $dispatcher ?? EventDispatcher::getInstance();
    }

    public function enrollStudent(EnrollmentDTO $dto, ?int $performedBy = null): int
    {
        // 1. Verify student exists
        $student = $this->studentRepo->find($dto->student_id);
        if (!$student) {
            throw new ModelNotFoundException('Student', [$dto->student_id]);
        }

        // 2. Verify course exists
        $course = $this->courseRepo->find($dto->course_id);
        if (!$course) {
            throw new ModelNotFoundException('Course', [$dto->course_id]);
        }

        // 3. Check for existing active enrollment
        $existing = $this->enrollmentRepo->findByStudentAndCourse($dto->student_id, $dto->course_id);
        if ($existing && $existing['status'] === 'enrolled') {
            throw new BusinessException("Student is already actively enrolled in this course.", 'DUPLICATE_ENROLLMENT', 409);
        }

        // 4. Verify seat capacity
        $activeCount = $this->enrollmentRepo->countActiveEnrollments($dto->course_id);
        $maxCapacity = (int)($course['max_capacity'] ?? 40);

        if ($activeCount >= $maxCapacity) {
            $this->dispatcher->dispatch(new CourseCapacityReachedEvent(
                courseId: $dto->course_id,
                courseCode: $course['course_code'],
                capacity: $maxCapacity
            ));
            throw new CapacityExceededException("Course capacity limit of {$maxCapacity} students has been reached.");
        }

        // 5. Create or re-activate enrollment
        if ($existing) {
            $this->enrollmentRepo->update((int)$existing['id'], [
                'status'          => 'enrolled',
                'enrollment_date' => $dto->enrollment_date
            ]);
            $enrollmentId = (int)$existing['id'];
        } else {
            $enrollmentId = $this->enrollmentRepo->create($dto->toArray());
        }

        // 6. Check if capacity reached after this enrollment
        if (($activeCount + 1) >= $maxCapacity) {
            $this->dispatcher->dispatch(new CourseCapacityReachedEvent(
                courseId: $dto->course_id,
                courseCode: $course['course_code'],
                capacity: $maxCapacity
            ));
        }

        // 7. Dispatch StudentEnrolledEvent
        $this->dispatcher->dispatch(new StudentEnrolledEvent(
            enrollmentId: $enrollmentId,
            studentId: $dto->student_id,
            courseId: $dto->course_id,
            performedByUserId: $performedBy
        ));

        return $enrollmentId;
    }

    public function dropEnrollment(int $enrollmentId, ?int $performedBy = null): bool
    {
        $enrollment = $this->enrollmentRepo->find($enrollmentId);
        if (!$enrollment) {
            throw new ModelNotFoundException('Enrollment', [$enrollmentId]);
        }

        return $this->enrollmentRepo->update($enrollmentId, ['status' => 'dropped']);
    }

    public function getStudentEnrollments(int $studentId): array
    {
        return $this->enrollmentRepo->getStudentEnrollments($studentId);
    }

    public function getCourseRoster(int $courseId): array
    {
        return $this->enrollmentRepo->getCourseRoster($courseId);
    }
}
