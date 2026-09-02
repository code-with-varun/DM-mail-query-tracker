<div class="container-fluid px-4 py-4">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="user-avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                    <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
                </div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['full_name']) ?></h5>
                <p class="badge bg-primary mb-2"><?= htmlspecialchars($user['role_name']) ?></p>
                <div class="text-muted fs-7">Code: <?= htmlspecialchars($user['user_code']) ?></div>
                <div class="text-muted fs-7">Dept: <?= htmlspecialchars($user['department']) ?></div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-id-card me-2 text-primary"></i>User Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-8 fw-bold">Full Name</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['full_name']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-8 fw-bold">Email Address</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-8 fw-bold">Mobile Number</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['mobile'] ?? 'N/A') ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fs-8 fw-bold">Reporting Manager</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['manager_name'] ?? 'None / Super Admin') ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" id="password">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-lock me-2 text-primary"></i>Change Password</h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('change-password') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-bold">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-bold">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-2"></i>Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
