<?php

declare(strict_types=1);

namespace App\Http\Requests\Grade;

use App\Core\FormRequest;
use App\Core\Gate;
use App\DTOs\Grade\GradeDTO;

class UpsertGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::getInstance()->allows('upsert', 'grade');
    }

    public function rules(): array
    {
        return [
            'enrollment_id'    => 'required|numeric',
            'assignment_grade' => 'numeric|min:0|max:100',
            'midterm_grade'    => 'numeric|min:0|max:100',
            'final_grade'      => 'numeric|min:0|max:100'
        ];
    }

    public function toDTO(): GradeDTO
    {
        return GradeDTO::fromArray($this->validated());
    }
}
