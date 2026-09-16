<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\AuditLogRepository;
use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $userRepo;
    private AuditLogRepository $auditRepo;

    public function __construct(
        ?UserRepository $userRepo = null,
        ?AuditLogRepository $auditRepo = null
    ) {
        $this->userRepo = $userRepo ?? new UserRepository();
        $this->auditRepo = $auditRepo ?? new AuditLogRepository();
    }

    /**
     * Authenticate user credentials and establish session.
     */
    public function attempt(string $identifier, string $password): bool
    {
        $user = $this->userRepo->findByUsernameOrEmail($identifier);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->auditRepo->log(
                null,
                'LOGIN_FAILED',
                'User',
                null,
                ['identifier' => $identifier]
            );
            return false;
        }

        if ((int)$user['is_active'] !== 1) {
            $this->auditRepo->log(
                (int)$user['id'],
                'LOGIN_BLOCKED_INACTIVE',
                'User',
                (int)$user['id']
            );
            return false;
        }

        // Prevent Session Fixation
        Session::regenerate();

        // Store clean user payload in session (excluding password hash)
        unset($user['password_hash']);
        Session::set('user', $user);

        // Audit successful login
        $this->auditRepo->log(
            (int)$user['id'],
            'LOGIN_SUCCESS',
            'User',
            (int)$user['id'],
            ['username' => $user['username'], 'role' => $user['role']]
        );

        return true;
    }

    /**
     * Terminate user session.
     */
    public function logout(): void
    {
        $user = Session::get('user');
        if ($user && isset($user['id'])) {
            $this->auditRepo->log(
                (int)$user['id'],
                'LOGOUT',
                'User',
                (int)$user['id']
            );
        }

        Session::destroy();
    }
}
