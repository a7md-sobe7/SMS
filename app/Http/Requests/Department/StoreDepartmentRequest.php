<?php

declare(strict_types=1);

namespace App\Http\Requests\Department;

use App\Core\FormRequest;
use App\Core\Gate;
use App\DTOs\Department\DepartmentDTO;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::getInstance()->allows('create', 'department');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|min:2|max:100',
            'code' => 'required|min:2|max:10|unique:departments,code',
        ];
    }

    public function toDTO(): DepartmentDTO
    {
        return DepartmentDTO::fromArray($this->validated());
    }
}
