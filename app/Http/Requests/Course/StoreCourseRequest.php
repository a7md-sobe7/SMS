<?php

declare(strict_types=1);

namespace App\Http\Requests\Course;

use App\Core\FormRequest;
use App\Core\Gate;
use App\DTOs\Course\CourseDTO;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::getInstance()->allows('create', 'course');
    }

    public function rules(): array
    {
        return [
            'course_code'   => 'required|unique:courses,course_code|min:2|max:20',
            'course_name'   => 'required|min:3|max:100',
            'department_id' => 'required|numeric',
            'credit_hours'  => 'required|numeric|min:1|max:6',
            'semester'      => 'required|in:Fall,Spring,Summer,Winter',
            'academic_year' => 'required|min:4',
            'max_capacity'  => 'required|numeric|min:1'
        ];
    }

    public function toDTO(): CourseDTO
    {
        return CourseDTO::fromArray($this->validated());
    }
}
