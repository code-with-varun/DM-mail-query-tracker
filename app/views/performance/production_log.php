<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fas fa-calculator text-primary me-2"></i>Daily Production & Count Logger
            </h4>
            <p class="text-muted fs-7 mb-0">Record Daily Transaction Volumes, Worked Hours & Quality Accuracy Checks</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('performance/export') ?>" class="btn btn-outline-success btn-sm fw-bold">
                <i class="fas fa-file-excel me-1"></i>Export CSV
            </a>
            <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#logModal">
                <i class="fas fa-plus-circle me-1"></i>Log Production Entry
            </button>
        </div>
    </div>

    <!-- Production Register Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Log Date</th>
                            <th>Employee</th>
                            <th>Project / Team</th>
                            <th>Sub Activity</th>
                            <th>Processed Count</th>
                            <th>Target Count</th>
                            <th>Achievement %</th>
                            <th>Worked Hrs</th>
                            <th>Internal Errors</th>
                            <th>External Errors</th>
                            <th>Shift Status</th>
                            <th>Logged At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $l): 
                            $ach = round(($l['processed_count'] / max(1, $l['target_count'])) * 100);
                            $badgeCls = $ach >= 100 ? 'bg-success' : ($ach >= 80 ? 'bg-info text-dark' : 'bg-warning text-dark');
                        ?>
                        <tr>
                            <td class="fw-bold text-dark"><?= format_date($l['log_date']) ?></td>
                            <td>
                                <div><strong class="text-primary"><?= htmlspecialchars($l['full_name']) ?></strong></div>
                                <span class="fs-9 text-muted"><?= htmlspecialchars($l['user_code']) ?></span>
                            </td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($l['project_name'] ?? 'General Operations') ?></span></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($l['sub_activity_name'] ?? 'General Production') ?></td>
                            <td class="fw-bold text-success fs-7"><?= number_format($l['processed_count']) ?></td>
                            <td class="text-muted"><?= number_format($l['target_count']) ?></td>
                            <td><span class="badge <?= $badgeCls ?>"><?= $ach ?>%</span></td>
                            <td><span class="badge bg-light text-dark border"><?= $l['worked_hours'] ?> hrs</span></td>
                            <td><span class="badge bg-<?= $l['internal_errors'] > 0 ? 'danger' : 'light text-muted border' ?>"><?= $l['internal_errors'] ?></span></td>
                            <td><span class="badge bg-<?= $l['external_errors'] > 0 ? 'danger' : 'light text-muted border' ?>"><?= $l['external_errors'] ?></span></td>
                            <td><span class="badge bg-<?= $l['shift_status'] === 'Present' ? 'success' : 'warning text-dark' ?>"><?= $l['shift_status'] ?></span></td>
                            <td class="fs-8 text-muted"><?= format_datetime($l['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Log Daily Production Entry -->
<div class="modal fade" id="logModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= base_url('performance/log-production') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Log Daily Production & Transaction Count</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">

                    <?php if (is_super_admin() || is_manager() || is_team_lead()): ?>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Select Employee <span class="text-danger">*</span></label>
                            <select name="user_id" class="form-select" required>
                                <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>" <?= $emp['id'] == $_SESSION['user_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['user_code']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Project / Team</label>
                            <select name="project_id" class="form-select">
                                <option value="">-- General Operations --</option>
                                <?php foreach ($projects as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['project_name']) ?> (<?= htmlspecialchars($p['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Log Date <span class="text-danger">*</span></label>
                            <input type="date" name="log_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Sub Activity</label>
                            <select name="sub_activity_id" class="form-select">
                                <option value="">-- Select Sub Activity --</option>
                                <?php foreach ($subActivities as $sa): ?>
                                <option value="<?= $sa['id'] ?>"><?= htmlspecialchars($sa['activity_name']) ?> — <?= htmlspecialchars($sa['sub_activity_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold">Processed Volume / Count <span class="text-danger">*</span></label>
                            <input type="number" name="processed_count" class="form-control" placeholder="e.g. 150" required min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold">Daily Target Volume <span class="text-danger">*</span></label>
                            <input type="number" name="target_count" class="form-control" placeholder="e.g. 150" required min="1" value="150">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold">Worked Hours</label>
                            <input type="number" step="0.5" name="worked_hours" class="form-control" value="8.0" min="1" max="16">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold">Internal QC Errors</label>
                            <input type="number" name="internal_errors" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold">External Client Errors</label>
                            <input type="number" name="external_errors" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fs-7 fw-bold">Shift Status</label>
                            <select name="shift_status" class="form-select">
                                <option value="Present" selected>Present</option>
                                <option value="Half Day">Half Day</option>
                                <option value="Leave">Leave</option>
                                <option value="Holiday">Holiday</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Remarks / Shift Notes</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes regarding volume or errors..."></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i>Save Production Log</button>
                </div>
            </form>
        </div>
    </div>
</div>
