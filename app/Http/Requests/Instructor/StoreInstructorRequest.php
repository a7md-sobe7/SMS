<?php

declare(strict_types=1);

namespace App\Http\Requests\Instructor;

use App\Core\FormRequest;
use App\Core\Gate;
use App\DTOs\Instructor\InstructorDTO;

class StoreInstructorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::getInstance()->allows('create', 'instructor');
    }

    public function rules(): array
    {
        return [
            'employee_code' => 'required|unique:instructors,employee_code|min:2|max:20',
            'first_name'    => 'required|min:2|max:50',
            'last_name'     => 'required|min:2|max:50',
            'email'         => 'required|email|unique:instructors,email',
            'department_id' => 'required|numeric',
            'hire_date'     => 'required|date'
        ];
    }

    public function toDTO(): InstructorDTO
    {
        return InstructorDTO::fromArray($this->validated());
    }
}
