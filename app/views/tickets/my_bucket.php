<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-user-clock text-primary me-2"></i>My Bucket</h4>
            <p class="text-muted fs-7 mb-0">Active tickets & tasks assigned to you across Maker, Checker, and Delivery stages</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('tickets/create') ?>" class="btn btn-primary btn-sm fw-bold">
                <i class="fas fa-plus-circle me-1"></i>Create Mail Ticket
            </a>
            <a href="<?= base_url('tasks/create') ?>" class="btn btn-outline-primary btn-sm fw-bold">
                <i class="fas fa-tasks me-1"></i>New Internal Task
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <?php
        $makerCount = count(array_filter($tickets, fn($t) => ($t['stage'] ?? '') === 'Maker Phase'));
        $checkerCount = count(array_filter($tickets, fn($t) => ($t['stage'] ?? '') === 'Checker Phase'));
        $deliveryCount = count(array_filter($tickets, fn($t) => ($t['stage'] ?? '') === 'Delivery Phase'));
        $totalBucket = count($tickets);
    ?>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fs-8 fw-bold">Maker Queue</span>
                            <h3 class="fw-bold text-primary mb-0 mt-1"><?= $makerCount ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary">
                            <i class="fas fa-pencil-alt fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fs-8 fw-bold">Checker Review</span>
                            <h3 class="fw-bold text-warning mb-0 mt-1"><?= $checkerCount ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                            <i class="fas fa-user-check fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fs-8 fw-bold">Ready For Delivery</span>
                            <h3 class="fw-bold text-success mb-0 mt-1"><?= $deliveryCount ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                            <i class="fas fa-paper-plane fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase text-muted fs-8 fw-bold">Total In My Bucket</span>
                            <h3 class="fw-bold text-info mb-0 mt-1"><?= $totalBucket ?></h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle text-info">
                            <i class="fas fa-user-clock fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= base_url('tickets/my-bucket') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ticket #, subject, sender email..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <select name="stage" class="form-select form-select-sm">
                        <option value="">All Stages</option>
                        <option value="Maker Phase" <?= ($filters['stage'] ?? '') === 'Maker Phase' ? 'selected' : '' ?>>Maker Phase</option>
                        <option value="Checker Phase" <?= ($filters['stage'] ?? '') === 'Checker Phase' ? 'selected' : '' ?>>Checker Phase</option>
                        <option value="Delivery Phase" <?= ($filters['stage'] ?? '') === 'Delivery Phase' ? 'selected' : '' ?>>Delivery Phase</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?= base_url('tickets/my-bucket') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bucket Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th>Ticket #</th>
                            <th>Type</th>
                            <th>Stage & Bucket Owner</th>
                            <th>Subject</th>
                            <th>Sub-Activity</th>
                            <th>Priority</th>
                            <th>Scheduled Date</th>
                            <th>SLA TAT</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td class="fw-bold">
                                <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="text-primary text-decoration-none fw-bold">
                                    <?= htmlspecialchars($t['ticket_number']) ?>
                                </a>
                            </td>
                             <td>
                                <span class="badge <?= ($t['ticket_type'] === 'Task Ticket' || !empty($t['is_task'])) ? 'badge-task-ticket' : 'badge-query-ticket' ?>">
                                    <?= htmlspecialchars($t['ticket_type'] ?? ($t['is_task'] ? 'Task Ticket' : 'Query Ticket')) ?>
                                </span>
                            </td>
                            <td>
                                <?php $bInfo = get_current_bucket_info($t); ?>
                                <span class="badge <?= $bInfo['badge_class'] ?> fw-bold me-1"><?= htmlspecialchars($bInfo['role_label']) ?></span>
                                <span class="fw-bold fs-8 text-dark d-block mt-1"><?= htmlspecialchars($bInfo['user_name']) ?></span>
                                <?php if (!empty($bInfo['user_code'])): ?>
                                    <small class="text-muted fs-8">(ID: <?= htmlspecialchars($bInfo['user_code']) ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($t['subject']) ?>">
                                <?= htmlspecialchars($t['subject']) ?>
                            </td>
                            <td class="fs-8 text-muted text-nowrap"><?= htmlspecialchars($t['sub_activity_name'] ?? 'General') ?></td>
                            <td>
                                <span class="badge bg-<?= strtolower($t['priority']) === 'high' || strtolower($t['priority']) === 'critical' ? 'danger' : 'secondary' ?>">
                                    <?= htmlspecialchars($t['priority']) ?>
                                </span>
                            </td>
                            <td class="fs-8 text-nowrap">
                                <?= !empty($t['scheduled_date']) ? date('d M Y', strtotime($t['scheduled_date'])) : '<span class="text-muted">Today</span>' ?>
                            </td>
                            <td class="fs-8 text-nowrap">
                                <?= format_datetime($t['tat_datetime']) ?>
                            </td>
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
