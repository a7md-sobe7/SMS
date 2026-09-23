<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Core\Request;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $enrollment = (array)$this->resource;

        return [
            'id'              => (int)($enrollment['id'] ?? ($enrollment['enrollment_id'] ?? 0)),
            'student_id'      => (int)($enrollment['student_id'] ?? 0),
            'student_code'    => $enrollment['student_code'] ?? null,
            'student_name'    => isset($enrollment['first_name']) ? trim($enrollment['first_name'] . ' ' . ($enrollment['last_name'] ?? '')) : null,
            'course_id'       => (int)($enrollment['course_id'] ?? 0),
            'course_code'     => $enrollment['course_code'] ?? null,
            'course_name'     => $enrollment['course_name'] ?? null,
            'enrollment_date' => (string)($enrollment['enrollment_date'] ?? ''),
            'status'          => (string)($enrollment['status'] ?? ($enrollment['enrollment_status'] ?? 'enrolled')),
            'grade'           => isset($enrollment['letter_grade']) ? [
                'assignment' => $enrollment['assignment_grade'] ?? null,
                'midterm'    => $enrollment['midterm_grade'] ?? null,
                'final'      => $enrollment['final_grade'] ?? null,
                'total'      => $enrollment['total_grade'] ?? null,
                'letter'     => $enrollment['letter_grade'] ?? null,
            ] : null
        ];
    }
}
