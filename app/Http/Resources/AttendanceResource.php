<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Core\Request;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $attendance = (array)$this->resource;

        return [
            'id'              => (int)($attendance['id'] ?? ($attendance['attendance_id'] ?? 0)),
            'student_id'      => (int)($attendance['student_id'] ?? 0),
            'student_code'    => $attendance['student_code'] ?? null,
            'student_name'    => isset($attendance['first_name']) ? trim($attendance['first_name'] . ' ' . ($attendance['last_name'] ?? '')) : null,
            'course_id'       => (int)($attendance['course_id'] ?? 0),
            'attendance_date' => (string)($attendance['attendance_date'] ?? ''),
            'status'          => (string)($attendance['status'] ?? 'present'),
            'notes'           => $attendance['notes'] ?? null,
        ];
    }
}
