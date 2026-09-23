<?php

declare(strict_types=1);

namespace App\Http\Requests\Enrollment;

use App\Core\FormRequest;
use App\Core\Gate;
use App\DTOs\Enrollment\EnrollmentDTO;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::getInstance()->allows('enroll', 'enrollment');
    }

    public function rules(): array
    {
        return [
            'student_id'      => 'required|numeric',
            'course_id'       => 'required|numeric',
            'enrollment_date' => 'date'
        ];
    }

    public function toDTO(): EnrollmentDTO
    {
        return EnrollmentDTO::fromArray($this->validated());
    }
}
