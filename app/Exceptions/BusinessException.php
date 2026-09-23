<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Thrown when a business domain rule or invariant is violated.
 */
class BusinessException extends Exception
{
    private string $errorCode;

    public function __construct(string $message, string $errorCode = 'BUSINESS_RULE_VIOLATION', int $code = 422)
    {
        parent::__construct($message, $code);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
