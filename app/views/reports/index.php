<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Reports & SLA Analytics</h4>
            <p class="text-muted fs-7 mb-0">Query resolution reports, employee SLA performance, and CSV exports</p>
        </div>
        <div>
            <a href="<?= base_url('reports/export?' . http_build_query($filters)) ?>" class="btn btn-success fw-bold">
                <i class="fas fa-file-csv me-2"></i>Export CSV / Excel
            </a>
        </div>
    </div>

    <!-- Filter Form Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= base_url('reports') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-2">
                    <label class="form-label fs-8 fw-bold mb-1">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['start_date']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-8 fw-bold mb-1">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['end_date']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-8 fw-bold mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <?php foreach (['New', 'Assigned', 'In Progress', 'Pending', 'On Hold', 'Completed', 'Closed'] as $st): ?>
                            <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold mb-1">Activity</label>
                    <select name="activity_id" class="form-select form-select-sm">
                        <option value="">All Activities</option>
                        <?php foreach ($activities as $act): ?>
                            <option value="<?= $act['id'] ?>" <?= ($filters['activity_id'] ?? '') == $act['id'] ? 'selected' : '' ?>><?= htmlspecialchars($act['activity_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-1 mt-4">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold"><i class="fas fa-search me-1"></i>Generate Report</button>
                    <a href="<?= base_url('reports') ?>" class="btn btn-sm btn-light border"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Employee Performance Metrics Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Employee SLA Compliance Summary</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee Code</th>
                            <th>Full Name</th>
                            <th>Total Assigned</th>
                            <th>Completed</th>
                            <th>Within SLA</th>
                            <th>Overdue</th>
                            <th>SLA Compliance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($performance as $p): ?>
                        <?php 
                            $compliance = ($p['completed_count'] > 0) 
                                ? round(($p['within_sla_count'] / $p['completed_count']) * 100, 1) 
                                : 100;
                        ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($p['user_code']) ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($p['full_name']) ?></td>
                            <td><?= $p['total_assigned'] ?></td>
                            <td class="text-success fw-bold"><?= $p['completed_count'] ?></td>
                            <td class="text-primary"><?= $p['within_sla_count'] ?></td>
                            <td class="text-danger fw-bold"><?= $p['overdue_count'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar bg-<?= $compliance >= 90 ? 'success' : ($compliance >= 75 ? 'warning' : 'danger') ?>" style="width: <?= $compliance ?>%;"></div>
                                    </div>
                                    <span class="fw-bold fs-8"><?= $compliance ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed Ticket Results Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-list text-secondary me-2"></i>Report Detailed Results (<?= count($tickets) ?> Records)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket #</th>
                            <th>Subject</th>
                            <th>Activity</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>TAT SLA</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($t['ticket_number']) ?></td>
                            <td>
                                <div class="fw-bold fs-7"><?= htmlspecialchars($t['subject']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($t['from_address']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($t['activity_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($t['allocated_user_name'] ?? 'Unassigned') ?></td>
                            <td><?= get_status_badge($t['status']) ?></td>
                            <td><?= get_tat_badge($t['tat_datetime'], $t['status']) ?></td>
                            <td class="fs-8"><?= format_datetime($t['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
