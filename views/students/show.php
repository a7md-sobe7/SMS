<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Academic Profile</h3>
        <p class="text-muted mb-0">Complete student record, enrolled courses, and grade evaluation</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/students" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Student Directory</a>
        <?php if (has_role(['admin', 'registrar'])): ?>
        <a href="/students/<?= (int)$student['id'] ?>/edit" class="btn btn-primary"><i class="bi bi-pencil me-1"></i> Edit Profile</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Student Demographic Profile -->
    <div class="col-lg-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="avatar bg-primary text-white fw-bold rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow" style="width: 72px; height: 72px; font-size: 1.75rem;">
                    <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
                </div>
                <h4 class="fw-bold mb-1"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h4>
                <div class="badge bg-secondary font-monospace mb-3"><?= e($student['student_code']) ?></div>
                
                <div class="border-top pt-3 text-start">
                    <div class="mb-2">
                        <span class="text-muted small d-block">Department</span>
                        <span class="fw-semibold"><?= e($student['department_name']) ?></span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small d-block">Academic Level</span>
                        <span class="fw-semibold text-capitalize"><?= e($student['academic_level']) ?> (Class of <?= e($student['enrollment_year']) ?>)</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small d-block">Official Email</span>
                        <span class="fw-semibold"><?= e($student['email']) ?></span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small d-block">Phone</span>
                        <span class="fw-semibold"><?= e($student['phone'] ?: 'N/A') ?></span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small d-block">Date of Birth & Gender</span>
                        <span class="fw-semibold"><?= e($student['date_of_birth']) ?> (<?= ucfirst(e($student['gender'])) ?>)</span>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Status</span>
                        <span class="badge bg-success text-capitalize"><?= e($student['status']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic Transcript & Enrolled Courses -->
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0">Course Enrollment & Transcript</h5>
                <div class="badge bg-primary-subtle text-primary p-2">
                    Total Credits Earned: <strong><?= (int)($gpa['total_credits_earned'] ?? 0) ?></strong>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Course</th>
                            <th>Term</th>
                            <th>Credits</th>
                            <th>Instructor</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($enrollments)): ?>
                            <?php foreach ($enrollments as $enr): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= e($enr['course_code']) ?></div>
                                        <small class="text-muted"><?= e($enr['course_name']) ?></small>
                                    </td>
                                    <td><?= e($enr['semester']) ?> <?= e($enr['academic_year']) ?></td>
                                    <td><?= (int)$enr['credit_hours'] ?></td>
                                    <td><?= e($enr['instructor_name'] ?? 'Unassigned') ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= e($enr['status']) ?></span></td>
                                    <td>
                                        <?php if ($enr['total_grade'] !== null): ?>
                                            <strong><?= number_format((float)$enr['total_grade'], 1) ?>%</strong>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($enr['letter_grade']): ?>
                                            <span class="badge bg-primary"><?= e($enr['letter_grade']) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No enrolled courses on record.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
