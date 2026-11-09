<style>
/* High-contrast Role Selector Tabs */
.role-nav-tab {
    transition: all 0.15s ease-in-out;
}
.role-nav-tab.role-tab-active {
    background-color: #0f172a !important;
    border-color: #0f172a !important;
    color: #ffffff !important;
}
.role-nav-tab.role-tab-active .role-nav-name {
    color: #ffffff !important;
}
.role-nav-tab.role-tab-inactive {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #1e293b !important;
}
.role-nav-tab.role-tab-inactive .role-nav-name {
    color: #1e293b !important;
}
.role-nav-tab.role-tab-inactive:hover {
    background-color: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}
.role-nav-tab.role-tab-inactive:hover .role-nav-name {
    color: #0f172a !important;
}

/* Accessible high-contrast CRUD badge labels */
.badge-perm-label {
    display: inline-flex;
    align-items: center;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.55rem;
    border-radius: 4px;
    border: 1px solid transparent;
    transition: all 0.15s ease-in-out;
    cursor: pointer;
}
.badge-perm-create {
    background-color: #ecfdf5;
    color: #065f46;
    border-color: #a7f3d0;
}
.badge-perm-edit {
    background-color: #eff6ff;
    color: #1e40af;
    border-color: #bfdbfe;
}
.badge-perm-delete {
    background-color: #fef2f2;
    color: #991b1b;
    border-color: #fecaca;
}
.permission-row.table-inactive {
    background-color: #f8fafc;
}
.permission-row.table-inactive .badge-perm-label {
    background-color: #f1f5f9;
    color: #64748b;
    border-color: #cbd5e1;
    opacity: 0.7;
}
.permission-row.table-inactive .badge-perm-label i {
    color: #94a3b8 !important;
}
</style>

