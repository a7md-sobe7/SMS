<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class RoleMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, array $args = []): mixed
    {
        if (!auth_check()) {
            if ($request->isAjax() || str_starts_with($request->getPath(), '/api/')) {
                json_response(null, 401, 'Unauthorized.');
            }
            redirect('/login');
        }

        $allowedRoles = $args;
        if (!empty($allowedRoles) && !has_role($allowedRoles)) {
            if ($request->isAjax() || str_starts_with($request->getPath(), '/api/')) {
                json_response(null, 403, 'Forbidden. Insufficient permissions.');
            }

            http_response_code(403);
            view('errors/403', ['message' => 'You do not have permission to access this resource.'], 'main');
            exit;
        }

        return null;
    }
}
