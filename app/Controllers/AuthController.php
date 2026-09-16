<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

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
        $identifier = trim((string)$request->input('identifier', ''));
        $password   = (string)$request->input('password', '');

        $validator = Validator::make([
            'identifier' => $identifier,
            'password'   => $password
        ], [
            'identifier' => 'required',
            'password'   => 'required'
        ]);

        if ($validator->fails()) {
            Session::flash('error', 'Please enter your username/email and password.');
            Session::flash('_old_input', ['identifier' => $identifier]);
            redirect('/login');
        }

        if (!$this->authService->attempt($identifier, $password)) {
            Session::flash('error', 'Invalid credentials or inactive account.');
            Session::flash('_old_input', ['identifier' => $identifier]);
            redirect('/login');
        }

        Session::flash('success', 'Welcome back, ' . htmlspecialchars(auth_user()['username']) . '!');
        redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        $this->authService->logout();
        redirect('/login');
    }
}
