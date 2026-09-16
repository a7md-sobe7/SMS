<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$role = auth_role() ?? 'guest';
$currentUser = auth_user() ?? [];
$username = $currentUser['username'] ?? 'User';
$userRole = $currentUser['role'] ?? 'Guest';
?>

<div class="sidebar col-md-3 col-lg-2 p-0 bg-dark text-white min-vh-100 shadow">
    <div class="p-3 border-bottom border-secondary">
        <a href="/dashboard" class="d-flex align-items-center text-white text-decoration-none">
            <span class="fs-5 fw-bold text-primary"><i class="bi bi-mortarboard-fill me-2"></i>SMS Portal</span>
        </a>
    </div>

    <div class="px-3 py-2 text-muted small text-uppercase fw-bold">Academic Modules</div>
    <ul class="nav nav-pills flex-column px-2 mb-auto">
        <li class="nav-item mb-1">
            <a href="/dashboard" class="nav-link text-white <?= $currentPath === '/' || $currentPath === '/dashboard' ? 'active bg-primary' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <?php if (has_role(['admin', 'registrar', 'instructor'])): ?>
        <li class="nav-item mb-1">
            <a href="/students" class="nav-link text-white <?= str_starts_with($currentPath, '/students') ? 'active bg-primary' : '' ?>">
                <i class="bi bi-people-fill me-2"></i> Students
            </a>
        </li>
        <?php endif; ?>

        <li class="nav-item mb-1">
            <a href="/departments" class="nav-link text-white <?= str_starts_with($currentPath, '/departments') ? 'active bg-primary' : '' ?>">
                <i class="bi bi-diagram-3-fill me-2"></i> Departments
            </a>
        </li>

        <li class="nav-item mb-1">
            <a href="/instructors" class="nav-link text-white <?= str_starts_with($currentPath, '/instructors') ? 'active bg-primary' : '' ?>">
                <i class="bi bi-person-badge-fill me-2"></i> Instructors
            </a>
        </li>

        <li class="nav-item mb-1">
            <a href="/courses" class="nav-link text-white <?= str_starts_with($currentPath, '/courses') ? 'active bg-primary' : '' ?>">
                <i class="bi bi-journal-bookmark-fill me-2"></i> Courses
            </a>
        </li>

        <?php if (has_role(['admin', 'registrar'])): ?>
        <li class="nav-item mb-1">
            <a href="/enrollments" class="nav-link text-white <?= str_starts_with($currentPath, '/enrollments') ? 'active bg-primary' : '' ?>">
                <i class="bi bi-ui-checks-grid me-2"></i> Enrollments
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_role(['admin', 'instructor', 'student'])): ?>
        <li class="nav-item mb-1">
            <a href="/grades" class="nav-link text-white <?= str_starts_with($currentPath, '/grades') ? 'active bg-primary' : '' ?>">
                <i class="bi bi-award-fill me-2"></i> Grades
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_role(['admin', 'instructor', 'student'])): ?>
        <li class="nav-item mb-1">
            <a href="/attendance" class="nav-link text-white <?= str_starts_with($currentPath, '/attendance') ? 'active bg-primary' : '' ?>">
                <i class="bi bi-calendar2-check-fill me-2"></i> Attendance
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="mt-auto p-3 border-top border-secondary">
        <?php if (auth_check()): ?>
            <div class="d-flex align-items-center mb-2">
                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 38px; height: 38px;">
                    <?= strtoupper(substr($username, 0, 1)) ?>
                </div>
                <div>
                    <div class="fw-bold small text-truncate" style="max-width: 120px;"><?= e($username) ?></div>
                    <span class="badge bg-secondary text-uppercase" style="font-size: 0.65rem;"><?= e($userRole) ?></span>
                </div>
            </div>
            <form action="/logout" method="POST" class="d-grid">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                </button>
            </form>
        <?php else: ?>
            <a href="/login" class="btn btn-primary btn-sm w-100">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </a>
        <?php endif; ?>
    </div>
</div>