<div class="container-fluid px-4 py-4">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fas fa-user-shield text-primary me-2"></i>Access Rights & Module Permissions
            </h4>
            <p class="text-muted fs-7 mb-0">
                Configure page-wise and module-wise visibility & functional access rights for system roles and individual users.
            </p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <!-- Mode Switcher -->
            <div class="btn-group shadow-sm p-1 bg-light rounded-pill border" role="group">
                <a href="<?= base_url('access-rights?mode=role&role_id=' . $selectedRoleId) ?>" class="btn btn-sm rounded-pill fw-bold px-3 <?= $mode === 'role' ? 'btn-primary' : 'btn-light text-muted' ?>">
                    <i class="fas fa-id-badge me-1"></i>Role-Wise Rights
                </a>
                <a href="<?= base_url('access-rights?mode=user&user_id=' . $selectedUserId) ?>" class="btn btn-sm rounded-pill fw-bold px-3 <?= $mode === 'user' ? 'btn-primary' : 'btn-light text-muted' ?>">
                    <i class="fas fa-user me-1"></i>User-Wise Rights
                </a>
            </div>
            <a href="<?= base_url('settings') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fas fa-cog me-1"></i>System Settings
            </a>
        </div>
    </div>

    <!-- Target Selector Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <?php if ($mode === 'role'): ?>
                <!-- Role Selector -->
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div class="flex-grow-1">
                        <label class="form-label fs-7 fw-bold text-muted text-uppercase mb-2">Select Target System Role</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($roles as $r): 
                                $rId = (int)$r['role_id'];
                                $isSelected = $rId === $selectedRoleId;
                                $badgeClass = 'bg-secondary text-white';
                                if ($rId === 1) $badgeClass = 'bg-danger text-white';
                                elseif ($rId === 2) $badgeClass = 'bg-primary text-white';
                                elseif ($rId === 3) $badgeClass = 'bg-success text-white';
                                elseif ($rId === 4) $badgeClass = 'bg-dark text-white';
                                elseif ($rId === 5) $badgeClass = 'bg-info text-white';
                            ?>
                                <a href="<?= base_url('access-rights?mode=role&role_id=' . $rId) ?>" 
                                   class="btn btn-sm fw-bold px-3 py-2 d-flex align-items-center gap-2 border shadow-2xs role-nav-tab <?= $isSelected ? 'role-tab-active' : 'role-tab-inactive' ?>">
                                    <span class="badge <?= $badgeClass ?>"><?= $rId ?></span>
                                    <span class="role-nav-name"><?= htmlspecialchars($r['role_name']) ?></span>
                                    <?php if ($isSelected): ?>
                                        <i class="fas fa-check-circle text-success ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Role Info Card -->
                    <div class="bg-light p-3 rounded-3 border" style="min-width: 320px;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="fs-8 fw-bold text-uppercase text-muted">Configuring Role:</span>
                            <span class="badge bg-primary fs-8"><?= htmlspecialchars($selectedRole['role_name']) ?></span>
                        </div>
                        <div class="fs-8 text-secondary mb-2">
                            <?= htmlspecialchars($selectedRole['description'] ?? 'System access tier') ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center fs-8 pt-2 border-top">
                            <span class="text-muted">Total Permitted Pages:</span>
                            <span class="fw-bold text-dark" id="roleAllowedCountBadge">
                                <span class="badge bg-success" id="activePagesCounter">0</span> / <span id="totalAvailablePages">0</span>
                            </span>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- User Selector -->
                <div class="row g-3 align-items-center">
                    <div class="col-lg-6">
                        <label class="form-label fs-7 fw-bold text-muted text-uppercase mb-1">Select Specific User Account</label>
                        <select class="form-select form-select-md select2-user" onchange="location.href='<?= base_url('access-rights?mode=user&user_id=') ?>' + this.value;">
                            <?php foreach ($allUsers as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= (int)$u['id'] === $selectedUserId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['user_code']) ?>) &bull; <?= htmlspecialchars($u['role_name']) ?> <?= !empty($u['designation']) ? '— ' . htmlspecialchars($u['designation']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text fs-8 text-muted">Select an employee to customize individual permissions or override role defaults.</div>
                    </div>

                    <div class="col-lg-6">
                        <div class="bg-light p-3 rounded-3 border">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <div>
                                    <strong class="fs-7 text-dark"><?= htmlspecialchars($selectedUser['full_name'] ?? 'User') ?></strong>
                                    <span class="text-muted fs-8">(<?= htmlspecialchars($selectedUser['user_code'] ?? '') ?>)</span>
                                </div>
                                <span class="badge bg-primary"><?= htmlspecialchars($selectedUser['role_name'] ?? '') ?></span>
                            </div>
                            <div class="fs-8 text-muted mb-2">
                                <i class="fas fa-briefcase me-1 text-secondary"></i> Designation: <strong><?= htmlspecialchars($selectedUser['designation'] ?: 'Not Specified') ?></strong> &bull; Dept: <?= htmlspecialchars($selectedUser['department'] ?? 'Operations') ?>
                            </div>
                            <div class="d-flex align-items-center justify-content-between fs-8 pt-2 border-top">
                                <span>Status: <?= $hasCustomOverrides ? '<span class="badge bg-warning text-dark"><i class="fas fa-user-edit me-1"></i>Custom Overrides Active</span>' : '<span class="badge bg-secondary">Inheriting Role Defaults</span>' ?></span>
                                <span class="fw-bold text-dark">
                                    Permitted: <span class="badge bg-success" id="activePagesCounter">0</span> / <span id="totalAvailablePages">0</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>    <!-- Quick Global Actions Toolbar & Live Metrics -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="fs-8 fw-bold text-muted text-uppercase me-2"><i class="fas fa-magic me-1 text-primary"></i>Global Presets:</span>
                    <button type="button" class="btn btn-sm btn-outline-success fw-bold px-3" onclick="applyGlobalPreset('full')">
                        <i class="fas fa-check-double me-1"></i>Full CRUD (All)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold px-3" onclick="applyGlobalPreset('readonly')">
                        <i class="fas fa-eye me-1"></i>Read Only (All)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-3" onclick="applyGlobalPreset('revoke')">
                        <i class="fas fa-ban me-1"></i>Revoke All
                    </button>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <?php if ($mode === 'role'): ?>
                        <form action="<?= base_url('access-rights/reset') ?>" method="POST" class="d-inline" onsubmit="return confirm('Reset this role permissions back to factory defaults?');">
                            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                            <input type="hidden" name="role_id" value="<?= $selectedRoleId ?>">
                            <button type="submit" class="btn btn-sm btn-outline-warning text-dark fw-bold">
                                <i class="fas fa-undo me-1"></i>Reset to Factory Defaults
                            </button>
                        </form>
                    <?php else: ?>
                        <?php if ($hasCustomOverrides): ?>
                            <form action="<?= base_url('access-rights/save') ?>" method="POST" class="d-inline" onsubmit="return confirm('Discard custom user overrides and restore standard role defaults?');">
                                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                <input type="hidden" name="mode" value="user">
                                <input type="hidden" name="user_id" value="<?= $selectedUserId ?>">
                                <input type="hidden" name="reset_to_role" value="1">
                                <button type="submit" class="btn btn-sm btn-outline-warning text-dark fw-bold">
                                    <i class="fas fa-undo me-1"></i>Reset to Role Defaults
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Live Rights Breakdown Badges -->
            <div class="d-flex align-items-center flex-wrap gap-2 pt-3 mt-3 border-top fs-8">
                <span class="text-muted fw-bold">Active Rights Matrix:</span>
                <span class="badge bg-light text-dark border px-2 py-1">
                    <i class="fas fa-eye text-primary me-1"></i>View / Access: <strong id="cnt-view" class="text-primary">0</strong>
                </span>
                <span class="badge bg-light text-dark border px-2 py-1">
                    <i class="fas fa-plus-circle text-success me-1"></i>Create (C): <strong id="cnt-create" class="text-success">0</strong>
                </span>
                <span class="badge bg-light text-dark border px-2 py-1">
                    <i class="fas fa-edit text-info me-1"></i>Edit / Update (U): <strong id="cnt-edit" class="text-info">0</strong>
                </span>
                <span class="badge bg-light text-dark border px-2 py-1">
                    <i class="fas fa-trash-alt text-danger me-1"></i>Delete (D): <strong id="cnt-delete" class="text-danger">0</strong>
                </span>
            </div>
        </div>
    </div>

    <!-- Permissions Form -->
    <form action="<?= base_url('access-rights/save') ?>" method="POST" id="permissionsForm">
        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
        <input type="hidden" name="mode" value="<?= $mode ?>">
        <?php if ($mode === 'role'): ?>
            <input type="hidden" name="role_id" value="<?= $selectedRoleId ?>">
        <?php else: ?>
            <input type="hidden" name="user_id" value="<?= $selectedUserId ?>">
        <?php endif; ?>

        <!-- Module Cards -->
        <div class="row g-4 mb-4">
            <?php 
            $totalCount = 0;
            foreach ($catalog as $modKey => $mod): 
                $modPages = $mod['pages'];
                $totalCount += count($modPages);
                $allowedInMod = 0;
                foreach ($modPages as $pk => $pd) {
                    $pData = $activePermissions[$pk] ?? null;
                    if (!empty($pData['is_allowed'])) $allowedInMod++;
                }
            ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden module-block" id="mod_card_<?= $modKey ?>">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle fs-5" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                <i class="<?= htmlspecialchars($mod['icon']) ?>"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($mod['name']) ?></h6>
                                <span class="text-muted fs-8"><?= htmlspecialchars($mod['description']) ?></span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-light text-dark border fs-8 py-2 px-3">
                                <span class="mod-allowed-count fw-bold text-primary" id="mod_count_<?= $modKey ?>"><?= $allowedInMod ?></span> / <?= count($modPages) ?> Allowed
                            </span>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-success btn-sm fw-bold px-2" title="Enable Full CRUD for all pages in this module" onclick="applyModulePreset('<?= $modKey ?>', 'full')">
                                    <i class="fas fa-check-double me-1"></i>Full CRUD
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm fw-bold px-2" title="Enable Read Only for all pages in this module" onclick="applyModulePreset('<?= $modKey ?>', 'readonly')">
                                    <i class="fas fa-eye me-1"></i>Read Only
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm fw-bold px-2" title="Revoke all access for this module" onclick="applyModulePreset('<?= $modKey ?>', 'revoke')">
                                    <i class="fas fa-ban me-1"></i>Revoke
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 fs-8">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 90px;" class="text-center">View (R)</th>
                                        <th style="width: 280px;">Page / Screen</th>
                                        <th style="width: 170px;">Route / URL</th>
                                        <th style="width: 320px;">CRUD Rights Controls</th>
                                        <th style="width: 160px;" class="text-center">Row Presets</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modPages as $pageKey => $page): 
                                        $pData = $activePermissions[$pageKey] ?? ['is_allowed' => 0, 'can_create' => 0, 'can_edit' => 0, 'can_delete' => 0];
                                        $isAllowed = !empty($pData['is_allowed']);
                                        $canCreate = !empty($pData['can_create']);
                                        $canEdit   = !empty($pData['can_edit']);
                                        $canDelete = !empty($pData['can_delete']);
                                    ?>
                                    <tr class="permission-row <?= $isAllowed ? 'table-success-subtle' : 'table-inactive' ?>" id="row_<?= $pageKey ?>">
                                        <!-- View / Read Master Toggle -->
                                        <td class="text-center">
                                            <div class="form-check form-switch d-inline-block m-0">
                                                <input class="form-check-input perm-view perm-mod-<?= $modKey ?>-view" 
                                                       type="checkbox" 
                                                       name="crud[<?= $pageKey ?>][view]" 
                                                       value="1" 
                                                       id="chk_view_<?= $pageKey ?>" 
                                                       data-page="<?= $pageKey ?>" 
                                                       data-module="<?= $modKey ?>" 
                                                       <?= $isAllowed ? 'checked' : '' ?> 
                                                       onchange="onViewToggle('<?= $pageKey ?>', '<?= $modKey ?>')">
                                            </div>
                                        </td>

                                        <!-- Page Name & Description -->
                                        <td>
                                            <label for="chk_view_<?= $pageKey ?>" class="fw-bold text-dark d-block mb-0 cursor-pointer">
                                                <?= htmlspecialchars($page['name']) ?>
                                            </label>
                                            <span class="text-muted fs-9 d-block"><?= htmlspecialchars($page['desc']) ?></span>
                                        </td>

                                        <!-- Route URL -->
                                        <td>
                                            <code class="text-primary bg-light px-2 py-1 rounded border fs-9">
                                                /<?= htmlspecialchars($page['url']) ?>
                                            </code>
                                        </td>

                                        <!-- CRUD Action Checkboxes -->
                                        <td>
                                            <div class="d-flex align-items-center gap-2 flex-wrap crud-controls-<?= $pageKey ?>">
                                                <!-- Create -->
                                                <div class="form-check m-0">
                                                    <input class="form-check-input perm-create perm-mod-<?= $modKey ?>-create" 
                                                           type="checkbox" 
                                                           name="crud[<?= $pageKey ?>][create]" 
                                                           value="1" 
                                                           id="chk_c_<?= $pageKey ?>" 
                                                           data-page="<?= $pageKey ?>" 
                                                           data-module="<?= $modKey ?>" 
                                                           <?= $canCreate ? 'checked' : '' ?> 
                                                           <?= !$isAllowed ? 'disabled' : '' ?> 
                                                           onchange="updateGlobalCounters()">
                                                    <label class="form-check-label badge-perm-label badge-perm-create" for="chk_c_<?= $pageKey ?>">
                                                        <i class="fas fa-plus me-1 text-success"></i>Create
                                                    </label>
                                                </div>

                                                <!-- Edit / Update -->
                                                <div class="form-check m-0">
                                                    <input class="form-check-input perm-edit perm-mod-<?= $modKey ?>-edit" 
                                                           type="checkbox" 
                                                           name="crud[<?= $pageKey ?>][edit]" 
                                                           value="1" 
                                                           id="chk_e_<?= $pageKey ?>" 
                                                           data-page="<?= $pageKey ?>" 
                                                           data-module="<?= $modKey ?>" 
                                                           <?= $canEdit ? 'checked' : '' ?> 
                                                           <?= !$isAllowed ? 'disabled' : '' ?> 
                                                           onchange="updateGlobalCounters()">
                                                    <label class="form-check-label badge-perm-label badge-perm-edit" for="chk_e_<?= $pageKey ?>">
                                                        <i class="fas fa-edit me-1 text-primary"></i>Edit
                                                    </label>
                                                </div>

                                                <!-- Delete -->
                                                <div class="form-check m-0">
                                                    <input class="form-check-input perm-delete perm-mod-<?= $modKey ?>-delete" 
                                                           type="checkbox" 
                                                           name="crud[<?= $pageKey ?>][delete]" 
                                                           value="1" 
                                                           id="chk_d_<?= $pageKey ?>" 
                                                           data-page="<?= $pageKey ?>" 
                                                           data-module="<?= $modKey ?>" 
                                                           <?= $canDelete ? 'checked' : '' ?> 
                                                           <?= !$isAllowed ? 'disabled' : '' ?> 
                                                           onchange="updateGlobalCounters()">
                                                    <label class="form-check-label badge-perm-label badge-perm-delete" for="chk_d_<?= $pageKey ?>">
                                                        <i class="fas fa-trash-alt me-1 text-danger"></i>Delete
                                                    </label>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Row Quick Presets -->
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-success btn-xs py-0 px-2 fw-semibold" title="Full CRUD for this page" onclick="applyRowPreset('<?= $pageKey ?>', '<?= $modKey ?>', 'full')">
                                                    Full
                                                </button>
                                                <button type="button" class="btn btn-outline-primary btn-xs py-0 px-2 fw-semibold" title="Read Only for this page" onclick="applyRowPreset('<?= $pageKey ?>', '<?= $modKey ?>', 'readonly')">
                                                    Read
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-2 fw-semibold" title="Revoke access" onclick="applyRowPreset('<?= $pageKey ?>', '<?= $modKey ?>', 'revoke')">
                                                    Off
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Sticky Bottom Save Bar -->
        <div class="sticky-bottom bg-white border shadow-lg p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-shield-alt text-primary fs-4"></i>
                <div>
                    <span class="fw-bold text-dark fs-7 d-block">Ready to apply access rights &amp; CRUD permissions?</span>
                    <span class="text-muted fs-8">Changes take effect immediately across all active user sessions without cache delay.</span>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= base_url('access-rights') ?>" class="btn btn-light border fw-bold px-3">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
                    <i class="fas fa-save me-1"></i>Save Access Rights
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Page Interactivity Script with CRUD Logic -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    updateGlobalCounters();
});

