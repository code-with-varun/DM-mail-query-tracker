<div class="container-fluid px-4 py-4">
    <!-- Header Page Title & Action -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Change Password</h4>
            <p class="text-muted fs-7 mb-0">Update your security credentials to keep your account safe.</p>
        </div>
        <a href="<?= base_url('profile') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Back to Profile
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fas fa-key"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Account Password Reset</h6>
                            <small class="text-muted fs-8">Logged in as <?= htmlspecialchars($user['full_name'] ?? '') ?> (<?= htmlspecialchars($user['email'] ?? '') ?>)</small>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form action="<?= base_url('change-password') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                        
                        <div class="mb-4">
                            <label class="form-label fs-7 fw-bold text-dark">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="current_password" class="form-control" placeholder="Enter existing password" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold text-dark">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-shield-alt text-muted"></i></span>
                                <input type="password" name="new_password" class="form-control" placeholder="Enter new strong password" required minlength="6">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fs-7 fw-bold text-dark">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-check-double text-muted"></i></span>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter new password" required minlength="6">
                            </div>
                        </div>

                        <div class="alert alert-light border fs-8 text-muted mb-4">
                            <i class="fas fa-info-circle me-1 text-primary"></i> Password should be at least 6 characters long. Make sure to use a combination of upper/lowercase letters, numbers, and special characters.
                        </div>

                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="<?= base_url('profile') ?>" class="btn btn-light fw-bold text-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
