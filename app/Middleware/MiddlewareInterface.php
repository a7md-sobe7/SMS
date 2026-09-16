<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;

/**
 * Middleware Contract
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming request.
     * Return null to continue to the next middleware/handler, or return a Response / redirect to halt execution.
     * 
     * @param Request $request
     * @param array $args Optional route/middleware arguments (e.g. ['admin', 'registrar'])
     * @return mixed
     */
    public function handle(Request $request, array $args = []): mixed;
}
