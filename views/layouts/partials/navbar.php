<?php
$currentUser = auth_user() ?? [];
$userEmail = $currentUser['email'] ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm px-3 py-2">
    <div class="container-fluid p-0">
        <span class="navbar-brand mb-0 h5 text-secondary fw-semibold">
            <?= e($pageTitle ?? 'Student Management System') ?>
        </span>

        <div class="d-flex align-items-center ms-auto gap-3">
            <?php if (auth_check()): ?>
                <div class="text-end d-none d-sm-block">
                    <div class="small fw-semibold text-dark"><?= e($userEmail) ?></div>
                    <div class="text-muted" style="font-size: 0.75rem;">Status: <span class="text-success fw-bold">Active</span></div>
                </div>
            <?php endif; ?>
            <a href="/dashboard" class="btn btn-light btn-sm border rounded-circle" title="Dashboard">
                <i class="bi bi-bell"></i>
            </a>
        </div>
    </div>
</nav>
