<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Core\Request;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $course = (array)$this->resource;

        return [
            'id'              => (int)($course['id'] ?? 0),
            'course_code'     => (string)($course['course_code'] ?? ''),
            'course_name'     => (string)($course['course_name'] ?? ''),
            'department_id'   => (int)($course['department_id'] ?? 0),
            'department_name' => $course['department_name'] ?? null,
            'department_code' => $course['department_code'] ?? null,
            'instructor_id'   => isset($course['instructor_id']) ? (int)$course['instructor_id'] : null,
            'instructor_name' => $course['instructor_name'] ?? null,
            'credit_hours'    => (int)($course['credit_hours'] ?? 3),
            'semester'        => (string)($course['semester'] ?? ''),
            'academic_year'   => (string)($course['academic_year'] ?? ''),
            'max_capacity'    => (int)($course['max_capacity'] ?? 40),
            'enrolled_count'  => isset($course['enrolled_count']) ? (int)$course['enrolled_count'] : 0,
            'description'     => $course['description'] ?? null,
        ];
    }
}
