<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Core\Request;

class GradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $grade = (array)$this->resource;

        return [
            'id'               => (int)($grade['id'] ?? 0),
            'enrollment_id'    => (int)($grade['enrollment_id'] ?? 0),
            'assignment_grade' => (float)($grade['assignment_grade'] ?? 0),
            'midterm_grade'    => (float)($grade['midterm_grade'] ?? 0),
            'final_grade'      => (float)($grade['final_grade'] ?? 0),
            'total_grade'      => (float)($grade['total_grade'] ?? 0),
            'letter_grade'     => (string)($grade['letter_grade'] ?? 'F'),
            'remarks'          => $grade['remarks'] ?? null,
        ];
    }
}
