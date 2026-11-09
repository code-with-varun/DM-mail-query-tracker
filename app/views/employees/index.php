<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Employee & User Management</h4>
            <p class="text-muted fs-7 mb-0">System User Accounts, Manager Hierarchy & Skill Matrix Mapping</p>
        </div>
        <?php if (is_super_admin() || is_admin()): ?>
        <div class="d-flex gap-2">
            <a href="<?= base_url('employees/skill-matrix') ?>" class="btn btn-outline-success btn-sm fw-bold">
                <i class="fas fa-sliders-h me-1"></i>Skill Matrix Hub
            </a>
            <a href="<?= base_url('employees/export') ?>" class="btn btn-outline-success btn-sm fw-bold">
                <i class="fas fa-file-excel me-1"></i>Export CSV
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#importEmployeesModal">
                <i class="fas fa-file-upload me-1"></i>Import Employees CSV
            </button>
            <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#userModal">
                <i class="fas fa-user-plus me-1"></i>Create User Account
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Main Employees Register Table -->
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
                            <th>Designation</th>
                            <th>Department</th>
                            <th>Reporting Manager</th>
                            <th>Aligned Activities</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <?php if (is_super_admin() || is_admin()): ?>
                            <th>Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <?php if ($u['id'] == Session::get('user_id')) continue; ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($u['user_code']) ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php
                                    $roleBadge = 'bg-secondary';
                                    if ($u['role_id'] == 1) $roleBadge = 'bg-danger'; // Super Admin
                                    elseif ($u['role_id'] == 2) $roleBadge = 'bg-primary'; // Admin (TL)
                                    elseif ($u['role_id'] == 3) $roleBadge = 'bg-success text-white'; // Manager
                                    elseif ($u['role_id'] == 4) $roleBadge = 'bg-dark text-white'; // Employee
                                    elseif ($u['role_id'] == 5) $roleBadge = 'bg-info text-dark'; // Senior Management
                                ?>
                                <span class="badge <?= $roleBadge ?>">
                                    <?= htmlspecialchars($u['role_name']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">
                                    <?= htmlspecialchars($u['designation'] ?: '—') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($u['department']) ?></td>
                            <td class="fs-8"><?= htmlspecialchars($u['manager_name'] ?? 'None / Super Admin') ?></td>
                            <td>
                                <?php $assignedSkills = $userFullSkillsMap[$u['id']] ?? []; ?>
                                <?php if (!empty($assignedSkills)): ?>
                                    <button type="button" class="btn btn-sm btn-outline-info fw-bold p-1 px-2" data-bs-toggle="modal" data-bs-target="#viewSkillsModal<?= $u['id'] ?>" title="Click to view aligned activities">
                                        <i class="fas fa-tasks me-1"></i><?= count($assignedSkills) ?> Sub-Activities
                                    </button>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">None Mapped</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-<?= $u['status'] === 'Active' ? 'success' : 'secondary' ?>"><?= $u['status'] ?></span></td>
                            <td class="fs-8 text-muted"><?= $u['last_login'] ? format_datetime($u['last_login']) : 'Never' ?></td>
                            <?php if (is_super_admin() || is_admin()): ?>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary p-1 px-2" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $u['id'] ?>" title="Edit User Account Details">
                                        <i class="fas fa-user-edit"></i>
                                    </button>
                                    <a href="<?= base_url('employees/skill-matrix/' . $u['id']) ?>" class="btn btn-outline-success p-1 px-2" title="Manage Skill Matrix Alignment">
                                        <i class="fas fa-sliders-h"></i>
                                    </a>
                                    <a href="<?= base_url('training/plan?user_id=' . $u['id']) ?>" class="btn btn-outline-info p-1 px-2" title="Create Training Plan & KT for <?= htmlspecialchars($u['full_name']) ?>">
                                        <i class="fas fa-graduation-cap"></i>
                                    </a>
                                    <?php if (is_super_admin() && $u['role_id'] != 1): ?>
                                    <form action="<?= base_url('employees/create') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete account <?= htmlspecialchars($u['user_code']) ?>?');">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-outline-danger p-1 px-2" title="Delete User">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODALS SECTION (Rendered cleanly outside table element to prevent DOM issues) -->
<!-- ========================================================================= -->

<?php foreach ($users as $u): ?>

<!-- 1. Modal: View Aligned Activities -->
<div class="modal fade" id="viewSkillsModal<?= $u['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-tasks me-2"></i>Aligned Activities — <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['user_code']) ?>)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <?php $userSkills = $userFullSkillsMap[$u['id']] ?? []; ?>
                <?php if (empty($userSkills)): ?>
                    <div class="text-center py-4 text-muted fs-7">
                        <i class="fas fa-info-circle fs-4 d-block mb-2"></i>
                        No sub-activities currently assigned to this employee.
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold text-dark fs-7">Total Assigned Sub-Activities: <span class="badge bg-primary"><?= count($userSkills) ?></span></span>
                        <input type="text" class="form-control form-control-sm w-50" id="filterUserSkills_<?= $u['id'] ?>" placeholder="Search sub-activities..." onkeyup="filterUserSkillTable(<?= $u['id'] ?>)">
                    </div>
                    <div class="table-responsive border rounded" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 fs-8" id="userSkillsTable_<?= $u['id'] ?>">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Sub-Activity Name</th>
                                    <th>Parent Activity</th>
                                    <th>Assigned Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userSkills as $skIdx => $sk): ?>
                                <tr>
                                    <td class="text-muted"><?= $skIdx + 1 ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($sk['sub_activity_name']) ?></td>
                                    <td class="text-muted"><?= htmlspecialchars($sk['activity_name']) ?></td>
                                    <td>
                                        <?php
                                            $rBadge = 'bg-primary';
                                            if ($sk['role_type'] === 'Checker') $rBadge = 'bg-warning text-dark';
                                            elseif ($sk['role_type'] === 'Both') $rBadge = 'bg-success';
                                        ?>
                                        <span class="badge <?= $rBadge ?> fw-bold"><?= htmlspecialchars($sk['role_type']) ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Modal: Lightweight Edit User Account Details -->