function updateGlobalCounters() {
    var viewChks = document.querySelectorAll(".perm-view");
    var createChks = document.querySelectorAll(".perm-create");
    var editChks = document.querySelectorAll(".perm-edit");
    var deleteChks = document.querySelectorAll(".perm-delete");

    var total = viewChks.length;
    var viewCount = 0;
    var createCount = 0;
    var editCount = 0;
    var deleteCount = 0;

    viewChks.forEach(function(chk) {
        if (chk.checked) viewCount++;
    });
    createChks.forEach(function(chk) {
        if (chk.checked && !chk.disabled) createCount++;
    });
    editChks.forEach(function(chk) {
        if (chk.checked && !chk.disabled) editCount++;
    });
    deleteChks.forEach(function(chk) {
        if (chk.checked && !chk.disabled) deleteCount++;
    });

    var badge = document.getElementById("activePagesCounter");
    if (badge) badge.textContent = viewCount;

    var totalEl = document.getElementById("totalAvailablePages");
    if (totalEl) totalEl.textContent = total;

    // Metric Badges
    var cntV = document.getElementById("cnt-view");
    if (cntV) cntV.textContent = viewCount;
    var cntC = document.getElementById("cnt-create");
    if (cntC) cntC.textContent = createCount;
    var cntE = document.getElementById("cnt-edit");
    if (cntE) cntE.textContent = editCount;
    var cntD = document.getElementById("cnt-delete");
    if (cntD) cntD.textContent = deleteCount;

    // Update module counts
    var modules = {};
    viewChks.forEach(function(chk) {
        var mod = chk.getAttribute("data-module");
        if (!modules[mod]) modules[mod] = { total: 0, checked: 0 };
        modules[mod].total++;
        if (chk.checked) modules[mod].checked++;
    });

    for (var m in modules) {
        var cntEl = document.getElementById("mod_count_" + m);
        if (cntEl) {
            cntEl.textContent = modules[m].checked;
        }
    }
}

