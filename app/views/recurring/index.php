<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Recurring Task Engine</h4>
            <p class="text-muted fs-7 mb-0">Automate recurring daily, weekly, monthly workloads</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('recurring/trigger') ?>" class="btn btn-outline-primary fw-bold" onclick="return confirm('Run recurring scheduler engine now?');">
                <i class="fas fa-play me-1"></i>Run Scheduler Now
            </a>
            <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#templateModal">
                <i class="fas fa-plus-circle me-1"></i>New Template
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0"><i class="fas fa-redo text-primary me-2"></i>Active Recurring Templates</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Template Name</th>
                            <th>Frequency</th>
                            <th>Due Day</th>
                            <th>Assigned Employee</th>
                            <th>Activity / Sub-Activity</th>
                            <th>Priority</th>
                            <th>Last Generated</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $tmpl): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($tmpl['template_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($tmpl['description']) ?></small>
                            </td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($tmpl['frequency']) ?></span></td>
                            <td>Day <?= htmlspecialchars($tmpl['due_day']) ?></td>
                            <td><?= htmlspecialchars($tmpl['assigned_user_name'] ?? 'Unassigned') ?></td>
                            <td>
                                <div><?= htmlspecialchars($tmpl['activity_name'] ?? 'N/A') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($tmpl['sub_activity_name'] ?? '') ?></small>
                            </td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($tmpl['priority']) ?></span></td>
                            <td class="fs-8"><?= $tmpl['last_generated_at'] ? format_datetime($tmpl['last_generated_at']) : 'Never' ?></td>
                            <td>
                                <span class="badge bg-<?= $tmpl['is_active'] ? 'success' : 'danger' ?>">
                                    <?= $tmpl['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: New Recurring Template -->
<div class="modal fade" id="templateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= base_url('recurring/create') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create Recurring Task Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Template Name <span class="text-danger">*</span></label>
                        <input type="text" name="template_name" class="form-control" placeholder="e.g. Monthly Audit Preparation" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Assigned Employee <span class="text-danger">*</span></label>
                            <select name="assigned_user_id" class="form-select" required>
                                <option value="">Select Employee</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= $u['department'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-7 fw-bold">Frequency <span class="text-danger">*</span></label>
                            <select name="frequency" class="form-select" required>
                                <option value="Daily">Daily</option>
                                <option value="Weekly">Weekly</option>
                                <option value="Monthly" selected>Monthly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Half Yearly">Half Yearly</option>
                                <option value="Yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-7 fw-bold">Due Day of Month</label>
                            <input type="number" name="due_day" class="form-control" value="5" min="1" max="31">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Activity <span class="text-danger">*</span></label>
                            <select name="activity_id" id="activity_id" class="form-select" required>
                                <option value="">Select Activity</option>
                                <?php foreach ($activities as $act): ?>
                                    <option value="<?= $act['id'] ?>"><?= htmlspecialchars($act['activity_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Sub Activity <span class="text-danger">*</span></label>
                            <select name="sub_activity_id" id="sub_activity_id" class="form-select" required>
                                <option value="">Select Activity First</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-7 fw-bold">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Description / Instructions</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Template task details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Create Template</button>
                </div>
            </form>
        </div>
    </div>
</div>
