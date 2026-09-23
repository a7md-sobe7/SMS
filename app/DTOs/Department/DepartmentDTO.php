<?php

declare(strict_types=1);

namespace App\DTOs\Department;

use App\DTOs\BaseDTO;

class DepartmentDTO extends BaseDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly ?string $description = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: trim((string)($data['name'] ?? '')),
            code: strtoupper(trim((string)($data['code'] ?? ''))),
            description: !empty($data['description']) ? trim((string)$data['description']) : null
        );
    }
}
