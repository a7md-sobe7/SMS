<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\DTOs\Auth\LoginDTO;
use App\Exceptions\AuthenticationException;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;

class ApiAuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function login(Request $request): Response
    {
        $identifier = (string)($request->input('identifier') ?? $request->input('username', ''));
        $password   = (string)$request->input('password', '');

        if ($identifier === '' || $password === '') {
            throw new AuthenticationException('Please provide both username/email and password.');
        }

        $dto = new LoginDTO(username: $identifier, password: $password);

        if (!$this->authService->attempt($dto)) {
            throw new AuthenticationException('Invalid username/email or password.');
        }

        $user = auth_user();
        $userResource = UserResource::make($user)->toArray($request);

        return Response::rawJson([
            'success' => true,
            'message' => 'Authentication successful.',
            'data'    => array_merge(['user' => $userResource], $userResource)
        ], 200);
    }

    public function register(RegisterRequest $request): Response
    {
        $dto = $request->toDTO();
        $user = $this->authService->register($dto);
        $userResource = UserResource::make($user)->toArray($request);

        return Response::rawJson([
            'success' => true,
            'message' => 'Account successfully created!',
            'data'    => array_merge(['user' => $userResource], $userResource)
        ], 201);
    }

    public function me(Request $request): Response
    {
        $user = auth_user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated session.');
        }

        $userResource = UserResource::make($user)->toArray($request);

        return Response::rawJson([
            'success' => true,
            'message' => 'Profile retrieved.',
            'data'    => array_merge(['user' => $userResource], $userResource)
        ], 200);
    }

    public function logout(Request $request): Response
    {
        $this->authService->logout();
        return Response::rawJson([
            'success' => true,
            'message' => 'Logged out successfully.'
        ], 200);
    }
}
