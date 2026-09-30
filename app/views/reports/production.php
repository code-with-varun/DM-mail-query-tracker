<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-industry text-primary me-2"></i>Daywise Production Tracker</h4>
            <p class="text-muted fs-7 mb-0">Daily Opening Balance, New Task Inflow, Output Closures & Carryover Register</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('reports/production/export?' . http_build_query($filters)) ?>" class="btn btn-outline-success btn-sm fw-bold">
                <i class="fas fa-file-csv me-1"></i>Export CSV
            </a>
            <a href="<?= base_url('reports') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fas fa-arrow-left me-1"></i>Reports & Analytics
            </a>
        </div>
    </div>

    <!-- Executive KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-4 border-primary h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase fs-8">Total New Tasks</small>
                            <h3 class="fw-bold mb-0 text-primary mt-1"><?= number_format($kpis['total_new'] ?? 0) ?></h3>
                        </div>
                        <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-plus-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-4 border-success h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase fs-8">Total Closed Tasks</small>
                            <h3 class="fw-bold mb-0 text-success mt-1"><?= number_format($kpis['total_closed'] ?? 0) ?></h3>
                        </div>
                        <div class="p-3 rounded-circle bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-double fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-4 border-warning h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase fs-8">Current Pending (Closing)</small>
                            <h3 class="fw-bold mb-0 text-dark mt-1"><?= number_format($kpis['latest_pending'] ?? 0) ?></h3>
                        </div>
                        <div class="p-3 rounded-circle bg-warning bg-opacity-15 text-dark">
                            <i class="fas fa-hourglass-half fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm border-start border-4 border-info h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted fw-bold text-uppercase fs-8">Avg Daily Closure</small>
                            <h3 class="fw-bold mb-0 text-info mt-1"><?= number_format($kpis['avg_closed'] ?? 0, 1) ?> <small class="fs-8 text-muted">/ day</small></h3>
                        </div>
                        <div class="p-3 rounded-circle bg-info bg-opacity-10 text-info">
                            <i class="fas fa-chart-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Control Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="<?= base_url('reports/production') ?>" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-dark mb-1">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['start_date']) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-dark mb-1">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['end_date']) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-dark mb-1">Employee / Assignee</label>
                    <select name="allocated_to" class="form-select form-select-sm">
                        <option value="">All Employees (Overall)</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($filters['allocated_to'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['user_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-bold text-dark mb-1">Activity</label>
                    <select name="activity_id" class="form-select form-select-sm">
                        <option value="">All Activities</option>
                        <?php foreach ($activities as $act): ?>
                            <option value="<?= $act['id'] ?>" <?= ($filters['activity_id'] ?? '') == $act['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($act['activity_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold" title="Apply Filter"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?= base_url('reports/production') ?>" class="btn btn-sm btn-light border" title="Reset Filters"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Daywise Production Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-list-ol text-primary me-2"></i>Daywise Task Ledger & Output Breakdown</h6>
            <small class="text-muted">Formula: <code>Opening Balance + New Tasks - Closed Tasks = Pending Tasks</code></small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th class="text-center">Opening Balance (Task)</th>
                            <th class="text-center">New Tasks</th>
                            <th class="text-center">Closed Tasks</th>
                            <th class="text-center">Pending Tasks (Closing Balance)</th>
                            <th class="text-center">Total Workload</th>
                            <th class="text-center">Closure Efficiency</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($report)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No production data found for the selected date range and filter criteria.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($report as $r): ?>
                            <tr>
                                <td class="fw-bold text-nowrap">
                                    <i class="far fa-calendar-alt text-secondary me-2"></i><?= htmlspecialchars($r['date_formatted']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-bold fs-8"><?= htmlspecialchars($r['day_name']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary bg-opacity-10 text-dark border border-secondary border-opacity-20 px-3 py-2 fw-bold fs-7">
                                        <?= number_format($r['opening_balance']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 fw-bold fs-7">
                                        +<?= number_format($r['new_tasks']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 fw-bold fs-7">
                                        -<?= number_format($r['closed_tasks']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $r['pending_tasks'] > 0 ? 'bg-warning bg-opacity-15 text-dark border border-warning' : 'bg-light text-muted border' ?> px-3 py-2 fw-bold fs-7">
                                        <?= number_format($r['pending_tasks']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info px-3 py-2 fw-bold fs-7">
                                        <?= number_format($r['total_workload']) ?>
                                    </span>
                                </td>
                                <td class="text-center" style="min-width: 140px;">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-<?= $r['closure_rate'] >= 80 ? 'success' : ($r['closure_rate'] >= 50 ? 'info' : 'warning') ?>" role="progressbar" style="width: <?= min(100, $r['closure_rate']) ?>%;"></div>
                                        </div>
                                        <small class="fw-bold text-dark fs-8 me-1"><?= $r['closure_rate'] ?>%</small>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
