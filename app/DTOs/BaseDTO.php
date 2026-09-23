<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Core\Request;

/**
 * Base Data Transfer Object (DTO)
 * 
 * Provides structured, immutable typed data encapsulation across layers.
 */
abstract class BaseDTO
{
    /**
     * Convert DTO instance properties to an associative array.
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        $result = [];

        foreach ($vars as $key => $value) {
            if ($value instanceof self) {
                $result[$key] = $value->toArray();
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Filter out null properties.
     */
    public function toFilteredArray(): array
    {
        return array_filter($this->toArray(), fn($v) => $v !== null);
    }
}
