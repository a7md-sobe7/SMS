<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Session;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, array $args = []): mixed
    {
        if (!auth_check()) {
            if ($request->isAjax() || str_starts_with($request->getPath(), '/api/')) {
                json_response(null, 401, 'Unauthorized. Please provide a valid authentication session.');
            }

            Session::flash('error', 'You must log in to access this page.');
            redirect('/login');
        }

        return null; // Authorized, continue to next middleware/controller
    }
}
