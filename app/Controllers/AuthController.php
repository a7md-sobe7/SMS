<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\DTOs\Auth\LoginDTO;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function showLoginForm(Request $request): void
    {
        if (auth_check()) {
            redirect('/dashboard');
        }

        $this->render('auth/login', [
            'pageTitle' => 'Sign In - Student Management System'
        ], 'auth');
    }

    public function login(Request $request): void
    {
        $identifier = trim((string)($request->input('identifier') ?? $request->input('username', '')));
        $password   = (string)$request->input('password', '');

        if ($identifier === '' || $password === '') {
            Session::flash('error', 'Please enter your username/email and password.');
            Session::flash('_old_input', ['identifier' => $identifier]);
            redirect('/login');
            return;
        }

        $dto = new LoginDTO(username: $identifier, password: $password);

        if (!$this->authService->attempt($dto)) {
            Session::flash('error', 'Invalid credentials or inactive account.');
            Session::flash('_old_input', ['identifier' => $identifier]);
            redirect('/login');
            return;
        }

        Session::flash('success', 'Welcome back, ' . htmlspecialchars(auth_user()['username'] ?? '') . '!');
        redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        $this->authService->logout();
        redirect('/login');
    }
}
