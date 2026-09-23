<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Thrown when an authenticated user lacks required policy permissions (403 Forbidden).
 */
class AuthorizationException extends Exception
{
    public function __construct(string $message = 'This action is unauthorized.', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
