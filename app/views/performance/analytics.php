<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-chart-bar text-primary me-2"></i>Multi-Horizon Performance Analytics
            </h4>
            <p class="text-muted fs-7 mb-0">Cross-Horizon Production Volume, Quality Accuracy % & Efficiency Ratios</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <form action="<?= base_url('performance/analytics') ?>" method="GET" class="d-flex gap-2">
                <select name="horizon" class="form-select form-select-sm fw-bold border-primary" onchange="this.form.submit()">
                    <option value="day" <?= $selectedHorizon === 'day' ? 'selected' : '' ?>>Daywise View</option>
                    <option value="month" <?= $selectedHorizon === 'month' ? 'selected' : '' ?>>Monthwise View</option>
                    <option value="quarter" <?= $selectedHorizon === 'quarter' ? 'selected' : '' ?>>Quarterwise View</option>
                    <option value="year" <?= $selectedHorizon === 'year' ? 'selected' : '' ?>>Yearwise View</option>
                </select>

                <select name="project_id" class="form-select form-select-sm fw-bold border-primary" onchange="this.form.submit()">
                    <option value="">-- All Teams / Projects --</option>
                    <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $selectedProjectId == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['project_name']) ?> (<?= htmlspecialchars($p['code']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <a href="<?= base_url('performance/export') ?>" class="btn btn-outline-success btn-sm fw-bold">
                <i class="fas fa-file-excel me-1"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- Analytics Register Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fas fa-table text-primary me-2"></i><?= ucfirst($selectedHorizon) ?>wise Aggregation Summary
            </h6>
            <span class="badge bg-primary fs-8"><?= count($analytics) ?> Periods Tracked</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Horizon Period</th>
                            <th>Active Contributors</th>
                            <th>Total Processed Volume</th>
                            <th>Total Target Volume</th>
                            <th>Volume Efficiency %</th>
                            <th>Total Internal Errors</th>
                            <th>Total External Errors</th>
                            <th>Quality Accuracy %</th>
                            <th>Avg Worked Hrs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analytics as $row): 
                            $effCls = $row['efficiency_pct'] >= 100 ? 'bg-success' : ($row['efficiency_pct'] >= 80 ? 'bg-info text-dark' : 'bg-warning text-dark');
                            $accCls = $row['accuracy_pct'] >= 98 ? 'bg-success' : ($row['accuracy_pct'] >= 95 ? 'bg-info text-dark' : 'bg-danger');
                        ?>
                        <tr>
                            <td class="fw-bold text-dark fs-7"><?= htmlspecialchars($row['horizon_period']) ?></td>
                            <td><span class="badge bg-secondary"><?= number_format($row['active_users']) ?> Users</span></td>
                            <td class="fw-bold text-primary fs-7"><?= number_format($row['total_processed']) ?></td>
                            <td class="text-muted"><?= number_format($row['total_target']) ?></td>
                            <td><span class="badge <?= $effCls ?> fs-8"><?= $row['efficiency_pct'] ?>%</span></td>
                            <td><span class="badge bg-<?= $row['total_internal_errors'] > 0 ? 'danger' : 'light text-muted border' ?>"><?= number_format($row['total_internal_errors']) ?></span></td>
                            <td><span class="badge bg-<?= $row['total_external_errors'] > 0 ? 'danger' : 'light text-muted border' ?>"><?= number_format($row['total_external_errors']) ?></span></td>
                            <td><span class="badge <?= $accCls ?> fs-8"><?= $row['accuracy_pct'] ?>%</span></td>
                            <td><span class="badge bg-light text-dark border"><?= number_format($row['avg_worked_hours'], 1) ?> hrs</span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($analytics)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted fs-8">
                                <i class="fas fa-info-circle fs-5 d-block mb-1"></i> No production log entries recorded for this horizon yet. Use Daily Production Logger to log entries.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
