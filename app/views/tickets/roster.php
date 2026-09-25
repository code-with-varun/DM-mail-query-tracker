<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-calendar-alt text-primary me-2"></i>Daily Roster & Operations Planner</h4>
            <p class="text-muted fs-7 mb-0">Daily scheduled work items + auto-rescheduled pending tickets from previous days</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="<?= base_url('roster') ?>" method="GET" class="d-flex align-items-center gap-2">
                <label class="form-label mb-0 fw-bold fs-7 text-muted text-nowrap">Planner Date:</label>
                <input type="date" name="date" class="form-control form-control-sm fw-bold text-primary" value="<?= htmlspecialchars($targetDate) ?>" onchange="this.form.submit();">
                <button type="submit" class="btn btn-sm btn-primary fw-bold px-3">Go</button>
            </form>
        </div>
    </div>

    <!-- Roster Overview Card -->
    <div class="row g-3 mb-4">
        <?php
            $todayStr = date('Y-m-d');
            $isToday = ($targetDate === $todayStr);
            $scheduledCount = count($tickets);
            $highPriority = count(array_filter($tickets, fn($t) => in_array(strtolower($t['priority']), ['high', 'critical'])));
            $pendingCarryover = count(array_filter($tickets, fn($t) => !empty($t['scheduled_date']) && $t['scheduled_date'] < $targetDate));
        ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-primary">
                <div class="card-body p-3">
                    <span class="text-uppercase text-muted fs-8 fw-bold">Scheduled Work Items</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1"><?= $scheduledCount ?> <span class="fs-7 text-muted font-normal">for <?= date('d M Y', strtotime($targetDate)) ?></span></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-danger">
                <div class="card-body p-3">
                    <span class="text-uppercase text-muted fs-8 fw-bold">High / Critical Priority</span>
                    <h3 class="fw-bold text-danger mb-0 mt-1"><?= $highPriority ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 border-warning">
                <div class="card-body p-3">
                    <span class="text-uppercase text-muted fs-8 fw-bold">Pending Auto-Carryover Items</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1"><?= $pendingCarryover ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Roster Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-list-check me-2 text-primary"></i>Roster Schedule (<?= date('D, d M Y', strtotime($targetDate)) ?>)</h6>
            <span class="badge bg-light text-dark border"><?= count($tickets) ?> Items</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th>Ticket #</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Sub-Activity</th>
                            <th>Assigned Employee</th>
                            <th>Stage</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Scheduled Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted fs-7">
                                <i class="fas fa-calendar-check text-success fs-4 d-block mb-2"></i>
                                No scheduled work items for this date.
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php foreach ($tickets as $t): ?>
                        <tr class="<?= (!empty($t['scheduled_date']) && $t['scheduled_date'] < $targetDate) ? 'table-warning bg-opacity-10' : '' ?>">
                            <td class="fw-bold">
                                <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="text-primary text-decoration-none fw-bold">
                                    <?= htmlspecialchars($t['ticket_number']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-<?= $t['ticket_type'] === 'Task Ticket' ? 'purple' : 'info' ?> bg-opacity-10 text-<?= $t['ticket_type'] === 'Task Ticket' ? 'purple' : 'info' ?> border border-<?= $t['ticket_type'] === 'Task Ticket' ? 'purple' : 'info' ?>">
                                    <?= htmlspecialchars($t['ticket_type']) ?>
                                </span>
                            </td>
                            <td class="text-truncate" style="max-width: 230px;" title="<?= htmlspecialchars($t['subject']) ?>">
                                <?= htmlspecialchars($t['subject']) ?>
                            </td>
                            <td class="fs-8 text-muted text-nowrap"><?= htmlspecialchars($t['sub_activity_name'] ?? 'General') ?></td>
                            <td class="fw-bold fs-8 text-dark text-nowrap">
                                <i class="fas fa-user-circle text-secondary me-1"></i><?= htmlspecialchars($t['allocated_user_name'] ?? 'Unassigned') ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($t['stage'] ?? 'Maker Phase') ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= strtolower($t['priority']) === 'high' || strtolower($t['priority']) === 'critical' ? 'danger' : 'secondary' ?>">
                                    <?= htmlspecialchars($t['priority']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $t['status'] === 'Completed' || $t['status'] === 'Closed' ? 'success' : 'warning text-dark' ?>">
                                    <?= htmlspecialchars($t['status']) ?>
                                </span>
                            </td>
                            <td class="fs-8 text-nowrap fw-bold text-dark">
                                <?php if (!empty($t['scheduled_date'])): ?>
                                    <?= date('d M Y', strtotime($t['scheduled_date'])) ?>
                                    <?php if ($t['scheduled_date'] < $todayStr): ?>
                                        <span class="badge bg-danger ms-1">Overdue Carryover</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Unscheduled</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="btn btn-outline-primary" title="View Ticket">
                                        <i class="fas fa-folder-open"></i>
                                    </a>
                                    <!-- Reschedule Modal Trigger -->
                                    <button type="button" class="btn btn-outline-warning text-dark btn-reschedule" 
                                            data-id="<?= $t['id'] ?>"
                                            data-ticket="<?= htmlspecialchars($t['ticket_number']) ?>"
                                            data-date="<?= $t['scheduled_date'] ?? date('Y-m-d') ?>"
                                            title="Postpone / Reschedule">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    <!-- Reassign Modal Trigger -->
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
                            <?php foreach ($users as $u): ?>
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
