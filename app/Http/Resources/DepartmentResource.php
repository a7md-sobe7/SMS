<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Core\Request;

class DepartmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $dept = (array)$this->resource;

        $studentCount = (int)($dept['student_count'] ?? ($dept['total_students'] ?? 0));
        $courseCount = (int)($dept['course_count'] ?? ($dept['total_courses'] ?? 0));
        $instructorCount = (int)($dept['instructor_count'] ?? ($dept['total_instructors'] ?? 0));

        return [
            'id'                => (int)($dept['id'] ?? 0),
            'name'              => (string)($dept['name'] ?? ''),
            'code'              => (string)($dept['code'] ?? ''),
            'description'       => $dept['description'] ?? null,
            'student_count'     => $studentCount,
            'total_students'    => $studentCount,
            'course_count'      => $courseCount,
            'total_courses'     => $courseCount,
            'instructor_count'  => $instructorCount,
            'total_instructors' => $instructorCount,
            'faculty_count'     => $instructorCount,
        ];
    }
}
