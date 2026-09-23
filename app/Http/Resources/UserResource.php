<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Core\Request;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = (array)$this->resource;

        return [
            'id'         => (int)($user['id'] ?? 0),
            'username'   => (string)($user['username'] ?? ''),
            'email'      => (string)($user['email'] ?? ''),
            'role'       => (string)($user['role'] ?? 'student'),
            'is_active'  => (bool)($user['is_active'] ?? true),
            'created_at' => (string)($user['created_at'] ?? ''),
        ];
    }
}
