<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Employee & User Management</h4>
            <p class="text-muted fs-7 mb-0">System User Accounts, Manager Hierarchy & Skill Matrix Mapping</p>
        </div>
        <?php if (is_super_admin() || is_admin()): ?>
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#userModal">
            <i class="fas fa-user-plus me-1"></i>Create User Account
        </button>
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
                                    <button type="button" class="btn btn-outline-success p-1 px-2" data-bs-toggle="modal" data-bs-target="#manageSkillsModal<?= $u['id'] ?>" title="Manage Skill Matrix & Aligned Sub-Activities">
                                        <i class="fas fa-sliders-h me-1"></i>Matrix
                                    </button>
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
                            <label class="form-label fs-7 fw-bold">Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                <?php if (is_super_admin()): ?>
                                    <option value="1" <?= $u['role_id'] == 1 ? 'selected' : '' ?>>Super Admin</option>
                                    <option value="2" <?= $u['role_id'] == 2 ? 'selected' : '' ?>>Admin (Manager)</option>
                                <?php endif; ?>
                                <option value="3" <?= $u['role_id'] == 3 ? 'selected' : '' ?>>Employee</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Department</label>
                            <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($u['department']) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Reporting Manager</label>
                            <select name="manager_id" class="form-select">
                                <option value="">Select Manager</option>
                                <?php foreach ($admins as $adm): ?>
                                    <option value="<?= $adm['id'] ?>" <?= $u['manager_id'] == $adm['id'] ? 'selected' : '' ?>><?= htmlspecialchars($adm['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="Active" <?= $u['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="Inactive" <?= $u['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label fs-7 fw-bold">Reset Password (Optional)</label>
                        <input type="text" name="password" class="form-control" placeholder="Leave blank to keep current password">
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

<!-- 3. Modal: Dedicated Skill Matrix Alignment (Grouped Accordions) -->
<div class="modal fade" id="manageSkillsModal<?= $u['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('employees/create') ?>" method="POST" id="skillMatrixForm_<?= $u['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <input type="hidden" name="action" value="update_skills">

                <div class="modal-header bg-dark text-white py-3">
                    <div>
                        <h5 class="modal-title fw-bold mb-0">
                            <i class="fas fa-sliders-h text-success me-2"></i>Skill Matrix Alignment
                        </h5>
                        <p class="text-white-50 fs-8 mb-0"><?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['user_code']) ?>) — <?= htmlspecialchars($u['department']) ?></p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- Top Quick Action Bar -->
                <div class="modal-body p-0">
                    <div class="bg-light border-bottom p-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" id="matrixSearch_<?= $u['id'] ?>" class="form-control" placeholder="Search division, activity, or sub-activity..." onkeyup="filterMatrixAccordions(<?= $u['id'] ?>)">
                                </div>
                            </div>
                            <div class="col-md-7 text-md-end">
                                <span class="fs-8 text-muted me-2">Quick Assign:</span>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary fw-bold" onclick="setAllRolesInUser(<?= $u['id'] ?>, 'Maker')"><i class="fas fa-check me-1"></i>All Maker</button>
                                    <button type="button" class="btn btn-outline-warning text-dark fw-bold" onclick="setAllRolesInUser(<?= $u['id'] ?>, 'Checker')"><i class="fas fa-user-check me-1"></i>All Checker</button>
                                    <button type="button" class="btn btn-outline-success fw-bold" onclick="setAllRolesInUser(<?= $u['id'] ?>, 'Both')"><i class="fas fa-sync me-1"></i>All Both</button>
                                    <button type="button" class="btn btn-outline-secondary fw-bold" onclick="clearAllRolesInUser(<?= $u['id'] ?>)"><i class="fas fa-times me-1"></i>Clear All</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion Content Container -->
                    <div class="p-4" style="max-height: 60vh; overflow-y: auto;" id="matrixContainer_<?= $u['id'] ?>">
                        <?php if (empty($groupedSubActivities)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-exclamation-triangle fs-3 d-block mb-2 text-warning"></i>
                                No sub-activities found in the system. Please add activities first under Masters menu.
                            </div>
                        <?php else: ?>
                            <?php $divIndex = 0; foreach ($groupedSubActivities as $divisionName => $activitiesGroup): $divIndex++; ?>
                            
                            <!-- Division Header Card -->
                            <div class="card mb-3 border-0 shadow-sm division-group-card" data-div-name="<?= strtolower(htmlspecialchars($divisionName)) ?>">
                                <div class="card-header bg-secondary bg-opacity-10 py-2 d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark fs-7">
                                        <i class="fas fa-building text-secondary me-2"></i>DIVISION: <?= htmlspecialchars($divisionName) ?>
                                    </span>
                                    <span class="badge bg-secondary rounded-pill fs-8"><?= count($activitiesGroup) ?> Activities</span>
                                </div>
                                <div class="card-body p-3">
                                    
                                    <!-- Activities Accordion inside Division -->
                                    <div class="accordion" id="accordionDiv_<?= $u['id'] ?>_<?= $divIndex ?>">
                                        <?php $actIndex = 0; foreach ($activitiesGroup as $activityName => $subActsList): $actIndex++; ?>
                                            <?php
                                                // Count assigned sub-activities for this group
                                                $assignedInGroup = 0;
                                                foreach ($subActsList as $sCheck) {
                                                    if (!empty($userSkillsMap[$u['id']][$sCheck['id']])) $assignedInGroup++;
                                                }
                                                $actCollapseId = "collapse_act_" . $u['id'] . "_" . $divIndex . "_" . $actIndex;
                                            ?>
                                            <div class="accordion-item border mb-2 rounded activity-item-card" data-act-name="<?= strtolower(htmlspecialchars($activityName)) ?>">
                                                <h2 class="accordion-header d-flex justify-content-between align-middle bg-light">
                                                    <button class="accordion-button collapsed py-2 px-3 fw-semibold text-dark fs-7" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $actCollapseId ?>">
                                                        <i class="fas fa-folder-open text-primary me-2"></i><?= htmlspecialchars($activityName) ?>
                                                        <span class="badge bg-<?= $assignedInGroup > 0 ? 'success' : 'light text-muted border' ?> ms-3 fs-8 act-assigned-count">
                                                            <?= $assignedInGroup ?> / <?= count($subActsList) ?> Assigned
                                                        </span>
                                                    </button>
                                                </h2>
                                                <div id="<?= $actCollapseId ?>" class="accordion-collapse collapse" data-bs-parent="#accordionDiv_<?= $u['id'] ?>_<?= $divIndex ?>">
                                                    <div class="accordion-body p-3 bg-white">
                                                        
                                                        <!-- Group Action Buttons -->
                                                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                                            <span class="fs-8 text-muted">Sub-activities under <strong><?= htmlspecialchars($activityName) ?></strong>:</span>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <button type="button" class="btn btn-xs btn-outline-primary px-2 fs-8" onclick="setGroupRoles(<?= $u['id'] ?>, '<?= $actCollapseId ?>', 'Maker')">Select All Maker</button>
                                                                <button type="button" class="btn btn-xs btn-outline-warning text-dark px-2 fs-8" onclick="setGroupRoles(<?= $u['id'] ?>, '<?= $actCollapseId ?>', 'Checker')">Select All Checker</button>
                                                                <button type="button" class="btn btn-xs btn-outline-secondary px-2 fs-8" onclick="clearGroupRoles(<?= $u['id'] ?>, '<?= $actCollapseId ?>')">Deselect Group</button>
                                                            </div>
                                                        </div>

                                                        <!-- Sub-Activities Table -->
                                                        <table class="table table-sm align-middle table-hover mb-0 fs-8">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th style="width: 40px;" class="text-center">Assign</th>
                                                                    <th>Sub-Activity Name</th>
                                                                    <th style="width: 120px;">TAT / Day</th>
                                                                    <th style="width: 200px;">Assigned Role</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($subActsList as $sa): 
                                                                    $userSkillRole = $userSkillsMap[$u['id']][$sa['id']] ?? null;
                                                                    $isAssigned = !empty($userSkillRole);
                                                                    $saSearchText = strtolower(htmlspecialchars($sa['sub_activity_name']));
                                                                ?>
                                                                <tr class="subact-row" data-search-text="<?= $saSearchText ?>">
                                                                    <td class="text-center">
                                                                        <input type="checkbox" name="skills[<?= $sa['id'] ?>][selected]" id="chk_<?= $u['id'] ?>_<?= $sa['id'] ?>" value="1" class="form-check-input matrix-chk" <?= $isAssigned ? 'checked' : '' ?> onchange="onMatrixCheckboxChange(<?= $u['id'] ?>, <?= $sa['id'] ?>)">
                                                                    </td>
                                                                    <td class="fw-bold text-dark">
                                                                        <label for="chk_<?= $u['id'] ?>_<?= $sa['id'] ?>" class="form-check-label cursor-pointer mb-0">
                                                                            <?= htmlspecialchars($sa['sub_activity_name']) ?>
                                                                        </label>
                                                                    </td>
                                                                    <td class="text-muted">
                                                                        <span class="badge bg-light text-dark border me-1"><i class="far fa-clock me-1"></i><?= (int)($sa['default_tat_hours'] ?? 24) ?>h</span>
                                                                        <?php if (!empty($sa['default_occurrence_day'])): ?>
                                                                            <span class="badge bg-light text-primary border" title="Monthly Schedule Day">Day <?= $sa['default_occurrence_day'] ?></span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <div class="btn-group btn-group-sm" role="group">
                                                                            <input type="radio" class="btn-check matrix-radio" name="skills[<?= $sa['id'] ?>][role_type]" id="role_m_<?= $u['id'] ?>_<?= $sa['id'] ?>" value="Maker" <?= ($userSkillRole === 'Maker' || !$userSkillRole) ? 'checked' : '' ?> onchange="onMatrixRadioChange(<?= $u['id'] ?>, <?= $sa['id'] ?>)">
                                                                            <label class="btn btn-outline-primary py-0 px-2 fs-8" for="role_m_<?= $u['id'] ?>_<?= $sa['id'] ?>">Maker</label>

                                                                            <input type="radio" class="btn-check matrix-radio" name="skills[<?= $sa['id'] ?>][role_type]" id="role_c_<?= $u['id'] ?>_<?= $sa['id'] ?>" value="Checker" <?= $userSkillRole === 'Checker' ? 'checked' : '' ?> onchange="onMatrixRadioChange(<?= $u['id'] ?>, <?= $sa['id'] ?>)">
                                                                            <label class="btn btn-outline-warning py-0 px-2 fs-8 text-dark" for="role_c_<?= $u['id'] ?>_<?= $sa['id'] ?>">Checker</label>

                                                                            <input type="radio" class="btn-check matrix-radio" name="skills[<?= $sa['id'] ?>][role_type]" id="role_b_<?= $u['id'] ?>_<?= $sa['id'] ?>" value="Both" <?= $userSkillRole === 'Both' ? 'checked' : '' ?> onchange="onMatrixRadioChange(<?= $u['id'] ?>, <?= $sa['id'] ?>)">
                                                                            <label class="btn btn-outline-success py-0 px-2 fs-8" for="role_b_<?= $u['id'] ?>_<?= $sa['id'] ?>">Both</label>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>

                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                </div>
                            </div>

                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-light border fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        <i class="fas fa-save me-1"></i>Save Skill Matrix
                    </button>
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
                            <label class="form-label fs-7 fw-bold">Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" required>
                                <?php if (is_super_admin()): ?>
                                    <option value="1">Super Admin</option>
                                    <option value="2">Admin (Manager)</option>
                                <?php endif; ?>
                                <option value="3" selected>Employee</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Department</label>
                            <input type="text" name="department" class="form-control" value="FAS">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Reporting Manager</label>
                            <select name="manager_id" class="form-select">
                                <option value="">Select Manager</option>
                                <?php foreach ($admins as $adm): ?>
                                    <option value="<?= $adm['id'] ?>"><?= htmlspecialchars($adm['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Initial Password</label>
                            <input type="text" name="password" class="form-control" value="ChangeMe@123" required>
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
