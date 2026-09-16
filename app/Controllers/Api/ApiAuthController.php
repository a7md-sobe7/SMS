<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Services\AuthService;

class ApiAuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(Request $request): void
    {
        $identifier = (string)$request->input('identifier', '');
        $password   = (string)$request->input('password', '');

        $validator = Validator::make([
            'identifier' => $identifier,
            'password'   => $password
        ], [
            'identifier' => 'required',
            'password'   => 'required'
        ]);

        if ($validator->fails()) {
            json_response(null, 422, 'Validation failed.', $validator->errors());
        }

        if (!$this->authService->attempt($identifier, $password)) {
            json_response(null, 401, 'Invalid username/email or password.');
        }

        json_response([
            'user' => auth_user()
        ], 200, 'Authentication successful.');
    }

    public function me(Request $request): void
    {
        json_response([
            'user' => auth_user()
        ], 200);
    }

    public function logout(Request $request): void
    {
        $this->authService->logout();
        json_response(null, 200, 'Logged out successfully.');
    }
}
