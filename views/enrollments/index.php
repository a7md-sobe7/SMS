<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Course Enrollment & Roster Management</h3>
        <p class="text-muted mb-0">Register students into courses and review active class rosters</p>
    </div>
</div>

<div class="row g-4">
    <!-- Quick Enrollment Form -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title fw-bold mb-0">Enroll Student</h5>
            </div>
            <div class="card-body">
                <form action="/enrollments" method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Course</label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Choose Course...</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= ($courseId ?? null) == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['course_code']) ?>: <?= e($c['course_name']) ?> (<?= (int)$c['enrolled_count'] ?>/<?= (int)$c['capacity'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Select Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Choose Student...</option>
                            <?php foreach ($students as $stu): ?>
                                <option value="<?= (int)$stu['id'] ?>">
                                    <?= e($stu['student_code']) ?> - <?= e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle me-1"></i> Submit Enrollment</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Course Roster View -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0">
                    <?= $selectedCourse ? e($selectedCourse['course_code'] . ' - ' . $selectedCourse['course_name']) : 'Select a course to view roster' ?>
                </h5>
            </div>
            <div class="card-body border-bottom p-3">
                <form method="GET" action="/enrollments" class="d-flex gap-2">
                    <select name="course_id" class="form-select">
                        <option value="">-- Switch Course Roster --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ($courseId ?? null) == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['course_code']) ?> - <?= e($c['course_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-outline-secondary px-4">Load</button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Code</th>
                            <th>Enrolled On</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($roster)): ?>
                            <?php foreach ($roster as $r): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
                                    <td><span class="badge bg-secondary font-monospace"><?= e($r['student_code']) ?></span></td>
                                    <td><?= e($r['enrollment_date']) ?></td>
                                    <td><span class="badge bg-success"><?= e($r['enrollment_status']) ?></span></td>
                                    <td class="text-end">
                                        <form action="/enrollments/<?= (int)$r['enrollment_id'] ?>/drop" method="POST" onsubmit="return confirm('Drop this student from course?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Drop</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-5">No students enrolled or no course selected.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
