<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Internal Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="container p-4" style="max-width: 800px;">
        <div class="card border-danger shadow p-4 text-center">
            <div class="display-1 text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> 500</div>
            <h3 class="fw-bold mt-3">Internal Server Error</h3>
            <p class="text-muted">A server error occurred while processing your request.</p>

            <?php if (!empty($isDebug) && !empty($exception)): ?>
                <div class="alert alert-danger text-start mt-3">
                    <div class="fw-bold fs-6 mb-2"><?= htmlspecialchars($exception->getMessage()) ?></div>
                    <div class="small text-muted mb-2">Location: <?= htmlspecialchars($exception->getFile()) ?>:<?= (int)$exception->getLine() ?></div>
                    <pre class="bg-dark text-white p-3 rounded small mb-0" style="max-height: 300px; overflow: auto;"><?= htmlspecialchars($exception->getTraceAsString()) ?></pre>
                </div>
            <?php endif; ?>

            <div class="mt-3">
                <a href="/login" class="btn btn-primary"><i class="bi bi-arrow-clockwise me-1"></i> Retry Login</a>
            </div>
        </div>
    </div>
</body>
</html>
