<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Employee Workspace</h4>
            <p class="text-muted fs-7 mb-0">My Assigned Queries, Tasks, Priority Actions & SLA Status</p>
        </div>
        <div>
            <a href="<?= base_url('tickets/create') ?>" class="btn btn-primary fw-bold btn-sm"><i class="fas fa-plus-circle me-1"></i>Create Ticket</a>
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
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">My Total</div>
                    <div class="stat-value text-dark fw-bold fs-4"><?= $stats['total'] ?></div>
                    <div class="fs-8 text-primary mt-1"><i class="fas fa-list-alt me-1"></i>Assigned Queue</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">Active / Pending</div>
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

    <!-- Interactive Charts Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>My Status Breakdown</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="empStatusChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie text-danger me-2"></i>My Priority Distribution</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="empPriorityChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-line text-success me-2"></i>Monthly Work Trend</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="empTrendChart" style="max-height: 240px;"></canvas>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusData = <?= json_encode($status_breakdown ?? []) ?>;
    const statusLabels = statusData.map(item => item.status);
    const statusCounts = statusData.map(item => item.count);
    
    new Chart(document.getElementById('empStatusChart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels.length ? statusLabels : ['No Data'],
            datasets: [{
                data: statusCounts.length ? statusCounts : [1],
                backgroundColor: ['#0d6efd', '#6610f2', '#ffc107', '#fd7e14', '#dc3545', '#198754', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    const priorityData = <?= json_encode($priority_breakdown ?? []) ?>;
    const prioLabels = priorityData.map(item => item.priority || 'Medium');
    const prioCounts = priorityData.map(item => item.count);

    new Chart(document.getElementById('empPriorityChart'), {
        type: 'pie',
        data: {
            labels: prioLabels.length ? prioLabels : ['Medium'],
            datasets: [{
                data: prioCounts.length ? prioCounts : [1],
                backgroundColor: ['#ffc107', '#dc3545', '#0d6efd', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    const trendData = <?= json_encode($monthly_trend ?? []) ?>;
    const trendLabels = trendData.map(item => item.month_name);
    const trendCounts = trendData.map(item => item.count);

    new Chart(document.getElementById('empTrendChart'), {
        type: 'line',
        data: {
            labels: trendLabels.length ? trendLabels : ['Current Month'],
            datasets: [{
                label: 'Assigned Work',
                data: trendCounts.length ? trendCounts : [0],
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
