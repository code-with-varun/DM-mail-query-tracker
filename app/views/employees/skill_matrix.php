<div class="container-fluid px-4 py-4">
    <!-- Header Section with Quick Employee Switcher -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="<?= base_url('employees') ?>" class="btn btn-sm btn-outline-secondary rounded-circle p-1" title="Back to Employee List">
                    <i class="fas fa-arrow-left px-1"></i>
                </a>
                <h4 class="fw-bold mb-0">Skill Matrix & Competency Alignment Hub</h4>
            </div>
            <p class="text-muted fs-7 mb-0">Configure Process Sub-Activities, SLA & Maker-Checker Responsibilities for Operational Teams</p>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <!-- Employee Selector -->
            <div class="input-group input-group-sm shadow-2xs" style="width: 320px;">
                <span class="input-group-text bg-dark text-white fw-bold"><i class="fas fa-user-cog me-1"></i>Employee:</span>
                <select class="form-select fw-bold border-dark" id="employeeSwitcherSelect" onchange="switchEmployeeMatrix(this.value)">
                    <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $u['id'] == $targetUser['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['user_code']) ?>) — <?= htmlspecialchars($u['role_name'] ?? 'User') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <a href="<?= base_url('employees') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fas fa-users me-1"></i>All Employees
            </a>
            <a href="<?= base_url('training/plan?user_id=' . $targetUser['id']) ?>" class="btn btn-outline-primary btn-sm fw-bold">
                <i class="fas fa-graduation-cap me-1"></i>Create Training Plan
            </a>
        </div>
    </div>

    <!-- Employee Information Card & Summary Metrics -->
    <div class="row g-3 mb-4">
        <!-- Employee Profile Info -->
        <div class="col-lg-4 col-md-5">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="user-avatar shadow-sm rounded-circle d-flex align-items-center justify-content-center text-white fw-bold bg-danger" style="width: 54px; height: 54px; font-size: 1.4rem;">
                        <?= strtoupper(substr($targetUser['full_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="overflow-hidden">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="fw-bold mb-0 text-dark text-truncate"><?= htmlspecialchars($targetUser['full_name']) ?></h5>
                            <span class="badge bg-primary fs-9 fw-bold"><?= htmlspecialchars($targetUser['user_code']) ?></span>
                        </div>
                        <div class="fs-8 text-muted mb-1 text-truncate">
                            <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($targetUser['email']) ?>
                        </div>
                        <div class="d-flex flex-wrap gap-1 align-items-center fs-8">
                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($targetUser['department'] ?? 'Operations') ?></span>
                            <span class="badge bg-info text-dark"><?= htmlspecialchars($targetUser['role_name'] ?? 'User') ?></span>
                            <?php if (!empty($targetUser['manager_name'])): ?>
                            <span class="text-muted fs-9 ms-1" title="Reporting Manager">Mgr: <strong><?= htmlspecialchars($targetUser['manager_name']) ?></strong></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="col-lg-8 col-md-7">
            <div class="row g-2 h-100">
                <div class="col-sm-3 col-6">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-light text-center">
                        <div class="text-muted fs-8 fw-bold mb-1"><i class="fas fa-tasks text-primary me-1"></i>Total System SLA</div>
                        <div class="fs-3 fw-bold text-dark"><?= $stats['total'] ?></div>
                        <div class="fs-9 text-secondary">Sub-Activities</div>
                    </div>
                </div>
                <div class="col-sm-3 col-6">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-light text-center border-start border-4 border-primary">
                        <div class="text-muted fs-8 fw-bold mb-1"><i class="fas fa-check-circle text-primary me-1"></i>Maker Assigned</div>
                        <div class="fs-3 fw-bold text-primary"><?= $stats['maker'] ?></div>
                        <div class="fs-9 text-muted">Primary Processors</div>
                    </div>
                </div>
                <div class="col-sm-3 col-6">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-light text-center border-start border-4 border-warning">
                        <div class="text-muted fs-8 fw-bold mb-1"><i class="fas fa-user-check text-warning me-1"></i>Checker Assigned</div>
                        <div class="fs-3 fw-bold text-dark"><?= $stats['checker'] ?></div>
                        <div class="fs-9 text-muted">QC Reviewers</div>
                    </div>
                </div>
                <div class="col-sm-3 col-6">
                    <div class="card border-0 shadow-sm p-3 h-100 bg-light text-center border-start border-4 border-success">
                        <div class="text-muted fs-8 fw-bold mb-1"><i class="fas fa-sync text-success me-1"></i>Both (Maker+QC)</div>
                        <div class="fs-3 fw-bold text-success"><?= $stats['both'] ?></div>
                        <div class="fs-9 text-muted">Full Responsibility</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Skill Matrix Main Form -->
    <form action="<?= base_url('employees/save-skill-matrix') ?>" method="POST" id="skillMatrixMainForm">
        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
        <input type="hidden" name="user_id" value="<?= $targetUser['id'] ?>">

        <!-- Control Action Bar -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 bg-white rounded">
                <div class="row g-2 align-items-center">
                    <!-- Search Input -->
                    <div class="col-lg-4 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="matrixSearchInput" class="form-control border-start-0" placeholder="Filter division, activity, or sub-activity..." onkeyup="filterMatrixView()">
                        </div>
                    </div>

                    <!-- Filter Radio Pills -->
                    <div class="col-lg-4 col-md-7 text-center">
                        <div class="btn-group btn-group-sm w-100" role="group" id="filterStatusPills">
                            <input type="radio" class="btn-check" name="filterFilterMode" id="flt_all" value="all" checked onchange="filterMatrixView()">
                            <label class="btn btn-outline-secondary fw-bold" for="flt_all">All (<?= $stats['total'] ?>)</label>

                            <input type="radio" class="btn-check" name="filterFilterMode" id="flt_assigned" value="assigned" onchange="filterMatrixView()">
                            <label class="btn btn-outline-primary fw-bold" for="flt_assigned">Assigned (<?= $stats['assigned'] ?>)</label>

                            <input type="radio" class="btn-check" name="filterFilterMode" id="flt_unassigned" value="unassigned" onchange="filterMatrixView()">
                            <label class="btn btn-outline-secondary fw-bold" for="flt_unassigned">Unassigned</label>
                        </div>
                    </div>

                    <!-- Mass Selection Actions -->
                    <div class="col-lg-4 col-md-12 text-lg-end text-center">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary fw-bold" onclick="setAllMatrixRoles('Maker')"><i class="fas fa-check me-1"></i>All Maker</button>
                            <button type="button" class="btn btn-outline-warning text-dark fw-bold" onclick="setAllMatrixRoles('Checker')"><i class="fas fa-user-check me-1"></i>All Checker</button>
                            <button type="button" class="btn btn-outline-success fw-bold" onclick="setAllMatrixRoles('Both')"><i class="fas fa-sync me-1"></i>All Both</button>
                            <button type="button" class="btn btn-outline-danger fw-bold" onclick="clearAllMatrixRoles()"><i class="fas fa-times me-1"></i>Clear All</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hierarchical Accordion Matrix List -->
        <div id="matrixAccordionsContainer">
            <?php if (empty($groupedSubActivities)): ?>
                <div class="card border-0 shadow-sm p-5 text-center text-muted">
                    <i class="fas fa-exclamation-triangle fs-1 d-block mb-3 text-warning"></i>
                    <h5 class="fw-bold text-dark">No Sub-Activities Configured</h5>
                    <p class="fs-7 mb-3">Please configure parent activities and sub-activities under Master Settings first.</p>
                    <div>
                        <a href="<?= base_url('master/activities') ?>" class="btn btn-primary btn-sm fw-bold">Manage Activities Master</a>
                    </div>
                </div>
            <?php else: ?>
                <?php 
                $divCounter = 0;
                foreach ($groupedSubActivities as $divName => $actList): 
                    $divCounter++;
                    $divIdSanitized = 'div_' . preg_replace('/[^a-zA-Z0-9]/', '_', $divName) . '_' . $divCounter;
                ?>
                <div class="card border-0 shadow-sm mb-4 division-group-card" data-div-name="<?= strtolower(htmlspecialchars($divName)) ?>">
                    <!-- Division Header -->
                    <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-building text-warning fs-5"></i>
                            <h5 class="fw-bold mb-0 text-white"><?= htmlspecialchars($divName) ?></h5>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-xs btn-outline-light text-white px-2 fs-8" onclick="setDivisionRoles('<?= $divIdSanitized ?>', 'Maker')">Set Div Maker</button>
                                <button type="button" class="btn btn-xs btn-outline-light text-white px-2 fs-8" onclick="setDivisionRoles('<?= $divIdSanitized ?>', 'Checker')">Set Div Checker</button>
                                <button type="button" class="btn btn-xs btn-outline-light text-white px-2 fs-8" onclick="clearDivisionRoles('<?= $divIdSanitized ?>')">Clear Div</button>
                            </div>
                        </div>
                    </div>

                    <!-- Activities Container inside Division -->
                    <div class="card-body p-3 bg-light" id="<?= $divIdSanitized ?>">
                        <div class="accordion" id="accParent_<?= $divIdSanitized ?>">
                            <?php 
                            $actCounter = 0;
                            foreach ($actList as $activityName => $subActs): 
                                $actCounter++;
                                $actCollapseId = $divIdSanitized . '_act_' . $actCounter;
                                
                                // Count how many sub activities in this group are assigned
                                $assignedInAct = 0;
                                foreach ($subActs as $saCheck) {
                                    if (!empty($userSkillsMap[$saCheck['id']])) {
                                        $assignedInAct++;
                                    }
                                }
                            ?>
                            <div class="accordion-item border-0 mb-3 rounded shadow-2xs overflow-hidden activity-item-card" data-act-name="<?= strtolower(htmlspecialchars($activityName)) ?>">
                                <h2 class="accordion-header" id="heading_<?= $actCollapseId ?>">
                                    <button class="accordion-button bg-white text-dark fw-bold shadow-none py-3" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $actCollapseId ?>" aria-expanded="true">
                                        <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                            <div>
                                                <i class="fas fa-folder-open text-primary me-2"></i>
                                                <span class="fs-6 fw-bold"><?= htmlspecialchars($activityName) ?></span>
                                            </div>
                                            <div>
                                                <span class="badge bg-<?= $assignedInAct > 0 ? 'success' : 'secondary' ?> fs-8 act-assigned-badge">
                                                    <?= $assignedInAct ?> / <?= count($subActs) ?> Mapped
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>

                                <div id="<?= $actCollapseId ?>" class="accordion-collapse collapse show" data-bs-parent="#accParent_<?= $divIdSanitized ?>">
                                    <div class="accordion-body p-0 bg-white">
                                        <!-- Sub Activities Data Table -->
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light fs-8 text-secondary">
                                                    <tr>
                                                        <th style="width: 50px;" class="text-center">Select</th>
                                                        <th>Sub-Activity Name & SLA Scope</th>
                                                        <th style="width: 150px;">Target SLA / Day</th>
                                                        <th style="width: 260px;">Role Responsibility</th>
                                                        <th style="width: 90px;" class="text-center">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($subActs as $sa): 
                                                        $userSkill = $userSkillsMap[$sa['id']] ?? null;
                                                        $isAssigned = !empty($userSkill);
                                                        $currentRole = $userSkill['role_type'] ?? 'Maker';
                                                        $saSearchText = strtolower(htmlspecialchars($sa['sub_activity_name'] . ' ' . $activityName . ' ' . $divName));
                                                    ?>
                                                    <tr class="subact-row" data-search-text="<?= $saSearchText ?>" data-assigned="<?= $isAssigned ? '1' : '0' ?>" data-role="<?= strtolower($currentRole) ?>">
                                                        <td class="text-center">
                                                            <input type="checkbox" name="skills[<?= $sa['id'] ?>][selected]" id="chk_sa_<?= $sa['id'] ?>" value="1" class="form-check-input matrix-chk cursor-pointer" style="width: 1.25rem; height: 1.25rem;" <?= $isAssigned ? 'checked' : '' ?> onchange="onMatrixCheckboxToggle(<?= $sa['id'] ?>)">
                                                        </td>
                                                        <td>
                                                            <label for="chk_sa_<?= $sa['id'] ?>" class="form-check-label cursor-pointer mb-0">
                                                                <div class="fw-bold text-dark fs-7 mb-0"><?= htmlspecialchars($sa['sub_activity_name']) ?></div>
                                                                <small class="text-muted fs-9 d-block">Parent: <?= htmlspecialchars($activityName) ?> &bull; Div: <?= htmlspecialchars($divName) ?></small>
                                                            </label>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-1">
                                                                <span class="badge bg-light text-dark border fs-8" title="Default TAT SLA Hours">
                                                                    <i class="far fa-clock me-1 text-primary"></i><?= (int)($sa['default_tat_hours'] ?? 24) ?>h SLA
                                                                </span>
                                                                <?php if (!empty($sa['default_occurrence_day'])): ?>
                                                                <span class="badge bg-light text-primary border fs-8" title="Scheduled Day of Month">
                                                                    Day <?= $sa['default_occurrence_day'] ?>
                                                                </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm w-100" role="group">
                                                                <input type="radio" class="btn-check matrix-radio" name="skills[<?= $sa['id'] ?>][role_type]" id="role_m_<?= $sa['id'] ?>" value="Maker" <?= ($currentRole === 'Maker' || !$userSkill) ? 'checked' : '' ?> onchange="onMatrixRadioToggle(<?= $sa['id'] ?>)">
                                                                <label class="btn btn-outline-primary py-1 px-2 fs-8 fw-bold" for="role_m_<?= $sa['id'] ?>">Maker</label>

                                                                <input type="radio" class="btn-check matrix-radio" name="skills[<?= $sa['id'] ?>][role_type]" id="role_c_<?= $sa['id'] ?>" value="Checker" <?= $currentRole === 'Checker' ? 'checked' : '' ?> onchange="onMatrixRadioToggle(<?= $sa['id'] ?>)">
                                                                <label class="btn btn-outline-warning text-dark py-1 px-2 fs-8 fw-bold" for="role_c_<?= $sa['id'] ?>">Checker</label>

                                                                <input type="radio" class="btn-check matrix-radio" name="skills[<?= $sa['id'] ?>][role_type]" id="role_b_<?= $sa['id'] ?>" value="Both" <?= $currentRole === 'Both' ? 'checked' : '' ?> onchange="onMatrixRadioToggle(<?= $sa['id'] ?>)">
                                                                <label class="btn btn-outline-success py-1 px-2 fs-8 fw-bold" for="role_b_<?= $sa['id'] ?>">Both</label>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-<?= $isAssigned ? 'success' : 'light text-muted border' ?> status-indicator-badge" id="badge_status_<?= $sa['id'] ?>">
                                                                <?= $isAssigned ? $currentRole : 'Unmapped' ?>
                                                            </span>
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
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Sticky Floating Footer Action Bar -->
        <div class="card border-0 shadow-lg position-sticky bottom-0 mt-4 rounded-3 overflow-hidden bg-dark text-white z-3" style="bottom: 1rem;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-sliders-h text-success fs-5"></i>
                    <div>
                        <div class="fw-bold fs-7">Editing Skill Matrix for: <span class="text-warning"><?= htmlspecialchars($targetUser['full_name']) ?></span> (<?= htmlspecialchars($targetUser['user_code']) ?>)</div>
                        <small class="text-white-50 fs-9">Changes take effect immediately for task auto-allocation & roster assignments</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= base_url('employees') ?>" class="btn btn-outline-light btn-sm fw-bold">Cancel</a>
                    <button type="submit" class="btn btn-success btn-md fw-bold px-4 shadow-sm">
                        <i class="fas fa-save me-1"></i>Save Skill Matrix
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript Controls & Interactivity -->
<script>
function switchEmployeeMatrix(userId) {
    if (userId) {
        window.location.href = BASE_URL + '/employees/skill-matrix/' + userId;
    }
}

function onMatrixRadioToggle(saId) {
    var chk = document.getElementById('chk_sa_' + saId);
    if (chk) {
        chk.checked = true;
    }
    updateRowBadgeStatus(saId);
}

function onMatrixCheckboxToggle(saId) {
    updateRowBadgeStatus(saId);
}

function updateRowBadgeStatus(saId) {
    var chk = document.getElementById('chk_sa_' + saId);
    var badge = document.getElementById('badge_status_' + saId);
    if (!chk || !badge) return;

    var row = chk.closest('tr');
    if (chk.checked) {
        var role = 'Maker';
        if (document.getElementById('role_c_' + saId) && document.getElementById('role_c_' + saId).checked) role = 'Checker';
        if (document.getElementById('role_b_' + saId) && document.getElementById('role_b_' + saId).checked) role = 'Both';

        badge.className = 'badge bg-success status-indicator-badge';
        badge.innerText = role;
        if (row) row.setAttribute('data-assigned', '1');
    } else {
        badge.className = 'badge bg-light text-muted border status-indicator-badge';
        badge.innerText = 'Unmapped';
        if (row) row.setAttribute('data-assigned', '0');
    }
}

function filterMatrixView() {
    var query = document.getElementById('matrixSearchInput').value.toLowerCase();
    var filterMode = document.querySelector('input[name="filterFilterMode"]:checked').value;

    var divCards = document.querySelectorAll('.division-group-card');
    divCards.forEach(function(divCard) {
        var divName = divCard.getAttribute('data-div-name') || '';
        var actCards = divCard.querySelectorAll('.activity-item-card');
        var anyActVisible = false;

        actCards.forEach(function(actCard) {
            var actName = actCard.getAttribute('data-act-name') || '';
            var rows = actCard.querySelectorAll('.subact-row');
            var anyRowVisible = false;

            rows.forEach(function(row) {
                var searchText = row.getAttribute('data-search-text') || '';
                var isAssigned = row.getAttribute('data-assigned') === '1';

                var matchesSearch = query === '' || searchText.includes(query) || actName.includes(query) || divName.includes(query);
                var matchesFilter = true;

                if (filterMode === 'assigned') matchesFilter = isAssigned;
                if (filterMode === 'unassigned') matchesFilter = !isAssigned;

                if (matchesSearch && matchesFilter) {
                    row.style.display = '';
                    anyRowVisible = true;
                } else {
                    row.style.display = 'none';
                }
            });

            if (anyRowVisible) {
                actCard.style.display = '';
                anyActVisible = true;
            } else {
                actCard.style.display = 'none';
            }
        });

        divCard.style.display = anyActVisible ? '' : 'none';
    });
}

function setAllMatrixRoles(roleType) {
    document.querySelectorAll('.subact-row').forEach(function(row) {
        var chk = row.querySelector('.matrix-chk');
        if (chk) chk.checked = true;

        var radios = row.querySelectorAll('.matrix-radio');
        radios.forEach(function(r) {
            if (r.value === roleType) r.checked = true;
        });

        var saId = chk.id.replace('chk_sa_', '');
        updateRowBadgeStatus(saId);
    });
}

function clearAllMatrixRoles() {
    document.querySelectorAll('.subact-row').forEach(function(row) {
        var chk = row.querySelector('.matrix-chk');
        if (chk) chk.checked = false;

        var saId = chk.id.replace('chk_sa_', '');
        updateRowBadgeStatus(saId);
    });
}

function setDivisionRoles(divContainerId, roleType) {
    var container = document.getElementById(divContainerId);
    if (!container) return;

    container.querySelectorAll('.subact-row').forEach(function(row) {
        var chk = row.querySelector('.matrix-chk');
        if (chk) chk.checked = true;

        var radios = row.querySelectorAll('.matrix-radio');
        radios.forEach(function(r) {
            if (r.value === roleType) r.checked = true;
        });

        var saId = chk.id.replace('chk_sa_', '');
        updateRowBadgeStatus(saId);
    });
}

function clearDivisionRoles(divContainerId) {
    var container = document.getElementById(divContainerId);
    if (!container) return;

    container.querySelectorAll('.subact-row').forEach(function(row) {
        var chk = row.querySelector('.matrix-chk');
        if (chk) chk.checked = false;

        var saId = chk.id.replace('chk_sa_', '');
        updateRowBadgeStatus(saId);
    });
}
</script>
