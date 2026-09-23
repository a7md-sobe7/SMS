<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\DashboardService;
use App\Services\DepartmentService;

class ApiDashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private DepartmentService $departmentService
    ) {}

    public function stats(Request $request): Response
    {
        $user = auth_user();
        $role = auth_role();

        $stats = $this->dashboardService->getAdminStats();
        $stats['departments'] = $this->departmentService->getWithStatistics();

        if ($role === 'student' && isset($user['id'])) {
            $studentStats = $this->dashboardService->getStudentStats((int)$user['id']);
            $stats = array_merge($stats, $studentStats);
        } elseif ($role === 'instructor' && isset($user['id'])) {
            $instructorStats = $this->dashboardService->getInstructorStats((int)$user['id']);
            $stats = array_merge($stats, $instructorStats);
        }

        return Response::rawJson([
            'success' => true,
            'message' => 'Dashboard statistics loaded successfully.',
            'data'    => [
                'role'  => $role,
                'user'  => $user,
                'stats' => $stats
            ]
        ], 200);
    }
}
