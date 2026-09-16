<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Session;

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, array $args = []): mixed
    {
        $method = $request->getMethod();

        // Only enforce CSRF verification on state-modifying verbs
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $token = $request->input('_csrf_token') ?? $request->getHeader('x-csrf-token');

            if (!Csrf::validate($token)) {
                if ($request->isAjax() || str_starts_with($request->getPath(), '/api/')) {
                    json_response(null, 419, 'CSRF token mismatch. Please refresh and try again.');
                }

                Session::flash('error', 'Your session or CSRF token expired. Please try again.');
                redirect($request->getPath());
            }
        }

        return null;
    }
}
