<div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-3" style="width: 56px; height: 56px;">
        <i class="bi bi-mortarboard-fill fs-3"></i>
    </div>
    <h3 class="fw-bold text-dark mb-1">Student Portal</h3>
    <p class="text-muted small">Sign in with your academic credentials</p>
</div>

<form action="/login" method="POST">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="identifier" class="form-label small fw-semibold text-secondary">Username or Email</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-secondary"><i class="bi bi-person"></i></span>
            <input type="text" name="identifier" id="identifier" class="form-control" placeholder="admin or admin@sms.edu" value="<?= e(old('identifier', 'admin')) ?>" required autofocus>
        </div>
    </div>

    <div class="mb-4">
        <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
        <div class="input-group">
            <span class="input-group-text bg-light text-secondary"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" value="Admin@123456" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to System
    </button>
</form>

<div class="mt-4 p-3 bg-light rounded border text-muted small">
    <div class="fw-bold text-dark mb-1"><i class="bi bi-info-circle me-1"></i> Demo Access Credentials:</div>
    <div class="d-flex justify-content-between mb-1"><span>Admin:</span> <code>admin</code> / <code>Admin@123456</code></div>
    <div class="d-flex justify-content-between mb-1"><span>Registrar:</span> <code>registrar</code> / <code>Admin@123456</code></div>
    <div class="d-flex justify-content-between mb-1"><span>Instructor:</span> <code>dr.alan</code> / <code>Admin@123456</code></div>
    <div class="d-flex justify-content-between"><span>Student:</span> <code>john.doe</code> / <code>Admin@123456</code></div>
</div>
