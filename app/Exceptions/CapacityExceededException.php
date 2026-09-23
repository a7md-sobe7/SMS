<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Thrown when course seat capacity or enrollment limit is exceeded (409 Conflict).
 */
class CapacityExceededException extends Exception
{
    public function __construct(string $message = 'Course capacity limit reached.', int $code = 409)
    {
        parent::__construct($message, $code);
    }
}
