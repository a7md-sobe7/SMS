<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Academic Departments</h3>
        <p class="text-muted mb-0">Manage university departments, division codes, and academic programs</p>
    </div>
    <?php if (has_role('admin')): ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDeptModal">
        <i class="bi bi-plus-lg me-1"></i> Add Department
    </button>
    <?php endif; ?>
</div>

<div class="row g-4">
    <?php foreach ($departments as $dept): ?>
        <div class="col-md-6 col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-primary fs-6"><?= e($dept['code']) ?></span>
                        <div class="text-muted small">ID: #<?= (int)$dept['id'] ?></div>
                    </div>
                    <h5 class="fw-bold text-dark mb-2"><?= e($dept['name']) ?></h5>
                    <p class="text-secondary small mb-4"><?= e($dept['description'] ?: 'No department description provided.') ?></p>

                    <div class="row g-2 text-center border-top pt-3">
                        <div class="col-4">
                            <div class="fw-bold text-dark"><?= (int)($dept['total_courses'] ?? 0) ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;">Courses</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-primary"><?= (int)($dept['total_students'] ?? 0) ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;">Students</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-success"><?= (int)($dept['total_instructors'] ?? 0) ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;">Faculty</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Add Department Modal (Admin Only) -->
<?php if (has_role('admin')): ?>
<div class="modal fade" id="createDeptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/departments" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create New Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Department Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. CS, EE, BIO" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Department Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Computer Science" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
