<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">Super Admin Dashboard & Executive Analytics</h4>
            <p class="text-muted fs-7 mb-0">Deep SLA Analytics, Employee Workload, Activity Volume & Quality Slicers</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('reports') ?>" class="btn btn-outline-secondary fw-bold btn-sm"><i class="fas fa-chart-line me-1"></i>SLA Reports</a>
            <a href="<?= base_url('tickets/create') ?>" class="btn btn-primary fw-bold btn-sm"><i class="fas fa-plus-circle me-1"></i>New Ticket</a>
        </div>
    </div>

    <!-- Interactive Dashboard Slicers Bar -->
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body p-3">
            <form action="<?= base_url('dashboard') ?>" method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-building me-1"></i>Division Slicer</label>
                    <select name="division_id" class="form-select form-select-sm">
                        <option value="">All Divisions</option>
                        <?php foreach ($slicer_divisions as $div): ?>
                            <option value="<?= $div['id'] ?>" <?= ($filters['division_id'] ?? '') == $div['id'] ? 'selected' : '' ?>><?= htmlspecialchars($div['division_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-sitemap me-1"></i>Activity Slicer</label>
                    <select name="activity_id" class="form-select form-select-sm">
                        <option value="">All Activities</option>
                        <?php foreach ($slicer_activities as $act): ?>
                            <option value="<?= $act['id'] ?>" <?= ($filters['activity_id'] ?? '') == $act['id'] ? 'selected' : '' ?>><?= htmlspecialchars($act['activity_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-user-tag me-1"></i>Employee Slicer</label>
                    <select name="allocated_to" class="form-select form-select-sm">
                        <option value="">All Assignees</option>
                        <?php foreach ($slicer_employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= ($filters['allocated_to'] ?? '') == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-flag me-1"></i>Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        <?php foreach (['Critical', 'High', 'Medium', 'Low'] as $prio): ?>
                            <option value="<?= $prio ?>" <?= ($filters['priority'] ?? '') === $prio ? 'selected' : '' ?>><?= $prio ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-calendar me-1"></i>Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>">
                </div>

                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold"><i class="fas fa-filter me-1"></i>Slice Data</button>
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light border" title="Reset Slicers"><i class="fas fa-undo"></i></a>
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
        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">Total Sliced Volume</div>
                    <div class="stat-value text-dark fw-bold fs-4"><?= $stats['total'] ?></div>
                    <div class="fs-8 text-primary mt-1"><i class="fas fa-layer-group me-1"></i>All Tickets</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">Active Open</div>
                    <div class="stat-value text-primary fw-bold fs-4"><?= $stats['open'] ?></div>
                    <div class="fs-8 text-info mt-1"><i class="fas fa-spinner fa-spin me-1"></i>In Work</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">Overdue SLA</div>
                    <div class="stat-value text-danger fw-bold fs-4"><?= $stats['overdue'] ?></div>
                    <div class="fs-8 text-danger mt-1"><i class="fas fa-exclamation-triangle me-1"></i>Breached SLA</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">On-Hold</div>
                    <div class="stat-value text-warning fw-bold fs-4"><?= $stats['on_hold'] ?></div>
                    <div class="fs-8 text-warning mt-1"><i class="fas fa-pause-circle me-1"></i>Pending Release</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">SLA Compliance</div>
                    <div class="stat-value text-<?= $slaPct >= 90 ? 'success' : ($slaPct >= 75 ? 'warning' : 'danger') ?> fw-bold fs-4"><?= $slaPct ?>%</div>
                    <div class="fs-8 text-muted mt-1"><i class="fas fa-shield-alt me-1"></i>Target &ge; 90%</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">Completed</div>
                    <div class="stat-value text-success fw-bold fs-4"><?= $stats['closed'] ?></div>
                    <div class="fs-8 text-success mt-1"><i class="fas fa-check-circle me-1"></i>Resolved</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Grid 1: Employeewise & Activitywise -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-users-cog text-primary me-2"></i>Employeewise Workload Analytics</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="employeeChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sitemap text-danger me-2"></i>Activitywise Ticket Volume</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="activityChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Grid 2: Quality & SLA, Ticket Types, Status Breakdown -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-award text-success me-2"></i>Quality & SLA Breach Analytics</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="qualitySlaChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-tasks text-info me-2"></i>Task Count vs Query Type Breakdown</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="ticketTypeChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie text-warning me-2"></i>Status Matrix</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="statusChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Grid 3: Division Distribution & Monthly Inflow Trend -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-building text-primary me-2"></i>Division Distribution</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="divisionChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-area text-success me-2"></i>Monthly Ticket Volume Trend</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="monthlyChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Employeewise Workload Bar Chart
    const empData = <?= json_encode($employee_breakdown ?? []) ?>;
    new Chart(document.getElementById('employeeChart'), {
        type: 'bar',
        data: {
            labels: empData.length ? empData.map(i => i.employee_name) : ['No Assignees'],
            datasets: [{
                label: 'Assigned Tickets',
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

    // 2. Activitywise Horizontal Bar Chart
    const actData = <?= json_encode($activity_breakdown ?? []) ?>;
    new Chart(document.getElementById('activityChart'), {
        type: 'bar',
        data: {
            labels: actData.length ? actData.map(i => i.activity_name) : ['No Activities'],
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

    // 3. Quality & SLA Breakdown Doughnut Chart
    const qualityData = <?= json_encode($quality_sla_breakdown ?? []) ?>;
    new Chart(document.getElementById('qualitySlaChart'), {
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

    // 4. Ticket Type Breakdown Doughnut Chart
    const typeData = <?= json_encode($ticket_type_breakdown ?? []) ?>;
    new Chart(document.getElementById('ticketTypeChart'), {
        type: 'doughnut',
        data: {
            labels: typeData.length ? typeData.map(i => i.ticket_type) : ['Query Ticket'],
            datasets: [{
                data: typeData.length ? typeData.map(i => i.count) : [0],
                backgroundColor: ['#0d6efd', '#fd7e14', '#6610f2']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 5. Status Doughnut Chart
    const statusData = <?= json_encode($status_breakdown ?? []) ?>;
    new Chart(document.getElementById('statusChart'), {
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

    // 6. Division Bar Chart
    const divisionData = <?= json_encode($division_breakdown ?? []) ?>;
    new Chart(document.getElementById('divisionChart'), {
        type: 'bar',
        data: {
            labels: divisionData.length ? divisionData.map(i => i.division_name) : ['General'],
            datasets: [{
                label: 'Tickets',
                data: divisionData.length ? divisionData.map(i => i.count) : [0],
                backgroundColor: '#6610f2',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });

    // 7. Monthly Trend Area Line Chart
    const monthlyData = <?= json_encode($monthly_trend ?? []) ?>;
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyData.length ? monthlyData.map(i => i.month_name) : ['Current Month'],
            datasets: [{
                label: 'Inflow Volume',
                data: monthlyData.length ? monthlyData.map(i => i.count) : [0],
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.15)',
                fill: true,
                tension: 0.35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
