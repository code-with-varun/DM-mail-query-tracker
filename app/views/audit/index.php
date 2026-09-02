<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">System Audit Logs</h4>
            <p class="text-muted fs-7 mb-0">Immutable system activity logs and compliance history</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>IP Address</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $l): ?>
                        <tr>
                            <td class="fs-8 text-muted"><?= format_datetime($l['created_at']) ?></td>
                            <td class="fw-bold text-dark"><?= $l['full_name'] ? htmlspecialchars($l['full_name']) . " (" . $l['user_code'] . ")" : 'System / Unauthenticated' ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($l['module']) ?></span></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($l['action']) ?></span></td>
                            <td class="fs-8 font-monospace"><?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?></td>
                            <td class="fs-8">
                                <?php if ($l['new_values']): ?>
                                    <code><?= htmlspecialchars(substr($l['new_values'], 0, 100)) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
