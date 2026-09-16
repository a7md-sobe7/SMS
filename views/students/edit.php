<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Edit Student: <?= e($student['first_name'] . ' ' . $student['last_name']) ?></h3>
        <p class="text-muted mb-0">Update personal, demographic, or academic details</p>
    </div>
    <a href="/students/<?= (int)$student['id'] ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back to Profile</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-4">
        <form action="/students/<?= (int)$student['id'] ?>" method="POST">
            <?= csrf_field() ?>

            <h5 class="fw-bold text-primary mb-3 border-bottom pb-2">1. Identity & Credentials</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Student Code <span class="text-danger">*</span></label>
                    <input type="text" name="student_code" class="form-control" value="<?= e(old('student_code', $student['student_code'])) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" class="form-control" value="<?= e(old('first_name', $student['first_name'])) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" class="form-control" value="<?= e(old('last_name', $student['last_name'])) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= e(old('email', $student['email'])) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= e(old('phone', $student['phone'])) ?>">
                </div>
            </div>

            <h5 class="fw-bold text-primary mb-3 border-bottom pb-2">2. Demographics</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_birth" class="form-control" value="<?= e(old('date_of_birth', $student['date_of_birth'])) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="male" <?= old('gender', $student['gender']) === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= old('gender', $student['gender']) === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= old('gender', $student['gender']) === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">Address</label>
                    <input type="text" name="address" class="form-control" value="<?= e(old('address', $student['address'])) ?>">
                </div>
            </div>

            <h5 class="fw-bold text-primary mb-3 border-bottom pb-2">3. Academic Placement</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Department <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-select" required>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= (int)$dept['id'] ?>" <?= old('department_id', $student['department_id']) == $dept['id'] ? 'selected' : '' ?>>
                                <?= e($dept['name']) ?> (<?= e($dept['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Enrollment Year <span class="text-danger">*</span></label>
                    <input type="number" name="enrollment_year" class="form-control" value="<?= e(old('enrollment_year', $student['enrollment_year'])) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Academic Level <span class="text-danger">*</span></label>
                    <select name="academic_level" class="form-select" required>
                        <option value="freshman" <?= old('academic_level', $student['academic_level']) === 'freshman' ? 'selected' : '' ?>>Freshman</option>
                        <option value="sophomore" <?= old('academic_level', $student['academic_level']) === 'sophomore' ? 'selected' : '' ?>>Sophomore</option>
                        <option value="junior" <?= old('academic_level', $student['academic_level']) === 'junior' ? 'selected' : '' ?>>Junior</option>
                        <option value="senior" <?= old('academic_level', $student['academic_level']) === 'senior' ? 'selected' : '' ?>>Senior</option>
                        <option value="graduate" <?= old('academic_level', $student['academic_level']) === 'graduate' ? 'selected' : '' ?>>Graduate</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active" <?= old('status', $student['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= old('status', $student['status']) === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        <option value="graduated" <?= old('status', $student['status']) === 'graduated' ? 'selected' : '' ?>>Graduated</option>
                        <option value="withdrawn" <?= old('status', $student['status']) === 'withdrawn' ? 'selected' : '' ?>>Withdrawn</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="/students/<?= (int)$student['id'] ?>" class="btn btn-light border px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i> Update Student</button>
            </div>
        </form>
    </div>
</div>
