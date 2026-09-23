<?php

declare(strict_types=1);

namespace App\Http\Requests\Student;

use App\Core\FormRequest;
use App\Core\Gate;
use App\DTOs\Student\StudentDTO;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::getInstance()->allows('create', 'student');
    }

    public function rules(): array
    {
        return [
            'student_code'    => 'required|unique:students,student_code',
            'first_name'      => 'required|min:2|max:50',
            'last_name'       => 'required|min:2|max:50',
            'email'           => 'required|email|unique:students,email',
            'department_id'   => 'required|numeric',
            'date_of_birth'   => 'required|date',
            'gender'          => 'required|in:male,female,other',
            'enrollment_year' => 'required|numeric',
            'academic_level'  => 'required|in:freshman,sophomore,junior,senior,graduate',
            'status'          => 'required|in:active,suspended,graduated,withdrawn'
        ];
    }

    public function toDTO(): StudentDTO
    {
        return StudentDTO::fromArray($this->validated());
    }
}
