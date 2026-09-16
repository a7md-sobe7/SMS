<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Course Attendance Roll-Call</h3>
        <p class="text-muted mb-0">Record and track daily classroom presence for active sessions</p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="/attendance" class="row g-2 align-items-center">
            <div class="col-md-7">
                <select name="course_id" class="form-select">
                    <option value="">-- Choose Course for Roll-Call --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ($courseId ?? null) == $c['id'] ? 'selected' : '' ?>>
                            <?= e($c['course_code']) ?>: <?= e($c['course_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" value="<?= e($currentDate ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Load Class</button>
            </div>
        </form>
    </div>
</div>

<?php if ($selectedCourse): ?>
<div class="card shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title fw-bold mb-0">Session: <?= e($selectedCourse['course_code']) ?> (<?= e($currentDate) ?>)</h5>
    </div>
    <form action="/attendance/record" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="course_id" value="<?= (int)$selectedCourse['id'] ?>">
        <input type="hidden" name="date" value="<?= e($currentDate) ?>">

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student Name</th>
                        <th>Student Code</th>
                        <th>Attendance Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($roster)): ?>
                        <?php foreach ($roster as $r): ?>
                            <tr>
                                <td class="fw-bold"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                <td><span class="badge bg-secondary font-monospace"><?= e($r['student_code']) ?></span></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <input type="radio" class="btn-check" name="attendance[<?= (int)$r['student_id'] ?>]" id="pres_<?= (int)$r['student_id'] ?>" value="present" <?= ($r['status'] ?? 'present') === 'present' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-success btn-sm" for="pres_<?= (int)$r['student_id'] ?>">Present</label>

                                        <input type="radio" class="btn-check" name="attendance[<?= (int)$r['student_id'] ?>]" id="late_<?= (int)$r['student_id'] ?>" value="late" <?= ($r['status'] ?? '') === 'late' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-warning btn-sm" for="late_<?= (int)$r['student_id'] ?>">Late</label>

                                        <input type="radio" class="btn-check" name="attendance[<?= (int)$r['student_id'] ?>]" id="abs_<?= (int)$r['student_id'] ?>" value="absent" <?= ($r['status'] ?? '') === 'absent' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-danger btn-sm" for="abs_<?= (int)$r['student_id'] ?>">Absent</label>

                                        <input type="radio" class="btn-check" name="attendance[<?= (int)$r['student_id'] ?>]" id="exc_<?= (int)$r['student_id'] ?>" value="excused" <?= ($r['status'] ?? '') === 'excused' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-secondary btn-sm" for="exc_<?= (int)$r['student_id'] ?>">Excused</label>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center text-muted py-5">No students enrolled in this course.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($roster)): ?>
        <div class="card-footer bg-white text-end py-3">
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i> Submit Roll-Call</button>
        </div>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>
