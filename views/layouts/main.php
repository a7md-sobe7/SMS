<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Student Management System') ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8f9fa; }
        .sidebar { background: #1e293b !important; min-height: 100vh; }
        .sidebar .nav-link { color: #94a3b8; font-weight: 500; border-radius: 6px; padding: 0.6rem 0.8rem; margin-bottom: 0.2rem; }
        .sidebar .nav-link:hover { color: #f8fafc; background: rgba(255,255,255,0.08); }
        .sidebar .nav-link.active { color: #ffffff !important; background-color: #3b82f6 !important; }
        .card { border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); }
        .btn-primary { background-color: #2563eb; border-color: #2563eb; }
        .btn-primary:hover { background-color: #1d4ed8; border-color: #1d4ed8; }
        .table > :not(caption) > * > * { padding: 0.75rem 1rem; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <?php require __DIR__ . '/partials/sidebar.php'; ?>

            <!-- Main Application View Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 p-0">
                <?php require __DIR__ . '/partials/navbar.php'; ?>

                <div class="p-4">
                    <!-- Global Session Alerts -->
                    <?php require __DIR__ . '/partials/alerts.php'; ?>

                    <!-- View Injection Point -->
                    <?= $content ?? '' ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
