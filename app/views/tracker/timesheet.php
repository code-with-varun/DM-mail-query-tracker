<div class="container-fluid px-4 py-4">
    <!-- Top Filter & Action Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-business-time text-primary me-2"></i>Daily Timesheet & Effort Tracker
            </h4>
            <p class="text-muted fs-7 mb-0">Track daily task hours, process effort allocation, and operational utilization</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#timesheetModal">
                <i class="fas fa-plus-circle me-1"></i>Log Timesheet Entry
            </button>
        </div>
    </div>

    <?php
        $totalHours = 0;
        $totalDays = count(array_unique(array_column($logs, 'log_date')));
        foreach ($logs as $l) {
            $totalHours += (float)$l['hours_spent'];
        }
        $avgDailyHours = $totalDays > 0 ? round($totalHours / $totalDays, 1) : 0;
        $expectedHours = max(1, $totalDays * 8.0);
        $adherencePct = min(100, round(($totalHours / $expectedHours) * 100));
    ?>

    <!-- Stat Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fs-8 text-muted fw-bold text-uppercase">Total Logged Effort</span>
                            <h3 class="fw-bold text-primary mb-0 mt-1"><?= number_format($totalHours, 1) ?> <span class="fs-7 fw-normal text-muted">hrs</span></h3>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3">
                            <i class="fas fa-clock fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fs-8 text-muted fw-bold text-uppercase">Logged Days</span>
                            <h3 class="fw-bold text-success mb-0 mt-1"><?= $totalDays ?> <span class="fs-7 fw-normal text-muted">shifts</span></h3>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3">
                            <i class="fas fa-calendar-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-info border-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fs-8 text-muted fw-bold text-uppercase">Avg Daily Effort</span>
                            <h3 class="fw-bold text-info mb-0 mt-1"><?= $avgDailyHours ?> <span class="fs-7 fw-normal text-muted">hrs/day</span></h3>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 text-info p-3">
                            <i class="fas fa-chart-line fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4 h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fs-8 text-muted fw-bold text-uppercase">Timesheet Adherence</span>
                            <h3 class="fw-bold text-warning mb-0 mt-1"><?= $adherencePct ?>%</h3>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3">
                            <i class="fas fa-award fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= base_url('tracker/timesheet') ?>" method="GET" class="row g-2 align-items-center">
                <?php if (is_super_admin() || is_senior_management() || is_manager() || is_team_lead()): ?>
                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold mb-1">Employee Downline</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">-- All Downline Members --</option>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= (!empty($filters['user_id']) && $filters['user_id'] == $emp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['user_code']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold mb-1">Project / Team</label>
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">-- All Projects --</option>
                        <?php foreach ($projects as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= (!empty($filters['project_id']) && $filters['project_id'] == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['project_name']) ?> (<?= htmlspecialchars($p['code']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-8 fw-bold mb-1">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= $filters['start_date'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-8 fw-bold mb-1">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?= $filters['end_date'] ?? '' ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold w-100">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                    <a href="<?= base_url('tracker/timesheet') ?>" class="btn btn-light btn-sm border" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Timesheet Records Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Project / Team</th>
                            <th>Activity</th>
                            <th>Hours Logged</th>
                            <th>Task Details</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-clipboard-list fs-3 mb-2 d-block text-secondary"></i>
                                No timesheet entries found for the selected filter criteria.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= format_date($l['log_date']) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($l['full_name']) ?></div>
                                <div class="fs-9 text-muted"><i class="fas fa-id-badge me-1"></i><?= htmlspecialchars($l['user_code']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                    <i class="fas fa-project-diagram me-1"></i><?= htmlspecialchars($l['project_name'] ?? 'General Ops') ?>
                                </span>
                            </td>
                            <td>
                                <div class="fs-8 fw-bold text-dark"><?= htmlspecialchars($l['activity_name'] ?? 'Operations') ?></div>
                                <?php if (!empty($l['sub_activity_name'])): ?>
                                <div class="fs-9 text-muted"><?= htmlspecialchars($l['sub_activity_name']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $hrs = (float)$l['hours_spent'];
                                    $badgeColor = $hrs >= 8.0 ? 'bg-success' : ($hrs >= 4.0 ? 'bg-warning text-dark' : 'bg-danger');
                                ?>
                                <span class="badge <?= $badgeColor ?> fs-7 px-2 py-1 fw-bold">
                                    <i class="fas fa-clock me-1"></i><?= number_format($hrs, 1) ?> hrs
                                </span>
                            </td>
                            <td class="fs-8 text-dark" style="max-width: 300px;">
                                <?= htmlspecialchars($l['task_description']) ?>
                            </td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                    <i class="fas fa-check-circle me-1"></i><?= htmlspecialchars($l['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($l['user_id'] == $_SESSION['user_id'] || is_super_admin() || is_manager() || is_team_lead()): ?>
                                <a href="<?= base_url('tracker/timesheet/delete/' . $l['id']) ?>" class="btn btn-outline-danger btn-sm p-1 px-2" title="Delete Entry" onclick="return confirm('Are you sure you want to delete this timesheet entry?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                                <?php endif; ?>
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

<!-- Modal: Log Timesheet Effort -->
<div class="modal fade" id="timesheetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tracker/timesheet/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-business-time me-2"></i>Log Timesheet Entry</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <?php if (is_super_admin() || is_senior_management() || is_manager() || is_team_lead()): ?>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Select Employee <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select" required>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= $emp['id'] == $_SESSION['user_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['user_code']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php else: ?>
                    <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
                    <?php endif; ?>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Log Date <span class="text-danger">*</span></label>
                            <input type="date" name="log_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Hours Spent <span class="text-danger">*</span></label>
                            <input type="number" step="0.25" min="0.5" max="24" name="hours_spent" class="form-control" value="8.0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Project / Team</label>
                        <select name="project_id" class="form-select">
                            <option value="">-- None / General Ops --</option>
                            <?php foreach ($projects as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['project_name']) ?> (<?= htmlspecialchars($p['code']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Activity</label>
                        <select name="activity_id" class="form-select">
                            <option value="">-- None / Operations Processing --</option>
                            <?php foreach ($activities as $a): ?>
                            <option value="<?= $a['id'] ?>">
                                <?= htmlspecialchars($a['activity_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Task Details / Work Description <span class="text-danger">*</span></label>
                        <textarea name="task_description" class="form-control" rows="3" placeholder="Describe the invoices processed, QC checks, email client communication, or operations tasks completed..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Submit Timesheet</button>
                </div>
            </form>
        </div>
    </div>
</div>
