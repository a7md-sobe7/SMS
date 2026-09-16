<div class="text-center py-5">
    <div class="display-1 text-danger fw-bold"><i class="bi bi-shield-lock-fill"></i> 403</div>
    <h3 class="fw-bold mt-3">Access Denied</h3>
    <p class="text-muted"><?= e($message ?? 'You do not have permission to access this resource.') ?></p>
    <a href="/dashboard" class="btn btn-primary mt-3"><i class="bi bi-house-door-fill me-1"></i> Return to Dashboard</a>
</div>
