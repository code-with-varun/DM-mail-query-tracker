<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Employee Workspace</h4>
            <p class="text-muted fs-7 mb-0">My Assigned Queries, Tasks & SLA Priorities</p>
        </div>
        <div>
            <a href="<?= base_url('tickets/create') ?>" class="btn btn-primary fw-bold"><i class="fas fa-plus-circle me-2"></i>Create Query</a>
        </div>
    </div>

    <!-- Stat Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-white">
                <div class="card-body">
                    <div>
                        <div class="stat-label">Assigned Tickets</div>
                        <div class="stat-value text-primary"><?= $stats['open'] ?></div>
                    </div>
                    <div class="icon-box bg-primary bg-opacity-10 text-primary"><i class="fas fa-tasks"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-white">
                <div class="card-body">
                    <div>
                        <div class="stat-label">Overdue Tasks</div>
                        <div class="stat-value text-danger"><?= $stats['overdue'] ?></div>
                    </div>
                    <div class="icon-box bg-danger bg-opacity-10 text-danger"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-white">
                <div class="card-body">
                    <div>
                        <div class="stat-label">On Hold</div>
                        <div class="stat-value text-dark"><?= $stats['on_hold'] ?></div>
                    </div>
                    <div class="icon-box bg-dark bg-opacity-10 text-dark"><i class="fas fa-pause"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-white">
                <div class="card-body">
                    <div>
                        <div class="stat-label">Completed</div>
                        <div class="stat-value text-success"><?= $stats['closed'] ?></div>
                    </div>
                    <div class="icon-box bg-success bg-opacity-10 text-success"><i class="fas fa-check"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Tickets Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-list text-primary me-2"></i>My Work Queue</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket #</th>
                            <th>Received</th>
                            <th>Subject</th>
                            <th>Activity</th>
                            <th>Status</th>
                            <th>TAT SLA</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_tickets as $t): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($t['ticket_number']) ?></td>
                            <td><?= format_datetime($t['received_datetime']) ?></td>
                            <td>
                                <div class="fw-bold fs-7"><?= htmlspecialchars($t['subject']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($t['from_address']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($t['activity_name'] ?? 'N/A') ?></td>
                            <td><?= get_status_badge($t['status']) ?></td>
                            <td><?= get_tat_badge($t['tat_datetime'], $t['status']) ?></td>
                            <td>
                                <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="btn btn-sm btn-primary"><i class="fas fa-pencil-alt me-1"></i> Update</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
