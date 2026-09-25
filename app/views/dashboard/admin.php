<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Team Lead / Admin Dashboard</h4>
            <p class="text-muted fs-7 mb-0">Team Workload Distribution, SLA Compliance & Ticket Allocation</p>
        </div>
        <div>
            <a href="<?= base_url('tickets/create') ?>" class="btn btn-primary fw-bold btn-sm"><i class="fas fa-plus-circle me-1"></i>New Ticket</a>
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
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">Team Volume</div>
                    <div class="stat-value text-dark fw-bold fs-4"><?= $stats['total'] ?></div>
                    <div class="fs-8 text-primary mt-1"><i class="fas fa-users me-1"></i>Team Assigned</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">Active / Pending</div>
                    <div class="stat-value text-warning fw-bold fs-4"><?= $stats['open'] ?></div>
                    <div class="fs-8 text-warning mt-1"><i class="fas fa-clock me-1"></i>In Progress</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">Overdue SLA</div>
                    <div class="stat-value text-danger fw-bold fs-4"><?= $stats['overdue'] ?></div>
                    <div class="fs-8 text-danger mt-1"><i class="fas fa-exclamation-circle me-1"></i>Escalated SLA</div>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card stat-card bg-white h-100 shadow-sm border-0">
                <div class="card-body p-3">
                    <div class="stat-label fs-8 text-uppercase fw-bold text-muted mb-1">On Hold</div>
                    <div class="stat-value text-secondary fw-bold fs-4"><?= $stats['on_hold'] ?></div>
                    <div class="fs-8 text-muted mt-1"><i class="fas fa-pause me-1"></i>Pending Review</div>
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
                    <div class="fs-8 text-muted mt-1"><i class="fas fa-shield-alt me-1"></i>SLA Accuracy</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Charts Row 1 -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie text-warning me-2"></i>Team Status Breakdown</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="adminStatusChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-bar text-primary me-2"></i>Division Distribution</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="adminDivisionChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie text-danger me-2"></i>Priority Distribution</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="adminPriorityChart" style="max-height: 240px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Tickets List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-list text-primary me-2"></i>Team Tickets Queue</h6>
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
                            <th>Assigned Employee</th>
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
                            <td><?= htmlspecialchars($t['allocated_user_name'] ?? 'Unassigned') ?></td>
                            <td><?= get_status_badge($t['status']) ?></td>
                            <td><?= get_tat_badge($t['tat_datetime'], $t['status']) ?></td>
                            <td>
                                <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="btn btn-sm btn-light border"><i class="fas fa-eye text-primary"></i> View</a>
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
    
    new Chart(document.getElementById('adminStatusChart'), {
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

    const divisionData = <?= json_encode($division_breakdown ?? []) ?>;
    const divLabels = divisionData.map(item => item.division_name);
    const divCounts = divisionData.map(item => item.count);

    new Chart(document.getElementById('adminDivisionChart'), {
        type: 'bar',
        data: {
            labels: divLabels.length ? divLabels : ['General'],
            datasets: [{
                label: 'Tickets',
                data: divCounts.length ? divCounts : [0],
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

    const priorityData = <?= json_encode($priority_breakdown ?? []) ?>;
    const prioLabels = priorityData.map(item => item.priority || 'Medium');
    const prioCounts = priorityData.map(item => item.count);

    new Chart(document.getElementById('adminPriorityChart'), {
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
});
</script>