function onViewToggle(pageKey, modKey) {
    var vChk = document.getElementById("chk_view_" + pageKey);
    var cChk = document.getElementById("chk_c_" + pageKey);
    var eChk = document.getElementById("chk_e_" + pageKey);
    var dChk = document.getElementById("chk_d_" + pageKey);
    var row = document.getElementById("row_" + pageKey);

    if (vChk && row) {
        if (vChk.checked) {
            row.classList.add("table-success-subtle");
            row.classList.remove("table-inactive");
            row.classList.remove("opacity-75");
            if (cChk) cChk.disabled = false;
            if (eChk) eChk.disabled = false;
            if (dChk) dChk.disabled = false;
        } else {
            row.classList.remove("table-success-subtle");
            row.classList.add("table-inactive");
            row.classList.remove("opacity-75");
            // If view is revoked, disable and uncheck CRUD actions
            if (cChk) { cChk.checked = false; cChk.disabled = true; }
            if (eChk) { eChk.checked = false; eChk.disabled = true; }
            if (dChk) { dChk.checked = false; dChk.disabled = true; }
        }
    }
    updateGlobalCounters();
}

function applyRowPreset(pageKey, modKey, preset) {
    var vChk = document.getElementById("chk_view_" + pageKey);
    var cChk = document.getElementById("chk_c_" + pageKey);
    var eChk = document.getElementById("chk_e_" + pageKey);
    var dChk = document.getElementById("chk_d_" + pageKey);
    var row = document.getElementById("row_" + pageKey);

    if (!vChk || !row) return;

    if (preset === "full") {
        vChk.checked = true;
        row.classList.add("table-success-subtle");
        row.classList.remove("table-inactive");
        row.classList.remove("opacity-75");
        if (cChk) { cChk.disabled = false; cChk.checked = true; }
        if (eChk) { eChk.disabled = false; eChk.checked = true; }
        if (dChk) { dChk.disabled = false; dChk.checked = true; }
    } else if (preset === "readonly") {
        vChk.checked = true;
        row.classList.add("table-success-subtle");
        row.classList.remove("table-inactive");
        row.classList.remove("opacity-75");
        if (cChk) { cChk.disabled = false; cChk.checked = false; }
        if (eChk) { eChk.disabled = false; eChk.checked = false; }
        if (dChk) { dChk.disabled = false; dChk.checked = false; }
    } else if (preset === "revoke") {
        vChk.checked = false;
        row.classList.remove("table-success-subtle");
        row.classList.add("table-inactive");
        row.classList.remove("opacity-75");
        if (cChk) { cChk.checked = false; cChk.disabled = true; }
        if (eChk) { eChk.checked = false; eChk.disabled = true; }
        if (dChk) { dChk.checked = false; dChk.disabled = true; }
    }

    updateGlobalCounters();
}

function applyModulePreset(modKey, preset) {
    var viewChks = document.querySelectorAll(".perm-mod-" + modKey + "-view");
    viewChks.forEach(function(vChk) {
        var pKey = vChk.getAttribute("data-page");
        applyRowPreset(pKey, modKey, preset);
    });
}

function applyGlobalPreset(preset) {
    var viewChks = document.querySelectorAll(".perm-view");
    viewChks.forEach(function(vChk) {
        var pKey = vChk.getAttribute("data-page");
        var modKey = vChk.getAttribute("data-module");
        applyRowPreset(pKey, modKey, preset);
    });
}
</script>
