<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Student Records</h3>
        <p class="text-muted mb-0">Browse, filter, and manage institutional student profiles</p>
    </div>
    <?php if (has_role(['admin', 'registrar'])): ?>
    <a href="/students/create" class="btn btn-primary"><i class="bi bi-person-plus-fill me-1"></i> Register Student</a>
    <?php endif; ?>
</div>

<!-- Search & Filter Card -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/students" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name, student code, email..." value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= (int)$dept['id'] ?>" <?= ($filters['department_id'] ?? null) == $dept['id'] ? 'selected' : '' ?>>
                            <?= e($dept['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                    <option value="graduated" <?= ($filters['status'] ?? '') === 'graduated' ? 'selected' : '' ?>>Graduated</option>
                    <option value="withdrawn" <?= ($filters['status'] ?? '') === 'withdrawn' ? 'selected' : '' ?>>Withdrawn</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                <a href="/students" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Students Data Table -->
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Code</th>
                    <th>Department</th>
                    <th>Level</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $stu): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <?= strtoupper(substr($stu['first_name'], 0, 1) . substr($stu['last_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?></div>
                                        <div class="text-muted small"><?= e($stu['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary font-monospace"><?= e($stu['student_code']) ?></span></td>
                            <td><?= e($stu['department_name'] ?? 'Unassigned') ?></td>
                            <td><span class="text-capitalize"><?= e($stu['academic_level']) ?></span></td>
                            <td>
                                <?php
                                $statusBadges = [
                                    'active'    => 'bg-success',
                                    'suspended' => 'bg-danger',
                                    'graduated' => 'bg-info',
                                    'withdrawn' => 'bg-secondary'
                                ];
                                $badge = $statusBadges[$stu['status']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $badge ?> text-capitalize"><?= e($stu['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="/students/<?= (int)$stu['id'] ?>" class="btn btn-outline-primary" title="View Profile">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (has_role(['admin', 'registrar'])): ?>
                                    <a href="/students/<?= (int)$stu['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">No students found matching current query.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
            <div class="small text-muted">
                Showing page <strong><?= (int)$pagination['current_page'] ?></strong> of <strong><?= (int)$pagination['total_pages'] ?></strong> (Total <?= (int)$pagination['total'] ?> students)
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($pagination['has_prev']): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= (int)$pagination['current_page'] - 1 ?>">Previous</a></li>
                    <?php endif; ?>
                    <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                        <li class="page-item <?= $p === $pagination['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($pagination['has_next']): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?= (int)$pagination['current_page'] + 1 ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
