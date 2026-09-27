<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Mail Query & Task Tickets</h4>
            <p class="text-muted fs-7 mb-0">Centralized Query Register and SLA Tracker</p>
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

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= base_url('tickets') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ticket #, subject, agency..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <?php foreach (['New', 'In Progress', 'Pending', 'Closed', 'Cancelled'] as $st): ?>
                            <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="activity_id" class="form-select form-select-sm">
                        <option value="">All Activities</option>
                        <?php foreach ($activities as $act): ?>
                            <option value="<?= $act['id'] ?>" <?= ($filters['activity_id'] ?? '') == $act['id'] ? 'selected' : '' ?>><?= htmlspecialchars($act['activity_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="allocated_to" class="form-select form-select-sm">
                        <option value="">All Assignees</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($filters['allocated_to'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?= base_url('tickets') ?>" class="btn btn-sm btn-light border"><i class="fas fa-undo"></i></a>
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
                            <th>Type</th>
                            <th>Category</th>
                            <th>Received Date</th>
                            <th>From / Agency</th>
                            <th>Subject</th>
                            <th>Activity</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>TAT SLA</th>
                            <th class="text-end text-nowrap" style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td class="text-nowrap">
                                <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="ticket-no-link" title="Click to view ticket details">
                                    <?= htmlspecialchars($t['ticket_number']) ?>
                                </a>
                            </td>
                            <td class="text-nowrap">
                                <span class="badge <?= ($t['ticket_type'] === 'Task Ticket' || !empty($t['is_task'])) ? 'badge-task-ticket' : 'badge-query-ticket' ?>">
                                    <?= htmlspecialchars($t['ticket_type'] ?? ($t['is_task'] ? 'Task Ticket' : 'Query Ticket')) ?>
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <?php if (!empty($t['category_name'])): ?>
                                    <span class="badge bg-secondary px-2 py-1"><?= htmlspecialchars($t['category_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted fs-8">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="fs-8 text-nowrap"><?= format_datetime($t['received_datetime']) ?></td>
                            <td>
                                <div class="fw-bold fs-7"><?= htmlspecialchars($t['from_address']) ?></div>
                                <?php if (!empty($t['agency_code'])): ?>
                                    <small class="text-muted">Agency: <?= htmlspecialchars($t['agency_code']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold fs-7"><?= htmlspecialchars($t['subject']) ?></div>
                            </td>
                            <td class="fs-8">
                                <div><?= htmlspecialchars($t['activity_name'] ?? 'N/A') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($t['sub_activity_name'] ?? '') ?></small>
                            </td>
                            <td class="fs-8 text-nowrap"><?= htmlspecialchars($t['allocated_user_name'] ?? 'Unassigned') ?></td>
                            <td class="text-nowrap"><?= get_status_badge($t['status']) ?></td>
                            <td class="text-nowrap"><?= get_tat_badge($t['tat_datetime'], $t['status']) ?></td>
                            <td class="text-end text-nowrap">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="btn btn-outline-primary" title="View Ticket">
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
                                    <?php if (is_super_admin()): ?>
                                    <form action="<?= base_url('tickets/delete/' . $t['id']) ?>" method="POST" class="d-inline mb-0" onsubmit="return confirm('Are you sure you want to delete ticket <?= htmlspecialchars($t['ticket_number']) ?>?');">
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
