<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Course Catalog & Offerings</h3>
        <p class="text-muted mb-0">Browse university curricula, instructor assignments, and term capacity</p>
    </div>
    <?php if (has_role(['admin', 'registrar'])): ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
        <i class="bi bi-plus-lg me-1"></i> Add Course
    </button>
    <?php endif; ?>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Course Title</th>
                    <th>Department</th>
                    <th>Instructor</th>
                    <th>Term</th>
                    <th>Credits</th>
                    <th>Capacity / Enrolled</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($courses)): ?>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td><span class="badge bg-dark font-monospace"><?= e($c['course_code']) ?></span></td>
                            <td>
                                <div class="fw-bold text-dark"><?= e($c['course_name']) ?></div>
                                <small class="text-muted"><?= e($c['description'] ?: 'No syllabus description') ?></small>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?= e($c['department_name']) ?></span></td>
                            <td><?= e($c['instructor_name'] ?: 'Unassigned') ?></td>
                            <td><?= e($c['semester']) ?> <?= e($c['academic_year']) ?></td>
                            <td><strong><?= (int)$c['credit_hours'] ?></strong> hrs</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-primary"><?= (int)$c['enrolled_count'] ?></span> / <?= (int)$c['capacity'] ?>
                                    <div class="progress flex-grow-1" style="height: 6px; min-width: 60px;">
                                        <?php $pct = min(100, round(($c['enrolled_count'] / max(1, $c['capacity'])) * 100)); ?>
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">No courses registered yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Course Modal -->
<?php if (has_role(['admin', 'registrar'])): ?>
<div class="modal fade" id="createCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/courses" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Register New Academic Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Course Code</label>
                        <input type="text" name="course_code" class="form-control" placeholder="CS301" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Course Name</label>
                        <input type="text" name="course_name" class="form-control" placeholder="Object-Oriented Programming" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Department</label>
                        <select name="department_id" class="form-select" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= (int)$dept['id'] ?>"><?= e($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Instructor</label>
                        <select name="instructor_id" class="form-select">
                            <option value="">Unassigned</option>
                            <?php foreach ($instructors as $inst): ?>
                                <option value="<?= (int)$inst['id'] ?>"><?= e($inst['first_name'] . ' ' . $inst['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Credit Hours</label>
                        <input type="number" name="credit_hours" class="form-control" value="3" min="1" max="6" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Semester</label>
                        <select name="semester" class="form-select" required>
                            <option value="Fall">Fall</option>
                            <option value="Spring">Spring</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Academic Year</label>
                        <input type="number" name="academic_year" class="form-control" value="<?= date('Y') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Seat Capacity</label>
                        <input type="number" name="capacity" class="form-control" value="30" min="1" max="500" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Description / Syllabus</label>
                        <input type="text" name="description" class="form-control" placeholder="Topics covered...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Course</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