<?php if (is_super_admin() || is_admin()): ?>
<div class="modal fade" id="editUserModal<?= $u['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-md">
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
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">System Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                <?php if (is_super_admin()): ?>
                                    <option value="1" <?= $u['role_id'] == 1 ? 'selected' : '' ?>>Super Admin</option>
                                    <option value="2" <?= $u['role_id'] == 2 ? 'selected' : '' ?>>Admin (TL)</option>
                                    <option value="3" <?= $u['role_id'] == 3 ? 'selected' : '' ?>>Manager</option>
                                    <option value="4" <?= $u['role_id'] == 4 ? 'selected' : '' ?>>Employee</option>
                                    <option value="5" <?= $u['role_id'] == 5 ? 'selected' : '' ?>>Senior Management</option>
                                <?php elseif (is_senior_management()): ?>
                                    <option value="3" <?= $u['role_id'] == 3 ? 'selected' : '' ?>>Manager</option>
                                    <option value="2" <?= $u['role_id'] == 2 ? 'selected' : '' ?>>Admin (TL)</option>
                                    <option value="4" <?= $u['role_id'] == 4 ? 'selected' : '' ?>>Employee</option>
                                <?php elseif (is_manager()): ?>
                                    <option value="2" <?= $u['role_id'] == 2 ? 'selected' : '' ?>>Admin (TL)</option>
                                    <option value="4" <?= $u['role_id'] == 4 ? 'selected' : '' ?>>Employee</option>
                                <?php else: ?>
                                    <option value="4" <?= $u['role_id'] == 4 ? 'selected' : '' ?>>Employee</option>
                                <?php endif; ?>
                            </select>
                            <div class="form-text fs-9 text-muted">System access permissions & workflows</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Designation</label>
                            <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($u['designation'] ?? '') ?>" placeholder="e.g. Operations Manager, Process Associate">
                            <div class="form-text fs-9 text-muted">Corporate / Business job title</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Department</label>
                            <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($u['department']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Reporting Manager</label>
                            <select name="manager_id" class="form-select">
                                <option value="">Select Manager</option>
                                <?php foreach ($admins as $adm): ?>
                                    <option value="<?= $adm['id'] ?>" <?= $u['manager_id'] == $adm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($adm['full_name']) ?> (<?= htmlspecialchars($adm['role_name']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="Active" <?= $u['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $u['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Reset Password (Optional)</label>
                            <input type="text" name="password" class="form-control" placeholder="Leave blank to keep current password">
                        </div>
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

<!-- 4. Modal: Create New User Account -->
<?php if (is_super_admin() || is_admin()): ?>
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-md">
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
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">System Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                <?php if (is_super_admin()): ?>
                                    <option value="1">Super Admin</option>
                                    <option value="2">Admin (TL)</option>
                                    <option value="3">Manager</option>
                                    <option value="4" selected>Employee</option>
                                    <option value="5">Senior Management</option>
                                <?php elseif (is_senior_management()): ?>
                                    <option value="3">Manager</option>
                                    <option value="2">Admin (TL)</option>
                                    <option value="4" selected>Employee</option>
                                <?php elseif (is_manager()): ?>
                                    <option value="2">Admin (TL)</option>
                                    <option value="4" selected>Employee</option>
                                <?php else: ?>
                                    <option value="4" selected>Employee</option>
                                <?php endif; ?>
                            </select>
                            <div class="form-text fs-9 text-muted">System access permissions & workflows</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Designation</label>
                            <input type="text" name="designation" class="form-control" placeholder="e.g. Process Associate, Team Leader">
                            <div class="form-text fs-9 text-muted">Corporate / Business job title</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Department</label>
                            <input type="text" name="department" class="form-control" value="Operations">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Reporting Manager</label>
                            <select name="manager_id" class="form-select">
                                <option value="">Select Manager</option>
                                <?php foreach ($admins as $adm): ?>
                                    <option value="<?= $adm['id'] ?>"><?= htmlspecialchars($adm['full_name']) ?> (<?= htmlspecialchars($adm['role_name']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Initial Password</label>
                            <input type="text" name="password" class="form-control" value="ChangeMe@123" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="Active" selected>Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
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

<!-- Javascript Helper Functions for Skill Matrix Interactivity -->
<script>
function filterUserSkillTable(userId) {
    var input = document.getElementById("filterUserSkills_" + userId);
    var filter = input.value.toLowerCase();
    var table = document.getElementById("userSkillsTable_" + userId);
    if (!table) return;
    var tr = table.getElementsByTagName("tr");
    for (var i = 1; i < tr.length; i++) {
        var txt = tr[i].textContent || tr[i].innerText;
        tr[i].style.display = (txt.toLowerCase().indexOf(filter) > -1) ? "" : "none";
    }
}

function filterMatrixAccordions(userId) {
    var query = document.getElementById("matrixSearch_" + userId).value.toLowerCase();
    var container = document.getElementById("matrixContainer_" + userId);
    if (!container) return;

    var divCards = container.querySelectorAll(".division-group-card");
    divCards.forEach(function(divCard) {
        var divName = divCard.getAttribute("data-div-name") || "";
        var actCards = divCard.querySelectorAll(".activity-item-card");
        var anyActVisible = false;

        actCards.forEach(function(actCard) {
            var actName = actCard.getAttribute("data-act-name") || "";
            var rows = actCard.querySelectorAll(".subact-row");
            var anyRowVisible = false;

            rows.forEach(function(row) {
                var searchText = row.getAttribute("data-search-text") || "";
                if (query === "" || searchText.includes(query) || actName.includes(query) || divName.includes(query)) {
                    row.style.display = "";
                    anyRowVisible = true;
                } else {
                    row.style.display = "none";
                }
            });

            if (anyRowVisible) {
                actCard.style.display = "";
                anyActVisible = true;
                // If query is typed, auto-expand matching accordion
                if (query !== "") {
                    var collapseEl = actCard.querySelector(".accordion-collapse");
                    if (collapseEl && !collapseEl.classList.contains("show")) {
                        var bsCollapse = new bootstrap.Collapse(collapseEl, { toggle: true });
                    }
                }
            } else {
                actCard.style.display = "none";
            }
        });

        divCard.style.display = anyActVisible ? "" : "none";
    });
}

function onMatrixRadioChange(userId, saId) {
    var chk = document.getElementById("chk_" + userId + "_" + saId);
    if (chk) {
        chk.checked = true;
    }
}

function onMatrixCheckboxChange(userId, saId) {
    var chk = document.getElementById("chk_" + userId + "_" + saId);
    if (chk && !chk.checked) {
        // Option to uncheck role selection visually if needed
    }
}

function setAllRolesInUser(userId, roleType) {
    var container = document.getElementById("matrixContainer_" + userId);
    if (!container) return;

    var chks = container.querySelectorAll(".matrix-chk");
    chks.forEach(function(chk) {
        chk.checked = true;
    });

    var radioValue = roleType;
    var radios = container.querySelectorAll(".matrix-radio");
    radios.forEach(function(r) {
        if (r.value === radioValue) {
            r.checked = true;
        }
    });
}

function clearAllRolesInUser(userId) {
    var container = document.getElementById("matrixContainer_" + userId);
    if (!container) return;

    var chks = container.querySelectorAll(".matrix-chk");
    chks.forEach(function(chk) {
        chk.checked = false;
    });
}

function setGroupRoles(userId, collapseId, roleType) {
    var collapse = document.getElementById(collapseId);
    if (!collapse) return;

    var chks = collapse.querySelectorAll(".matrix-chk");
    chks.forEach(function(chk) {
        chk.checked = true;
    });

    var radios = collapse.querySelectorAll(".matrix-radio");
    radios.forEach(function(r) {
        if (r.value === roleType) {
            r.checked = true;
        }
    });
}

function clearGroupRoles(userId, collapseId) {
    var collapse = document.getElementById(collapseId);
    if (!collapse) return;

    var chks = collapse.querySelectorAll(".matrix-chk");
    chks.forEach(function(chk) {
        chk.checked = false;
    });
}
</script>

<!-- Modal: Bulk Import Employees -->
<div class="modal fade" id="importEmployeesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= base_url('employees/import') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-upload me-2"></i>Bulk Import Employees CSV</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-2xs mb-3 fs-8">
                        <i class="fas fa-info-circle me-1"></i> Upload a CSV file to bulk import user/employee accounts. Existing employee codes or email addresses will be updated.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="import_file" class="form-control" accept=".csv" required>
                    </div>

                    <div class="border p-3 rounded bg-light fs-8">
                        <div class="fw-bold text-dark mb-1"><i class="fas fa-table me-1 text-primary"></i>Expected CSV Headers:</div>
                        <code>Employee Code, Full Name, Email Address, Mobile, Designation, Role, Department, Password, Status</code>
                        <div class="mt-2 text-muted fs-9">
                            Sample: <code>EMP010, Ramesh Rao, ramesh@company.com, 9876543210, Process Associate, Employee, Operations, ChangeMe@123, Active</code>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 d-flex justify-content-between">
                    <a href="<?= base_url('employees/download_template') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                        <i class="fas fa-download me-1"></i>Download CSV Template
                    </a>
                    <div>
                        <button type="button" class="btn btn-light border fw-bold me-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-upload me-1"></i>Upload & Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
