<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Faculty & Instructors</h3>
        <p class="text-muted mb-0">Directory of university professors and teaching faculty</p>
    </div>
    <?php if (has_role('admin')): ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInstructorModal">
        <i class="bi bi-person-plus-fill me-1"></i> Add Instructor
    </button>
    <?php endif; ?>
</div>

<div class="row g-4">
    <?php foreach ($instructors as $inst): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="avatar bg-success-subtle text-success fw-bold rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 60px; height: 60px; font-size: 1.4rem;">
                        <?= strtoupper(substr($inst['first_name'], 0, 1) . substr($inst['last_name'], 0, 1)) ?>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?= e($inst['first_name'] . ' ' . $inst['last_name']) ?></h5>
                    <div class="badge bg-secondary font-monospace mb-3"><?= e($inst['employee_code']) ?></div>

                    <div class="border-top pt-3 text-start small">
                        <div class="mb-2">
                            <span class="text-muted d-block">Department:</span>
                            <span class="fw-semibold"><?= e($inst['department_name']) ?></span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted d-block">Email:</span>
                            <span class="fw-semibold"><?= e($inst['email']) ?></span>
                        </div>
                        <div>
                            <span class="text-muted d-block">Phone:</span>
                            <span class="fw-semibold"><?= e($inst['phone'] ?: 'N/A') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Add Instructor Modal -->
<?php if (has_role('admin')): ?>
<div class="modal fade" id="createInstructorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/instructors" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Faculty Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Employee Code</label>
                        <input type="text" name="employee_code" class="form-control" placeholder="INS-2026-005" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label small fw-semibold">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label small fw-semibold">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Department</label>
                        <select name="department_id" class="form-select" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= (int)$dept['id'] ?>"><?= e($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Instructor</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
