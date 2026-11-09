<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-list-alt text-primary me-2"></i>All Tasks</h4>
            <p class="text-muted fs-7 mb-0">Centralized Register and SLA Tracker for All Tasks (Common View)</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if (is_super_admin() || is_admin()): ?>
                <a href="<?= base_url('tickets/template') ?>" class="btn btn-outline-success btn-sm fw-bold" title="Download Excel CSV Template">
                    <i class="fas fa-file-excel me-1"></i>Download Template
                </a>
                <a href="<?= base_url('tickets/import') ?>" class="btn btn-success btn-sm fw-bold" title="Bulk Import Tickets Page">
                    <i class="fas fa-file-upload me-1"></i>Bulk Import
                </a>
            <?php endif; ?>
            <a href="<?= base_url('tickets/create') ?>" class="btn btn-primary btn-sm fw-bold ms-1">
                <i class="fas fa-plus-circle me-1"></i>New Ticket
            </a>
        </div>
    </div>

    <!-- Filter Slicer Card (Multi-Select Dropdown Slicers) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= base_url('tickets') ?>" method="GET" id="allTasksFilterForm">
                <div class="row g-3 align-items-end">
                    <!-- Keyword Search -->
                    <div class="col-md-3">
                        <label class="form-label fs-8 text-uppercase fw-bold text-muted mb-1"><i class="fas fa-search me-1 text-primary"></i>Keyword Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ticket #, subject, agency..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>

                    <!-- 1. STATUS SLICER -->
                    <?php 
                    $statusOptions = ['New', 'Assigned', 'In Progress', 'Pending', 'Waiting for Customer', 'Waiting for Internal Team', 'On Hold', 'Released', 'Completed', 'Closed', 'Cancelled'];
                    $isStatusSet = isset($_GET['status']);
                    $selectedStatuses = $isStatusSet ? (array)$filters['status'] : [];
                    $statusCount = count($selectedStatuses);
                    $statusLabel = ($statusCount === 0) ? 'All Statuses' : (($statusCount === count($statusOptions)) ? 'All Selected (' . count($statusOptions) . ')' : $statusCount . ' Selected');
                    ?>
                    <div class="col-md-3 slicer-dropdown-container">
                        <label class="form-label fs-8 text-uppercase fw-bold text-muted mb-1"><i class="fas fa-tasks me-1 text-primary"></i>Status Slicer</label>
                        <button type="button" class="slicer-btn" onclick="toggleSlicerDropdown('dropdown-status', event)">
                            <span id="btn-status-label" class="truncate"><?= $statusLabel ?></span>
                            <i class="fas fa-chevron-down fs-8 text-muted"></i>
                        </button>
                        <div id="dropdown-status" class="slicer-panel">
                            <input type="text" oninput="filterSlicerOptions('status')" id="search-status" placeholder="Search status..." class="form-control form-control-sm slicer-search-input mb-2">
                            <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom fs-8">
                                <button type="button" onclick="selectAllSlicer('status')" class="btn btn-link p-0 text-primary fw-bold text-decoration-none fs-8">Select All</button>
                                <button type="button" onclick="clearAllSlicer('status')" class="btn btn-link p-0 text-muted text-decoration-none fs-8">Clear</button>
                            </div>
                            <div id="list-status" class="slicer-list-scroll">
                                <?php foreach ($statusOptions as $st): ?>
                                    <label class="slicer-item-option">
                                        <input type="checkbox" name="status[]" value="<?= $st ?>" <?= in_array($st, $selectedStatuses) ? 'checked' : '' ?> onchange="updateSlicerLabel('status')">
                                        <span><?= $st ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 2. ACTIVITY SLICER -->
                    <?php 
                    $selectedActivities = isset($_GET['activity_id']) ? (array)$filters['activity_id'] : [];
                    $actCount = count($selectedActivities);
                    $actLabel = ($actCount === 0) ? 'All Activities' : (($actCount === count($activities)) ? 'All Selected (' . count($activities) . ')' : $actCount . ' Selected');
                    ?>
                    <div class="col-md-3 slicer-dropdown-container">
                        <label class="form-label fs-8 text-uppercase fw-bold text-muted mb-1"><i class="fas fa-cubes me-1 text-primary"></i>Activity Slicer</label>
                        <button type="button" class="slicer-btn" onclick="toggleSlicerDropdown('dropdown-activity', event)">
                            <span id="btn-activity-label" class="truncate"><?= $actLabel ?></span>
                            <i class="fas fa-chevron-down fs-8 text-muted"></i>
                        </button>
                        <div id="dropdown-activity" class="slicer-panel">
                            <input type="text" oninput="filterSlicerOptions('activity')" id="search-activity" placeholder="Search activity..." class="form-control form-control-sm slicer-search-input mb-2">
                            <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom fs-8">
                                <button type="button" onclick="selectAllSlicer('activity')" class="btn btn-link p-0 text-primary fw-bold text-decoration-none fs-8">Select All</button>
                                <button type="button" onclick="clearAllSlicer('activity')" class="btn btn-link p-0 text-muted text-decoration-none fs-8">Clear</button>
                            </div>
                            <div id="list-activity" class="slicer-list-scroll">
                                <?php foreach ($activities as $act): ?>
                                    <label class="slicer-item-option">
                                        <input type="checkbox" name="activity_id[]" value="<?= $act['id'] ?>" <?= in_array($act['id'], $selectedActivities) ? 'checked' : '' ?> onchange="updateSlicerLabel('activity')">
                                        <span><?= htmlspecialchars($act['activity_name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 3. ASSIGNEE SLICER -->
                    <?php 
                    $selectedAssignees = isset($_GET['allocated_to']) ? (array)$filters['allocated_to'] : [];
                    $userCount = count($selectedAssignees);
                    $userLabel = ($userCount === 0) ? 'All Assignees' : (($userCount === count($users)) ? 'All Selected (' . count($users) . ')' : $userCount . ' Selected');
                    ?>
                    <div class="col-md-3 slicer-dropdown-container">
                        <label class="form-label fs-8 text-uppercase fw-bold text-muted mb-1"><i class="fas fa-users me-1 text-primary"></i>Assignee Slicer</label>
                        <button type="button" class="slicer-btn" onclick="toggleSlicerDropdown('dropdown-assignee', event)">
                            <span id="btn-assignee-label" class="truncate"><?= $userLabel ?></span>
                            <i class="fas fa-chevron-down fs-8 text-muted"></i>
                        </button>
                        <div id="dropdown-assignee" class="slicer-panel">
                            <input type="text" oninput="filterSlicerOptions('assignee')" id="search-assignee" placeholder="Search assignee..." class="form-control form-control-sm slicer-search-input mb-2">
                            <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom fs-8">
                                <button type="button" onclick="selectAllSlicer('assignee')" class="btn btn-link p-0 text-primary fw-bold text-decoration-none fs-8">Select All</button>
                                <button type="button" onclick="clearAllSlicer('assignee')" class="btn btn-link p-0 text-muted text-decoration-none fs-8">Clear</button>
                            </div>
                            <div id="list-assignee" class="slicer-list-scroll">
                                <?php foreach ($users as $u): ?>
                                    <label class="slicer-item-option">
                                        <input type="checkbox" name="allocated_to[]" value="<?= $u['id'] ?>" <?= in_array($u['id'], $selectedAssignees) ? 'checked' : '' ?> onchange="updateSlicerLabel('assignee')">
                                        <span><?= htmlspecialchars($u['full_name']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 pt-1">
                        <button type="submit" class="btn btn-primary btn-sm fw-bold px-4"><i class="fas fa-filter me-1"></i>Apply Slicers</button>
                        <a href="<?= base_url('tickets') ?>" class="btn btn-outline-secondary btn-sm fw-bold px-3"><i class="fas fa-undo me-1"></i>Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Listing Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th>Ticket #</th>
                            <th>Category</th>
                            <th>Received Date</th>
                            <th>From / Agency</th>
                            <th>Activity</th>
                            <th>Current Bucket Owner</th>
                            <th>Status</th>
                            <th>TAT SLA</th>
                            <th class="text-end text-nowrap" style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td class="text-nowrap">
                                <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="ticket-no-link fw-bold" title="Subject: <?= htmlspecialchars($t['subject'], ENT_QUOTES) ?>">
                                    <?= htmlspecialchars($t['ticket_number']) ?>
                                </a>
                            </td>
                            <td class="text-nowrap">
                                <?= get_category_badge($t['category_name'] ?? '') ?>
                            </td>
                            <td class="fs-8 text-nowrap"><?= format_datetime($t['received_datetime']) ?></td>
                            <td>
                                <div class="fw-bold fs-7 text-dark"><?= htmlspecialchars($t['from_address']) ?></div>
                                <?php if (!empty($t['agency_code'])): ?>
                                    <small class="text-muted"><i class="fas fa-building me-1"></i><?= htmlspecialchars($t['agency_code']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="fs-8">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($t['activity_name'] ?? 'N/A') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($t['sub_activity_name'] ?? '') ?></small>
                            </td>
                            <td class="fs-8 text-nowrap">
                                <?php $bInfo = get_current_bucket_info($t); ?>
                                <div class="mb-1">
                                    <span class="badge <?= $bInfo['badge_class'] ?> py-1 px-2 fw-bold fs-8">
                                        <i class="fas <?= $bInfo['icon'] ?? 'fa-user' ?> me-1"></i><?= htmlspecialchars($bInfo['role_label']) ?>
                                    </span>
                                </div>
                                <div class="fw-bold text-dark fs-7"><i class="fas fa-user-circle text-secondary me-1"></i><?= htmlspecialchars($bInfo['user_name']) ?></div>
                                <?php if (!empty($bInfo['user_code'])): ?>
                                    <small class="text-muted fs-8"><i class="fas fa-id-badge me-1"></i>ID: <?= htmlspecialchars($bInfo['user_code']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap"><?= get_status_badge($t['status'] ?? 'New') ?></td>
                            <td class="fs-8 text-nowrap">
                                <?= get_tat_badge($t['tat_datetime'], $t['status'] ?? '') ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="btn btn-outline-primary" title="View Details">
                                        <i class="fas fa-folder-open"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-warning text-dark btn-reschedule" 
                                            data-id="<?= $t['id'] ?>"
                                            data-ticket="<?= htmlspecialchars($t['ticket_number']) ?>"
                                            data-date="<?= $t['scheduled_date'] ?? date('Y-m-d') ?>"
                                            title="Postpone / Reschedule">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-reassign"
                                            data-id="<?= $t['id'] ?>"
                                            data-ticket="<?= htmlspecialchars($t['ticket_number']) ?>"
                                            data-user_id="<?= $t['allocated_to'] ?? '' ?>"
                                            title="Reassign Employee">
                                        <i class="fas fa-user-edit"></i>
                                    </button>
                                    <?php if (is_super_admin() || is_admin()): ?>
                                    <form action="<?= base_url('tickets/delete/' . $t['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete ticket <?= $t['ticket_number'] ?>?');">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete Ticket">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
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

<!-- Modal: Reschedule Ticket -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/reschedule') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" id="reschedule_ticket_id" value="0">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-clock me-2"></i>Reschedule / Postpone Ticket <span id="reschedule_ticket_num"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Target Scheduled Date <span class="text-danger">*</span></label>
                        <input type="date" name="scheduled_date" id="reschedule_date_val" class="form-control fw-bold" min="<?= date('Y-m-d') ?>" required>
                        <small class="text-muted fs-8 d-block mt-1">Select today or a future date to postpone work on this ticket.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Reason / Reschedule Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Postponed awaiting client confirmation"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4"><i class="fas fa-calendar-check me-1"></i>Save Reschedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reassign Ticket -->
<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/reassign') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="ticket_id" id="reassign_ticket_id" value="0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i>Reassign Ticket <span id="reassign_ticket_num"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Assign to Employee <span class="text-danger">*</span></label>
                        <select name="allocated_to" id="reassign_user_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach (($users ?? []) as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= $u['user_code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Reassignment Reason / Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Workload balancing"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fas fa-user-check me-1"></i>Confirm Reassign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSlicerDropdown(dropdownId, event) {
    if (event) event.stopPropagation();
    const targetPanel = document.getElementById(dropdownId);
    if (!targetPanel) return;
    const isShowing = targetPanel.classList.contains('show');
    
    document.querySelectorAll('.slicer-panel').forEach(panel => {
        if (panel !== targetPanel) panel.classList.remove('show');
    });

    if (!isShowing) {
        targetPanel.classList.add('show');
        const searchInput = targetPanel.querySelector('.slicer-search-input');
        if (searchInput) {
            setTimeout(() => searchInput.focus(), 50);
        }
    } else {
        targetPanel.classList.remove('show');
    }
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.slicer-dropdown-container')) {
        document.querySelectorAll('.slicer-panel').forEach(panel => panel.classList.remove('show'));
    }
});

function filterSlicerOptions(key) {
    const input = document.getElementById('search-' + key);
    if (!input) return;
    const filter = input.value.toLowerCase();
    const list = document.getElementById('list-' + key);
    if (!list) return;
    const options = list.getElementsByClassName('slicer-item-option');

    for (let i = 0; i < options.length; i++) {
        const txtValue = options[i].textContent || options[i].innerText;
        if (txtValue.toLowerCase().indexOf(filter) > -1) {
            options[i].style.display = "flex";
        } else {
            options[i].style.display = "none";
        }
    }
}

function selectAllSlicer(key) {
    const list = document.getElementById('list-' + key);
    if (!list) return;
    const options = list.getElementsByClassName('slicer-item-option');
    for (let i = 0; i < options.length; i++) {
        if (options[i].style.display !== 'none') {
            const cb = options[i].querySelector('input[type="checkbox"]');
            if (cb) cb.checked = true;
        }
    }
    updateSlicerLabel(key);
}

function clearAllSlicer(key) {
    const list = document.getElementById('list-' + key);
    if (!list) return;
    const options = list.getElementsByClassName('slicer-item-option');
    for (let i = 0; i < options.length; i++) {
        if (options[i].style.display !== 'none') {
            const cb = options[i].querySelector('input[type="checkbox"]');
            if (cb) cb.checked = false;
        }
    }
    updateSlicerLabel(key);
}

function updateSlicerLabel(key) {
    const list = document.getElementById('list-' + key);
    if (!list) return;
    const checkboxes = list.querySelectorAll('input[type="checkbox"]');
    const checked = list.querySelectorAll('input[type="checkbox"]:checked');
    const labelSpan = document.getElementById('btn-' + key + '-label');

    if (!labelSpan) return;

    if (checked.length === 0) {
        labelSpan.textContent = 'None Selected (0)';
    } else if (checked.length === checkboxes.length) {
        labelSpan.textContent = 'All Selected (' + checkboxes.length + ')';
    } else {
        labelSpan.textContent = checked.length + ' Selected';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-reschedule').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('reschedule_ticket_id').value = this.dataset.id;
            document.getElementById('reschedule_ticket_num').textContent = '(' + this.dataset.ticket + ')';
            document.getElementById('reschedule_date_val').value = this.dataset.date || "<?= date('Y-m-d') ?>";

            var modal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
            modal.show();
        });
    });

    document.querySelectorAll('.btn-reassign').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('reassign_ticket_id').value = this.dataset.id;
            document.getElementById('reassign_ticket_num').textContent = '(' + this.dataset.ticket + ')';
            document.getElementById('reassign_user_id').value = this.dataset.user_id || '';

            var modal = new bootstrap.Modal(document.getElementById('reassignModal'));
            modal.show();
        });
    });
});
</script>
