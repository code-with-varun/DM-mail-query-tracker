<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">Team Lead / Admin Dashboard</h4>
            <p class="text-muted fs-7 mb-0">Team Workload Distribution, Employee SLA Compliance & Quality Analytics</p>
        </div>
        <div>
            <a href="<?= base_url('tickets/create') ?>" class="btn btn-primary fw-bold btn-sm"><i class="fas fa-plus-circle me-1"></i>New Ticket</a>
        </div>
    </div>

    <!-- Interactive Dashboard Slicers Bar -->
    <div class="card border-0 shadow-sm mb-4 bg-white">
        <div class="card-body p-3">
            <form action="<?= base_url('dashboard') ?>" method="GET" id="slicerForm" class="row g-2 align-items-end">
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-building text-primary me-1"></i>Division Slicer</label>
                    <select name="division_id" class="form-select form-select-sm">
                        <option value="">All Divisions</option>
                        <?php foreach ($slicer_divisions as $div): ?>
                            <option value="<?= $div['id'] ?>" <?= ($filters['division_id'] ?? '') == $div['id'] ? 'selected' : '' ?>><?= htmlspecialchars($div['division_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-sitemap text-danger me-1"></i>Activity Slicer</label>
                    <select name="activity_id" class="form-select form-select-sm">
                        <option value="">All Activities</option>
                        <?php foreach ($slicer_activities as $act): ?>
                            <option value="<?= $act['id'] ?>" <?= ($filters['activity_id'] ?? '') == $act['id'] ? 'selected' : '' ?>><?= htmlspecialchars($act['activity_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-flag text-warning me-1"></i>Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        <?php foreach (['Critical', 'High', 'Medium', 'Low'] as $prio): ?>
                            <option value="<?= $prio ?>" <?= ($filters['priority'] ?? '') === $prio ? 'selected' : '' ?>><?= $prio ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-calendar-alt text-secondary me-1"></i>Start Date</label>
                    <input type="date" name="start_date" id="slicer_start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>">
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-calendar-check text-secondary me-1"></i>End Date</label>
                    <input type="date" name="end_date" id="slicer_end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['end_date'] ?? '') ?>">
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold"><i class="fas fa-filter me-1"></i>Filter Team Data</button>
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light border px-2" title="Reset Slicers"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Executive KPI Cards Row -->
    <?php 
        $tot = intval($stats['total'] ?? 0);
        $overdue = intval($stats['overdue'] ?? 0);
        $slaPct = ($tot > 0) ? max(0, round((($tot - $overdue) / $tot) * 100, 1)) : 100;
    ?>
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card stat-card-executive border-start border-4 border-primary">
                <div class="card-body">
                    <div class="stat-header">
                        <span class="stat-label">Team Volume</span>
                        <div class="icon-box-sm bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-value text-dark"><?= number_format($stats['total']) ?></div>
                    <div>
                        <span class="stat-badge bg-primary bg-opacity-10 text-primary border border-primary">
                            <i class="fas fa-list-alt me-1"></i>Team Queue
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card stat-card-executive border-start border-4 border-info">
                <div class="card-body">
                    <div class="stat-header">
                        <span class="stat-label">Active / Pending</span>
                        <div class="icon-box-sm bg-info bg-opacity-10 text-info">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                    </div>
                    <div class="stat-value text-info"><?= number_format($stats['open']) ?></div>
                    <div>
                        <span class="stat-badge bg-info bg-opacity-10 text-info border border-info">
                            <i class="fas fa-tasks me-1"></i>In Work
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card stat-card-executive border-start border-4 border-danger">
                <div class="card-body">
                    <div class="stat-header">
                        <span class="stat-label">Overdue SLA</span>
                        <div class="icon-box-sm bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="stat-value text-danger"><?= number_format($stats['overdue']) ?></div>
                    <div>
                        <span class="stat-badge bg-danger bg-opacity-10 text-danger border border-danger">
                            <i class="fas fa-bell me-1"></i>Breached SLA
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card stat-card-executive border-start border-4 border-warning">
                <div class="card-body">
                    <div class="stat-header">
                        <span class="stat-label">On Hold</span>
                        <div class="icon-box-sm bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                    </div>
                    <div class="stat-value text-warning-dark" style="color: #d97706;"><?= number_format($stats['on_hold']) ?></div>
                    <div>
                        <span class="stat-badge bg-warning bg-opacity-10 text-warning-dark border border-warning">
                            <i class="fas fa-clock me-1"></i>Pending Review
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card stat-card-executive border-start border-4 border-purple">
                <div class="card-body">
                    <div class="stat-header">
                        <span class="stat-label">Completed</span>
                        <div class="icon-box-sm bg-purple-subtle">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-value text-purple"><?= number_format($stats['closed']) ?></div>
                    <div>
                        <span class="stat-badge bg-purple-subtle border border-purple">
                            <i class="fas fa-check-double me-1"></i>Resolved
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card stat-card-executive border-start border-4 border-<?= $slaPct >= 90 ? 'success' : ($slaPct >= 75 ? 'warning' : 'danger') ?>">
                <div class="card-body">
                    <div class="stat-header">
                        <span class="stat-label">SLA Compliance</span>
                        <div class="icon-box-sm bg-<?= $slaPct >= 90 ? 'success' : ($slaPct >= 75 ? 'warning' : 'danger') ?> bg-opacity-10 text-<?= $slaPct >= 90 ? 'success' : ($slaPct >= 75 ? 'warning' : 'danger') ?>">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                    <div class="stat-value text-<?= $slaPct >= 90 ? 'success' : ($slaPct >= 75 ? 'warning' : 'danger') ?>"><?= $slaPct ?>%</div>
                    <div>
                        <span class="stat-badge bg-light text-muted border">
                            <i class="fas fa-bullseye me-1"></i>Team SLA
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Grid 1: Employeewise & Activitywise -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-users-cog text-primary me-2"></i>Team Employee Capacity & Workload</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="adminEmployeeChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sitemap text-danger me-2"></i>Activity Ticket Distribution</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="adminActivityChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Grid 2: Quality & Status -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-award text-success me-2"></i>Quality & SLA Breach Performance</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="adminQualityChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie text-warning me-2"></i>Status Breakdown</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="adminStatusChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const empData = <?= json_encode($employee_breakdown ?? []) ?>;
    new Chart(document.getElementById('adminEmployeeChart'), {
        type: 'bar',
        data: {
            labels: empData.length ? empData.map(i => i.employee_name) : ['No Staff'],
            datasets: [{
                label: 'Tickets Assigned',
                data: empData.length ? empData.map(i => i.count) : [0],
                backgroundColor: '#0d6efd',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    const actData = <?= json_encode($activity_breakdown ?? []) ?>;
    new Chart(document.getElementById('adminActivityChart'), {
        type: 'bar',
        data: {
            labels: actData.length ? actData.map(i => i.activity_name) : ['General'],
            datasets: [{
                label: 'Volume',
                data: actData.length ? actData.map(i => i.count) : [0],
                backgroundColor: '#dc3545',
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: { x: { beginAtZero: true } }
        }
    });

    const qualityData = <?= json_encode($quality_sla_breakdown ?? []) ?>;
    new Chart(document.getElementById('adminQualityChart'), {
        type: 'doughnut',
        data: {
            labels: qualityData.map(i => i.metric),
            datasets: [{
                data: qualityData.map(i => i.count),
                backgroundColor: ['#198754', '#0d6efd', '#ffc107', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    const statusData = <?= json_encode($status_breakdown ?? []) ?>;
    new Chart(document.getElementById('adminStatusChart'), {
        type: 'doughnut',
        data: {
            labels: statusData.length ? statusData.map(i => i.status) : ['No Data'],
            datasets: [{
                data: statusData.length ? statusData.map(i => i.count) : [1],
                backgroundColor: ['#0d6efd', '#6610f2', '#ffc107', '#fd7e14', '#dc3545', '#198754', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
    // Date Slicer Logic & Validation
    const startDateInput = document.getElementById('slicer_start_date');
    const endDateInput = document.getElementById('slicer_end_date');
    const slicerForm = document.getElementById('slicerForm');

    if (startDateInput && endDateInput) {
        if (startDateInput.value) {
            endDateInput.min = startDateInput.value;
        }
        if (endDateInput.value) {
            startDateInput.max = endDateInput.value;
        }

        startDateInput.addEventListener('change', function() {
            if (this.value) {
                endDateInput.min = this.value;
                if (endDateInput.value && endDateInput.value < this.value) {
                    endDateInput.value = this.value;
                }
            } else {
                endDateInput.removeAttribute('min');
            }
        });

        endDateInput.addEventListener('change', function() {
            if (this.value) {
                startDateInput.max = this.value;
                if (startDateInput.value && startDateInput.value > this.value) {
                    startDateInput.value = this.value;
                }
            } else {
                startDateInput.removeAttribute('max');
            }
        });

        if (slicerForm) {
            slicerForm.addEventListener('submit', function(e) {
                if (startDateInput.value && endDateInput.value && endDateInput.value < startDateInput.value) {
                    e.preventDefault();
                    alert('End Date cannot be earlier than Start Date.');
                    endDateInput.focus();
                }
            });
        }
    }
});
</script>
