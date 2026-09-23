<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Core\Events\EventDispatcher;
use App\DTOs\Grade\GradeDTO;
use App\Events\GradeAssignedEvent;

class GradeService
{
    public function __construct(
        private GradeRepositoryInterface $gradeRepo,
        private EnrollmentRepositoryInterface $enrollmentRepo,
        private CourseRepositoryInterface $courseRepo,
        private ?EventDispatcher $dispatcher = null
    ) {
        $this->dispatcher = $dispatcher ?? EventDispatcher::getInstance();
    }

    public function getGradeOverview(?int $courseId = null): array
    {
        $courses = $this->courseRepo->findAllWithDetails();
        $roster = [];
        $selectedCourse = null;

        if ($courseId) {
            $selectedCourse = $this->courseRepo->findWithDetails($courseId);
            $roster = $this->enrollmentRepo->getCourseRoster($courseId);
        }

        return [
            'courses'        => $courses,
            'selectedCourse' => $selectedCourse,
            'roster'         => $roster,
            'courseId'       => $courseId
        ];
    }

    public function evaluateAndUpsertGrade(GradeDTO $dto, ?int $performedBy = null): array
    {
        $assignment = $dto->assignment_grade;
        $midterm    = $dto->midterm_grade;
        $final      = $dto->final_grade;

        // Default weights: 30% assignment, 30% midterm, 40% final
        $weights = ['assignment' => 0.30, 'midterm' => 0.30, 'final' => 0.40];

        $configFile = dirname(__DIR__, 2) . '/config/grading.php';
        $scale = [
            ['min' => 90, 'max' => 100, 'letter' => 'A', 'remark' => 'Excellent'],
            ['min' => 85, 'max' => 89.99, 'letter' => 'A-', 'remark' => 'Very Good'],
            ['min' => 80, 'max' => 84.99, 'letter' => 'B+', 'remark' => 'Good'],
            ['min' => 75, 'max' => 79.99, 'letter' => 'B', 'remark' => 'Satisfactory'],
            ['min' => 70, 'max' => 74.99, 'letter' => 'B-', 'remark' => 'Adequate'],
            ['min' => 65, 'max' => 69.99, 'letter' => 'C+', 'remark' => 'Fair'],
            ['min' => 60, 'max' => 64.99, 'letter' => 'C', 'remark' => 'Pass'],
            ['min' => 0, 'max' => 59.99, 'letter' => 'F', 'remark' => 'Fail'],
        ];

        if (file_exists($configFile)) {
            $config = require $configFile;
            if (isset($config['weights'])) {
                $weights = $config['weights'];
            }
            if (isset($config['scale'])) {
                $scale = $config['scale'];
            }
        }

        $totalGrade = round(
            ($assignment * $weights['assignment']) +
            ($midterm * $weights['midterm']) +
            ($final * $weights['final']),
            2
        );

        $letterGrade = 'F';
        $remarks = 'Fail';

        foreach ($scale as $tier) {
            if ($totalGrade >= $tier['min'] && $totalGrade <= $tier['max']) {
                $letterGrade = $tier['letter'];
                $remarks = $tier['remark'];
                break;
            }
        }

        $gradeId = $this->gradeRepo->upsert([
            'enrollment_id'    => $dto->enrollment_id,
            'assignment_grade' => $assignment,
            'midterm_grade'    => $midterm,
            'final_grade'      => $final,
            'total_grade'      => $totalGrade,
            'letter_grade'     => $letterGrade,
            'remarks'          => $remarks
        ]);

        $this->dispatcher->dispatch(new GradeAssignedEvent(
            gradeId: $gradeId,
            enrollmentId: $dto->enrollment_id,
            totalGrade: $totalGrade,
            letterGrade: $letterGrade,
            performedByUserId: $performedBy
        ));

        return [
            'id'               => $gradeId,
            'enrollment_id'    => $dto->enrollment_id,
            'assignment_grade' => $assignment,
            'midterm_grade'    => $midterm,
            'final_grade'      => $final,
            'total_grade'      => $totalGrade,
            'letter_grade'     => $letterGrade,
            'remarks'          => $remarks
        ];
    }
}
