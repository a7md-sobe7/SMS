<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\DashboardService;
use App\Services\DepartmentService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private DepartmentService $departmentService
    ) {}

    public function index(Request $request): void
    {
        $role = auth_role();
        $adminStats = $this->dashboardService->getAdminStats();
        $adminStats['departments'] = $this->departmentService->getWithStatistics();

        $this->render('dashboard/admin', [
            'pageTitle' => 'Academic Dashboard',
            'stats'     => $adminStats,
            'user'      => auth_user(),
            'role'      => $role
        ]);
    }
}
