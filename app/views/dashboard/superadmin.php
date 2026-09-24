<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Super Admin Dashboard</h4>
            <p class="text-muted fs-7 mb-0">Global Mail Query & Task Metrics Overview</p>
        </div>
        <div>
            <a href="<?= base_url('tickets/create') ?>" class="btn btn-primary fw-bold"><i class="fas fa-plus-circle me-2"></i>New Ticket</a>
        </div>
    </div>

    <!-- Stat Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-white">
                <div class="card-body">
                    <div>
                        <div class="stat-label">Total Tickets</div>
                        <div class="stat-value text-dark"><?= $stats['total'] ?></div>
                    </div>
                    <div class="icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card bg-white">
                <div class="card-body">
                    <div>
                        <div class="stat-label">Open / Active</div>
                        <div class="stat-value text-primary"><?= $stats['open'] ?></div>
                    </div>
                    <div class="icon-box bg-info bg-opacity-10 text-info">
                        <i class="fas fa-spinner"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card bg-white">
                <div class="card-body">
                    <div>
                        <div class="stat-label">Overdue SLA</div>
                        <div class="stat-value text-danger"><?= $stats['overdue'] ?></div>
                    </div>
                    <div class="icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card stat-card bg-white">
                <div class="card-body">
                    <div>
                        <div class="stat-label">Total Admins</div>
                        <div class="stat-value text-dark"><?= $total_admins ?></div>
                    </div>
                    <div class="icon-box bg-success bg-opacity-10 text-success">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Charts Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-pie text-primary me-2"></i>Ticket Status Breakdown</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-3">
                    <canvas id="statusChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar text-danger me-2"></i>Division Performance Analytics</h6>
                </div>
                <div class="card-body p-3">
                    <canvas id="divisionChart" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tickets DataTable -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="fas fa-list text-primary me-2"></i>Recent Tickets</h6>
            <a href="<?= base_url('tickets') ?>" class="btn btn-sm btn-outline-primary">View All</a>
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
                            <th>Allocated To</th>
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
    // Status Doughnut Chart
    const statusData = <?= json_encode($status_breakdown ?? []) ?>;
    const statusLabels = statusData.map(item => item.status);
    const statusCounts = statusData.map(item => item.count);
    
    new Chart(document.getElementById('statusChart'), {
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

    // Division Bar Chart
    const divisionData = <?= json_encode($division_breakdown ?? []) ?>;
    const divLabels = divisionData.map(item => item.division_name);
    const divCounts = divisionData.map(item => item.count);

    new Chart(document.getElementById('divisionChart'), {
        type: 'bar',
        data: {
            labels: divLabels.length ? divLabels : ['General'],
            datasets: [{
                label: 'Tickets',
                data: divCounts.length ? divCounts : [0],
                backgroundColor: '#dc3545',
                borderRadius: 4
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
