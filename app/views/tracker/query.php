<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-question-circle text-primary me-2"></i>Query Tracker</h4>
            <p class="text-muted fs-7 mb-0">Centralized Register & Real-Time Status Monitor for Query Tickets</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('tickets/create') ?>" class="btn btn-primary btn-sm fw-bold">
                <i class="fas fa-plus-circle me-1"></i>Raise Query Ticket
            </a>
        </div>
    </div>

    <!-- KPI Summary Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase fs-8">Total Queries</small>
                            <h3 class="fw-bold mb-0 text-dark mt-1"><?= $stats['total'] ?? 0 ?></h3>
                        </div>
                        <div class="p-2 rounded bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-list-alt fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase fs-8">Open & Assigned</small>
                            <h3 class="fw-bold mb-0 text-info mt-1"><?= $stats['open'] ?? 0 ?></h3>
                        </div>
                        <div class="p-2 rounded bg-info bg-opacity-10 text-info">
                            <i class="fas fa-user-clock fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-4 border-indigo h-100" style="border-left-color: #6366f1 !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase fs-8">In Progress</small>
                            <h3 class="fw-bold mb-0 text-indigo mt-1" style="color: #4338ca !important;"><?= $stats['in_progress'] ?? 0 ?></h3>
                        </div>
                        <div class="p-2 rounded bg-indigo bg-opacity-10 text-indigo" style="background-color: #e0e7ff !important; color: #4338ca !important;">
                            <i class="fas fa-spinner fa-spin fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase fs-8">Pending / Hold</small>
                            <h3 class="fw-bold mb-0 text-dark mt-1"><?= $stats['pending'] ?? 0 ?></h3>
                        </div>
                        <div class="p-2 rounded bg-warning bg-opacity-15 text-dark">
                            <i class="fas fa-hourglass-half fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase fs-8">Resolved & Closed</small>
                            <h3 class="fw-bold mb-0 text-success mt-1"><?= $stats['closed'] ?? 0 ?></h3>
                        </div>
                        <div class="p-2 rounded bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-circle fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-4 border-danger h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase fs-8">Overdue SLA</small>
                            <h3 class="fw-bold mb-0 text-danger mt-1"><?= $stats['overdue'] ?? 0 ?></h3>
                        </div>
                        <div class="p-2 rounded bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-fire fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= base_url('tracker/query') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ticket #, sender, agency..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <?php foreach (['New', 'Assigned', 'In Progress', 'Pending', 'Waiting for Customer', 'Waiting for Internal Team', 'On Hold', 'Closed', 'Cancelled'] as $st): ?>
                            <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="activity_id" class="form-select form-select-sm">
                        <option value="">All Activities</option>
                        <?php foreach ($activities as $act): ?>
                            <option value="<?= $act['id'] ?>" <?= ($filters['activity_id'] ?? '') == $act['id'] ? 'selected' : '' ?>><?= htmlspecialchars($act['activity_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="allocated_to" class="form-select form-select-sm">
                        <option value="">All Assignees</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($filters['allocated_to'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?= base_url('tracker/query') ?>" class="btn btn-sm btn-light border"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Query Tickets Listing Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th>Ticket #</th>
                            <th>Category</th>
                            <th>From / Agency</th>
                            <th>Activity / Sub-Activity</th>
                            <th>Query Resolver</th>
                            <th>Create Date</th>
                            <th>Schedule Date</th>
                            <th>Target Date (SLA)</th>
                            <th>Close Date</th>
                            <th>Status</th>
                            <th>SLA TAT</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td class="fw-bold text-nowrap">
                                <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="ticket-no-link fw-bold" title="Subject: <?= htmlspecialchars($t['subject'] ?? '', ENT_QUOTES) ?>">
                                    <?= htmlspecialchars($t['ticket_number']) ?>
                                </a>
                            </td>
                            <td class="text-nowrap">
                                <?= get_category_badge($t['category_name'] ?? '') ?>
                            </td>
                            <td>
                                <div class="fw-bold fs-7 text-dark"><?= htmlspecialchars($t['from_address'] ?? 'N/A') ?></div>
                                <?php if (!empty($t['agency_code'])): ?>
                                    <small class="text-muted"><i class="fas fa-building me-1"></i><?= htmlspecialchars($t['agency_code']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="fs-8">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($t['activity_name'] ?? 'N/A') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($t['sub_activity_name'] ?? '') ?></small>
                            </td>
                            <td class="fs-8 text-nowrap">
                                <div class="fw-bold text-dark fs-7"><i class="fas fa-user-circle text-secondary me-1"></i><?= htmlspecialchars($t['allocated_user_name'] ?? 'Unassigned') ?></div>
                                <?php if (!empty($t['allocated_user_code'])): ?>
                                    <small class="text-muted fs-8"><i class="fas fa-id-badge me-1"></i>ID: <?= htmlspecialchars($t['allocated_user_code']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="fs-8 text-nowrap">
                                <span class="fw-bold text-dark"><?= format_datetime($t['created_at']) ?></span>
                            </td>
                            <td class="fs-8 text-nowrap">
                                <span class="badge bg-light text-dark border fw-bold">
                                    <i class="fas fa-calendar-day me-1 text-primary"></i><?= !empty($t['scheduled_date']) ? date('d M Y', strtotime($t['scheduled_date'])) : date('d M Y') ?>
                                </span>
                            </td>
                            <td class="fs-8 text-nowrap">
                                <span class="fw-bold text-dark"><?= !empty($t['tat_datetime']) ? format_datetime($t['tat_datetime']) : '-' ?></span>
                            </td>
                            <td class="fs-8 text-nowrap">
                                <?php if (!empty($t['replied_datetime']) && in_array($t['status'], ['Completed', 'Closed'])): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold">
                                        <i class="fas fa-check-circle me-1"></i><?= date('d M Y, h:i A', strtotime($t['replied_datetime'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted fs-8">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap"><?= get_status_badge($t['status'] ?? 'New') ?></td>
                            <td class="text-nowrap"><?= get_tat_badge($t['tat_datetime'] ?? date('Y-m-d H:i:s'), $t['status'] ?? '') ?></td>
                            <td class="text-end text-nowrap">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="btn btn-outline-primary" title="View Query Ticket">
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
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary"></i>
                                    No Query Tickets found matching your active filter options.
                                </td>
                            </tr>
                        <?php endif; ?>
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
                    <h5 class="modal-title fw-bold"><i class="fas fa-clock me-2"></i>Reschedule Query Ticket <span id="reschedule_ticket_num"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Target Scheduled Date <span class="text-danger">*</span></label>
                        <input type="date" name="scheduled_date" id="reschedule_date_val" class="form-control fw-bold" min="<?= date('Y-m-d') ?>" required>
                        <small class="text-muted fs-8 d-block mt-1">Select today or a future date to postpone work on this query ticket.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Reason / Reschedule Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Awaiting client response"></textarea>
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
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-edit me-2"></i>Reassign Query Ticket <span id="reassign_ticket_num"></span></h5>
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
