<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Core\Database;
use App\Core\Events\EventDispatcher;
use App\Core\Session;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Events\UserLoggedInEvent;
use App\Exceptions\AuthenticationException;
use App\Repositories\InstructorRepository;
use App\Repositories\StudentRepository;
use PDO;

class AuthService
{
    private UserRepositoryInterface $userRepo;
    private AuditLogRepositoryInterface $auditRepo;
    private EventDispatcher $dispatcher;

    public function __construct(
        UserRepositoryInterface $userRepo,
        AuditLogRepositoryInterface $auditRepo,
        ?EventDispatcher $dispatcher = null
    ) {
        $this->userRepo = $userRepo;
        $this->auditRepo = $auditRepo;
        $this->dispatcher = $dispatcher ?? EventDispatcher::getInstance();
    }

    /**
     * Authenticate user credentials and establish session.
     */
    public function attempt(LoginDTO|string $identifier, ?string $password = null): bool
    {
        $id = $identifier instanceof LoginDTO ? $identifier->username : $identifier;
        $pwd = $identifier instanceof LoginDTO ? $identifier->password : (string)$password;

        $user = $this->userRepo->findByUsernameOrEmail($id);

        if (!$user || !password_verify($pwd, $user['password_hash'])) {
            $this->auditRepo->log(
                null,
                'LOGIN_FAILED',
                'User',
                null,
                ['identifier' => $id]
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

        // Store clean user payload in session
        unset($user['password_hash']);
        Session::set('user', $user);

        // Dispatch Event
        $this->dispatcher->dispatch(new UserLoggedInEvent(
            userId: (int)$user['id'],
            username: $user['username'],
            role: $user['role'],
            ipAddress: $_SERVER['REMOTE_ADDR'] ?? null
        ));

        return true;
    }

    /**
     * Register a new user and link related role entity (Student / Instructor).
     */
    public function register(RegisterDTO|array $data): array
    {
        $payload = $data instanceof RegisterDTO ? $data->toArray() : $data;

        return Database::transaction(function (PDO $pdo) use ($payload) {
            $username = trim((string)$payload['username']);
            $email    = strtolower(trim((string)$payload['email']));
            $password = (string)$payload['password'];
            $role     = in_array($payload['role'] ?? '', ['student', 'instructor', 'registrar', 'admin'], true)
                ? (string)$payload['role']
                : 'student';

            $firstName = trim((string)($payload['first_name'] ?? ''));
            $lastName  = trim((string)($payload['last_name'] ?? ''));
            if ($firstName === '') {
                $firstName = ucfirst($username);
            }

            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            // 1. Create User
            $userId = $this->userRepo->create([
                'username'      => $username,
                'email'         => $email,
                'password_hash' => $passwordHash,
                'role'          => $role,
                'is_active'     => 1,
            ]);

            // Resolve department id if applicable
            $departmentId = !empty($payload['department_id']) ? (int)$payload['department_id'] : null;
            if (!$departmentId && in_array($role, ['student', 'instructor'], true)) {
                $deptStmt = $pdo->query("SELECT `id` FROM `departments` ORDER BY `id` ASC LIMIT 1");
                $firstDept = $deptStmt->fetch();
                $departmentId = $firstDept ? (int)$firstDept['id'] : 1;
            }

            // 2. Role-specific profile creation
            if ($role === 'student' && $departmentId) {
                $studentRepo = new StudentRepository($pdo);
                $year = (int)date('Y');
                $studentCode = sprintf('STU-%d-%04d', $year, $userId);

                $studentRepo->create([
                    'user_id'         => $userId,
                    'department_id'   => $departmentId,
                    'student_code'    => $studentCode,
                    'first_name'      => $firstName,
                    'last_name'       => $lastName ?: 'Student',
                    'email'           => $email,
                    'phone'           => trim((string)($payload['phone'] ?? '')),
                    'date_of_birth'   => !empty($payload['date_of_birth']) ? $payload['date_of_birth'] : date('Y-m-d', strtotime('-19 years')),
                    'gender'          => in_array($payload['gender'] ?? '', ['male', 'female', 'other'], true) ? $payload['gender'] : 'male',
                    'address'         => trim((string)($payload['address'] ?? '')),
                    'enrollment_year' => $year,
                    'academic_level'  => in_array($payload['academic_level'] ?? '', ['freshman', 'sophomore', 'junior', 'senior', 'graduate'], true) ? $payload['academic_level'] : 'freshman',
                    'status'          => 'active',
                ]);
            } elseif ($role === 'instructor' && $departmentId) {
                $instructorRepo = new InstructorRepository($pdo);
                $year = (int)date('Y');
                $empCode = sprintf('FAC-%d-%04d', $year, $userId);

                $instructorRepo->create([
                    'user_id'       => $userId,
                    'department_id' => $departmentId,
                    'employee_code' => $empCode,
                    'first_name'    => $firstName,
                    'last_name'     => $lastName ?: 'Faculty',
                    'email'         => $email,
                    'phone'         => trim((string)($payload['phone'] ?? '')),
                ]);
            }

            // 3. Prevent Session Fixation and log user in
            Session::regenerate();

            $user = $this->userRepo->find($userId);
            unset($user['password_hash']);
            Session::set('user', $user);

            // 4. Audit Log
            $this->auditRepo->log(
                $userId,
                'REGISTER_SUCCESS',
                'User',
                $userId,
                [
                    'username' => $username,
                    'role'     => $role,
                    'email'    => $email
                ]
            );

            return $user;
        });
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
