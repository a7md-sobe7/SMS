<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Academic Grade Evaluation</h3>
        <p class="text-muted mb-0">Record assignment, midterm, and final grades with automated letter computation</p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/grades" class="d-flex gap-2">
            <select name="course_id" class="form-select">
                <option value="">-- Choose Course to Grade --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= ($courseId ?? null) == $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['course_code']) ?>: <?= e($c['course_name']) ?> (<?= e($c['semester']) ?> <?= e($c['academic_year']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary px-4">Load Roster</button>
        </form>
    </div>
</div>

<?php if ($selectedCourse): ?>
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="card-title fw-bold mb-0">Grade Entry: <?= e($selectedCourse['course_code']) ?> - <?= e($selectedCourse['course_name']) ?></h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Assignment (20%)</th>
                    <th>Midterm (30%)</th>
                    <th>Final (50%)</th>
                    <th>Total Score</th>
                    <th>Letter</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($roster)): ?>
                    <?php foreach ($roster as $row): ?>
                        <tr>
                            <form action="/grades/upsert" method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="enrollment_id" value="<?= (int)$row['enrollment_id'] ?>">
                                <input type="hidden" name="course_id" value="<?= (int)$selectedCourse['id'] ?>">

                                <td>
                                    <div class="fw-bold"><?= e($row['first_name'] . ' ' . $row['last_name']) ?></div>
                                    <small class="text-muted"><?= e($row['student_code']) ?></small>
                                </td>
                                <td style="max-width: 110px;">
                                    <input type="number" step="0.1" min="0" max="100" name="assignment_grade" class="form-control form-control-sm" value="<?= (float)$row['assignment_grade'] ?>" required>
                                </td>
                                <td style="max-width: 110px;">
                                    <input type="number" step="0.1" min="0" max="100" name="midterm_grade" class="form-control form-control-sm" value="<?= (float)$row['midterm_grade'] ?>" required>
                                </td>
                                <td style="max-width: 110px;">
                                    <input type="number" step="0.1" min="0" max="100" name="final_grade" class="form-control form-control-sm" value="<?= (float)$row['final_grade'] ?>" required>
                                </td>
                                <td>
                                    <strong><?= $row['total_grade'] ? number_format((float)$row['total_grade'], 1) . '%' : '0.0%' ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-primary fs-6"><?= e($row['letter_grade'] ?: 'F') ?></span>
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-save me-1"></i> Save</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-5">No enrolled students in this course.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
