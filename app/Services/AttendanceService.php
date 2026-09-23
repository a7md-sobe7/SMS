<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Core\Events\EventDispatcher;
use App\DTOs\Attendance\AttendanceDTO;
use App\Events\AttendanceRecordedEvent;

class AttendanceService
{
    public function __construct(
        private AttendanceRepositoryInterface $attendanceRepo,
        private CourseRepositoryInterface $courseRepo,
        private ?EventDispatcher $dispatcher = null
    ) {
        $this->dispatcher = $dispatcher ?? EventDispatcher::getInstance();
    }

    public function getAttendanceView(?int $courseId = null, ?string $date = null): array
    {
        $courses = $this->courseRepo->findAllWithDetails();
        $date = $date ?: date('Y-m-d');
        $roster = [];
        $selectedCourse = null;

        if ($courseId) {
            $selectedCourse = $this->courseRepo->findWithDetails($courseId);
            $roster = $this->attendanceRepo->getCourseAttendanceByDate($courseId, $date);
        }

        return [
            'courses'        => $courses,
            'selectedCourse' => $selectedCourse,
            'roster'         => $roster,
            'courseId'       => $courseId,
            'date'           => $date
        ];
    }

    public function recordAttendance(AttendanceDTO $dto, ?int $performedBy = null): bool
    {
        $success = $this->attendanceRepo->upsert(
            studentId: $dto->student_id,
            courseId: $dto->course_id,
            date: $dto->attendance_date,
            status: $dto->status,
            notes: $dto->notes
        );

        if ($success) {
            $this->dispatcher->dispatch(new AttendanceRecordedEvent(
                studentId: $dto->student_id,
                courseId: $dto->course_id,
                date: $dto->attendance_date,
                status: $dto->status,
                performedByUserId: $performedBy
            ));
        }

        return $success;
    }

    public function getStudentStats(int $studentId, int $courseId): array
    {
        return $this->attendanceRepo->getStudentCourseAttendanceStats($studentId, $courseId);
    }
}
