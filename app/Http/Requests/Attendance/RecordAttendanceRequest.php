<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendance;

use App\Core\FormRequest;
use App\Core\Gate;
use App\DTOs\Attendance\AttendanceDTO;

class RecordAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::getInstance()->allows('record', 'attendance');
    }

    public function rules(): array
    {
        return [
            'student_id'      => 'required|numeric',
            'course_id'       => 'required|numeric',
            'attendance_date' => 'required|date',
            'status'          => 'required|in:present,late,absent,excused'
        ];
    }

    public function toDTO(): AttendanceDTO
    {
        return AttendanceDTO::fromArray($this->validated());
    }
}
