<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Employee & User Management</h4>
            <p class="text-muted fs-7 mb-0">System User Accounts, Roles & Manager Hierarchy</p>
        </div>
        <?php if (is_super_admin() || is_admin()): ?>
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#userModal">
            <i class="fas fa-user-plus me-1"></i>Create User Account
        </button>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Full Name</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Reporting Manager</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <?php if (is_super_admin() || is_admin()): ?>
                            <th>Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($u['user_code']) ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge bg-<?= $u['role_id'] == 1 ? 'danger' : ($u['role_id'] == 2 ? 'warning text-dark' : 'info text-dark') ?>">
                                    <?= htmlspecialchars($u['role_name']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($u['department']) ?></td>
                            <td class="fs-8"><?= htmlspecialchars($u['manager_name'] ?? 'None / Super Admin') ?></td>
                            <td><span class="badge bg-<?= $u['status'] === 'Active' ? 'success' : 'secondary' ?>"><?= $u['status'] ?></span></td>
                            <td class="fs-8 text-muted"><?= $u['last_login'] ? format_datetime($u['last_login']) : 'Never' ?></td>
                            <?php if (is_super_admin() || is_admin()): ?>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2 me-1" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $u['id'] ?>" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if (is_super_admin() && $u['role_id'] != 1): ?>
                                <form action="<?= base_url('employees/create') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete account <?= htmlspecialchars($u['user_code']) ?>?');">
                                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="Delete User">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>

                        <!-- Modal: Edit User -->
                        <?php if (is_super_admin() || is_admin()): ?>
                        <div class="modal fade" id="editUserModal<?= $u['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content border-0 shadow">
                                    <form action="<?= base_url('employees/create') ?>" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i>Edit User Account (<?= htmlspecialchars($u['user_code']) ?>)</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fs-7 fw-bold">Employee Code <span class="text-danger">*</span></label>
                                                    <input type="text" name="user_code" class="form-control" value="<?= htmlspecialchars($u['user_code']) ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fs-7 fw-bold">Full Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($u['full_name']) ?>" required>
                                                </div>
                                            </div>

                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fs-7 fw-bold">Email Address <span class="text-danger">*</span></label>
                                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($u['email']) ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fs-7 fw-bold">Mobile Number</label>
                                                    <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($u['mobile'] ?? '') ?>">
                                                </div>
                                            </div>

                                            <div class="row g-3 mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fs-7 fw-bold">Role <span class="text-danger">*</span></label>
                                                    <select name="role_id" class="form-select" required>
                                                        <?php if (is_super_admin()): ?>
                                                            <option value="1" <?= $u['role_id'] == 1 ? 'selected' : '' ?>>Super Admin</option>
                                                            <option value="2" <?= $u['role_id'] == 2 ? 'selected' : '' ?>>Admin (Manager)</option>
                                                        <?php endif; ?>
                                                        <option value="3" <?= $u['role_id'] == 3 ? 'selected' : '' ?>>Employee</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fs-7 fw-bold">Department</label>
                                                    <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($u['department']) ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fs-7 fw-bold">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="Active" <?= $u['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                                        <option value="Inactive" <?= $u['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fs-7 fw-bold">Reset Password (Leave blank to keep current)</label>
                                                <input type="text" name="password" class="form-control" placeholder="Enter new password to reset">
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-bold">Update Account</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: New User -->
<?php if (is_super_admin() || is_admin()): ?>
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('employees/create') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Create User Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Employee Code <span class="text-danger">*</span></label>
                            <input type="text" name="user_code" class="form-control" placeholder="e.g. EMP004" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" placeholder="Employee Full Name" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Email Address / Username <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="employee@company.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" placeholder="Phone number">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold">Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                <?php if (is_super_admin()): ?>
                                    <option value="1">Super Admin</option>
                                    <option value="2">Admin (Manager)</option>
                                <?php endif; ?>
                                <option value="3" selected>Employee</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold">Department</label>
                            <input type="text" name="department" class="form-control" value="Operations">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold">Reporting Manager</label>
                            <select name="manager_id" class="form-select">
                                <option value="">Select Manager</option>
                                <?php foreach ($admins as $adm): ?>
                                    <option value="<?= $adm['id'] ?>"><?= htmlspecialchars($adm['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Initial Password</label>
                        <input type="text" name="password" class="form-control" value="ChangeMe@123" required>
                        <small class="text-muted fs-8">Default: <code>ChangeMe@123</code></small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
