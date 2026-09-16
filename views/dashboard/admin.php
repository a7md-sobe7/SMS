<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Academic Overview</h3>
        <p class="text-muted mb-0">System performance metrics and active registrations</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/students/create" class="btn btn-primary btn-sm"><i class="bi bi-person-plus-fill me-1"></i> Add Student</a>
        <a href="/courses" class="btn btn-outline-secondary btn-sm"><i class="bi bi-plus-circle me-1"></i> Add Course</a>
    </div>
</div>

<!-- Metric Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Total Students</div>
                    <div class="fs-3 fw-bold text-dark"><?= (int)($stats['total_students'] ?? 0) ?></div>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                    <i class="bi bi-people fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Active Courses</div>
                    <div class="fs-3 fw-bold text-dark"><?= (int)($stats['total_courses'] ?? 0) ?></div>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle">
                    <i class="bi bi-journal-check fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Departments</div>
                    <div class="fs-3 fw-bold text-dark"><?= (int)($stats['total_departments'] ?? 0) ?></div>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                    <i class="bi bi-diagram-3 fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 border-start border-info border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase fw-bold">Faculty Members</div>
                    <div class="fs-3 fw-bold text-dark"><?= (int)($stats['total_instructors'] ?? 0) ?></div>
                </div>
                <div class="bg-info-subtle text-info p-3 rounded-circle">
                    <i class="bi bi-person-badge fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Department Distribution Table -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h5 class="card-title fw-bold mb-0">Department Academic Distribution</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Department</th>
                            <th class="text-center">Courses</th>
                            <th class="text-center">Students</th>
                            <th class="text-center">Faculty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($stats['departments'])): ?>
                            <?php foreach ($stats['departments'] as $dept): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= e($dept['name']) ?></div>
                                        <span class="badge bg-secondary"><?= e($dept['code']) ?></span>
                                    </td>
                                    <td class="text-center fw-semibold"><?= (int)$dept['total_courses'] ?></td>
                                    <td class="text-center fw-semibold text-primary"><?= (int)$dept['total_students'] ?></td>
                                    <td class="text-center fw-semibold"><?= (int)$dept['total_instructors'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No department records available.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Security & Audit Log Feed -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-white py-3">
                <h5 class="card-title fw-bold mb-0">Recent Activity & Audit Logs</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (!empty($stats['recent_logs'])): ?>
                        <?php foreach ($stats['recent_logs'] as $log): ?>
                            <div class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-dark-subtle text-dark fw-bold"><?= e($log['action']) ?></span>
                                    <small class="text-muted"><?= date('M d, H:i', strtotime($log['created_at'])) ?></small>
                                </div>
                                <div class="small text-secondary">
                                    <strong><?= e($log['username'] ?? 'System') ?></strong> performed action on <strong><?= e($log['entity_type']) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">No recent activity logged.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
