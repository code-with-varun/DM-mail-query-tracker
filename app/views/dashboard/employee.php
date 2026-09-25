<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-bold mb-1">Employee Workspace & Analytics</h4>
            <p class="text-muted fs-7 mb-0">Personal Workload, Activity Volume, SLA Compliance & Slicers</p>
        </div>
        <div>
            <a href="<?= base_url('tickets/create') ?>" class="btn btn-primary fw-bold btn-sm"><i class="fas fa-plus-circle me-1"></i>Create Ticket</a>
        </div>
    </div>

    <!-- Interactive Dashboard Slicers Bar -->
    <div class="card border-0 shadow-sm mb-4 bg-light">
        <div class="card-body p-3">
            <form action="<?= base_url('dashboard') ?>" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-sitemap me-1"></i>Activity Slicer</label>
                    <select name="activity_id" class="form-select form-select-sm">
                        <option value="">All Activities</option>
                        <?php foreach ($slicer_activities as $act): ?>
                            <option value="<?= $act['id'] ?>" <?= ($filters['activity_id'] ?? '') == $act['id'] ? 'selected' : '' ?>><?= htmlspecialchars($act['activity_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-flag me-1"></i>Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        <?php foreach (['Critical', 'High', 'Medium', 'Low'] as $prio): ?>
                            <option value="<?= $prio ?>" <?= ($filters['priority'] ?? '') === $prio ? 'selected' : '' ?>><?= $prio ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-muted mb-1"><i class="fas fa-calendar me-1"></i>Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['start_date'] ?? '') ?>">
                </div>

                <div class="col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold"><i class="fas fa-filter me-1"></i>Filter My Work</button>
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light border" title="Reset Slicers"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stat Cards Row -->
    <?php 
        $tot = intval($stats['total'] ?? 0);
        $overdue = intval($stats['overdue'] ?? 0);
        $slaPct = ($tot > 0) ? max(0, round((($tot - $overdue) / $tot) * 100, 1)) : 100;
    ?>
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">My Total Queue</div>
                    <div class="stat-value text-dark fw-bold fs-4"><?= $stats['total'] ?></div>
                    <div class="fs-8 text-primary mt-1"><i class="fas fa-list-alt me-1"></i>Assigned Queue</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">Active Open</div>
                    <div class="stat-value text-primary fw-bold fs-4"><?= $stats['open'] ?></div>
                    <div class="fs-8 text-info mt-1"><i class="fas fa-tasks me-1"></i>In Work</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">Overdue Tasks</div>
                    <div class="stat-value text-danger fw-bold fs-4"><?= $stats['overdue'] ?></div>
                    <div class="fs-8 text-danger mt-1"><i class="fas fa-exclamation-triangle me-1"></i>Urgent Action</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">On Hold</div>
                    <div class="stat-value text-warning fw-bold fs-4"><?= $stats['on_hold'] ?></div>
                    <div class="fs-8 text-warning mt-1"><i class="fas fa-pause me-1"></i>Pending Release</div>
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

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">SLA Compliance</div>
                    <div class="stat-value text-<?= $slaPct >= 90 ? 'success' : ($slaPct >= 75 ? 'warning' : 'danger') ?> fw-bold fs-4"><?= $slaPct ?>%</div>
                    <div class="fs-8 text-muted mt-1"><i class="fas fa-shield-alt me-1"></i>Personal Score</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts Grid -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>Status Breakdown</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="empStatusChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-award text-success me-2"></i>Quality SLA Score</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="empQualityChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-line text-success me-2"></i>Monthly Work Inflow</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="empTrendChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusData = <?= json_encode($status_breakdown ?? []) ?>;
    new Chart(document.getElementById('empStatusChart'), {
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

    const qualityData = <?= json_encode($quality_sla_breakdown ?? []) ?>;
    new Chart(document.getElementById('empQualityChart'), {
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

    const trendData = <?= json_encode($monthly_trend ?? []) ?>;
    new Chart(document.getElementById('empTrendChart'), {
        type: 'line',
        data: {
            labels: trendData.length ? trendData.map(i => i.month_name) : ['Current Month'],
            datasets: [{
                label: 'Work Inflow',
                data: trendData.length ? trendData.map(i => i.count) : [0],
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                fill: true,
                tension: 0.3
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
