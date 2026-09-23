<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found - Student Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
    <div class="container text-center">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 p-4">
                    <div class="mb-3 text-info">
                        <i class="bi bi-compass" style="font-size: 4rem;"></i>
                    </div>
                    <h1 class="display-4 fw-bold text-dark">404</h1>
                    <h4 class="mb-3">Page or Record Not Found</h4>
                    <p class="text-muted mb-4"><?= htmlspecialchars($message ?? ($exception->getMessage() ?? 'The requested resource could not be found.')) ?></p>
                    <div>
                        <a href="/dashboard" class="btn btn-primary px-4"><i class="bi bi-house me-2"></i>Return to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
