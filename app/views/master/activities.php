<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Activities & Sub-Activities Master</h4>
            <p class="text-muted fs-7 mb-0">Manage Division &gt; Activity &gt; Sub-Activity hierarchy and Default Employee mappings</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary fw-bold btn-sm" data-bs-toggle="modal" data-bs-target="#activityModal">
                <i class="fas fa-plus me-1"></i>New Parent Activity
            </button>
            <button type="button" class="btn btn-primary fw-bold btn-sm" data-bs-toggle="modal" data-bs-target="#subActivityModal">
                <i class="fas fa-plus-circle me-1"></i>New Sub-Activity
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded p-3 me-3"><i class="fas fa-building fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block fs-8 fw-bold text-uppercase">Divisions</small>
                        <h4 class="fw-bold mb-0 text-dark"><?= count($divisions) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success text-white rounded p-3 me-3"><i class="fas fa-folder fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block fs-8 fw-bold text-uppercase">Parent Activities</small>
                        <h4 class="fw-bold mb-0 text-dark"><?= count($activities) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-info text-white rounded p-3 me-3"><i class="fas fa-list-ol fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block fs-8 fw-bold text-uppercase">Sub-Activities</small>
                        <h4 class="fw-bold mb-0 text-dark"><?= count($allSubActivities) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-warning text-dark rounded p-3 me-3"><i class="fas fa-user-check fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block fs-8 fw-bold text-uppercase">Default Assignees</small>
                        <h4 class="fw-bold mb-0 text-dark"><?= count(array_filter($allSubActivities, fn($sa) => !empty($sa['default_user_id']))) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scalable Master Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-table me-2 text-primary"></i>Master Hierarchy Register</h6>
            <a href="<?= base_url('master/export_all') ?>" class="btn btn-outline-success btn-sm fw-bold">
                <i class="fas fa-file-csv me-1"></i>Export Master CSV
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Sub-Activity Name</th>
                            <th>Parent Activity</th>
                            <th>Division</th>
                            <th>Default Assigned Employee</th>
                            <th>Default SLA TAT</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allSubActivities as $sa): ?>
                        <tr>
                            <td class="fw-bold text-dark">
                                <i class="fas fa-level-up-alt fa-rotate-90 me-2 text-primary"></i>
                                <?= htmlspecialchars($sa['sub_activity_name']) ?>
                            </td>
                            <td>
                                <span class="fw-bold text-primary"><?= htmlspecialchars($sa['activity_name']) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($sa['division_code'])): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($sa['division_code']) ?> - <?= htmlspecialchars($sa['division_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted fs-8">General</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($sa['default_user_name'])): ?>
                                    <div class="fw-bold fs-7 text-dark"><i class="fas fa-user-check text-success me-1"></i><?= htmlspecialchars($sa['default_user_name']) ?></div>
                                    <small class="text-muted fs-8">Code: <?= htmlspecialchars($sa['default_user_code']) ?></small>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-dark"><i class="fas fa-clock text-warning me-1"></i><?= $sa['default_tat_hours'] ?> Hours</span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $sa['status'] === 'Active' ? 'success' : 'secondary' ?>"><?= $sa['status'] ?></span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2 me-1" data-bs-toggle="modal" data-bs-target="#editSubActModal<?= $sa['id'] ?>" title="Edit Sub-Activity">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="<?= base_url('master/activities') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete sub-activity <?= htmlspecialchars($sa['sub_activity_name']) ?>?');">
                                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                    <input type="hidden" name="form_type" value="sub_activity">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $sa['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="Delete Sub-Activity">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal: Edit Sub-Activity -->
                        <div class="modal fade" id="editSubActModal<?= $sa['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <form action="<?= base_url('master/activities') ?>" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="form_type" value="sub_activity">
                                        <input type="hidden" name="id" value="<?= $sa['id'] ?>">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Sub-Activity</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fs-7 fw-bold">Parent Division</label>
                                                <select name="division_id" class="form-select">
                                                    <option value="">Select Division (Optional)</option>
                                                    <?php foreach ($divisions as $div): ?>
                                                        <option value="<?= $div['id'] ?>" <?= ($sa['division_id'] ?? '') == $div['id'] ? 'selected' : '' ?>><?= htmlspecialchars($div['division_name']) ?> (<?= htmlspecialchars($div['code']) ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fs-7 fw-bold">Parent Activity <span class="text-danger">*</span></label>
                                                <select name="activity_id" class="form-select" required>
                                                    <?php foreach ($activities as $pAct): ?>
                                                        <option value="<?= $pAct['id'] ?>" <?= $pAct['id'] == $sa['activity_id'] ? 'selected' : '' ?>><?= htmlspecialchars($pAct['activity_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fs-7 fw-bold">Sub-Activity Name <span class="text-danger">*</span></label>
                                                <input type="text" name="sub_activity_name" class="form-control" value="<?= htmlspecialchars($sa['sub_activity_name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fs-7 fw-bold">Default Assigned Employee</label>
                                                <select name="default_user_id" class="form-select">
                                                    <option value="">Select Default Employee (Optional)</option>
                                                    <?php foreach ($employees as $emp): ?>
                                                        <option value="<?= $emp['id'] ?>" <?= ($sa['default_user_id'] ?? '') == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['user_code']) ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fs-7 fw-bold">Default SLA TAT Hours <span class="text-danger">*</span></label>
                                                <input type="number" name="default_tat_hours" class="form-control" value="<?= $sa['default_tat_hours'] ?>" min="1" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-bold">Update Sub-Activity</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: New Parent Activity -->
<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('master/activities') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="form_type" value="activity">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-folder-plus me-2"></i>Create Parent Activity</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Parent Division</label>
                        <select name="division_id" class="form-select">
                            <option value="">Select Division (Optional)</option>
                            <?php foreach ($divisions as $div): ?>
                                <option value="<?= $div['id'] ?>"><?= htmlspecialchars($div['division_name']) ?> (<?= htmlspecialchars($div['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Activity Name <span class="text-danger">*</span></label>
                        <input type="text" name="activity_name" class="form-control" placeholder="e.g. Legal Compliance" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: New Sub-Activity -->
<div class="modal fade" id="subActivityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('master/activities') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="form_type" value="sub_activity">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Create Sub-Activity</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Parent Division</label>
                        <select name="division_id" class="form-select">
                            <option value="">Select Division (Optional)</option>
                            <?php foreach ($divisions as $div): ?>
                                <option value="<?= $div['id'] ?>"><?= htmlspecialchars($div['division_name']) ?> (<?= htmlspecialchars($div['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Parent Activity <span class="text-danger">*</span></label>
                        <select name="activity_id" class="form-select" required>
                            <option value="">Select Parent Activity</option>
                            <?php foreach ($activities as $act): ?>
                                <option value="<?= $act['id'] ?>"><?= htmlspecialchars($act['activity_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Sub-Activity Name <span class="text-danger">*</span></label>
                        <input type="text" name="sub_activity_name" class="form-control" placeholder="e.g. Contract Verification" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Default Assigned Employee</label>
                        <select name="default_user_id" class="form-select">
                            <option value="">Select Default Employee (Optional)</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['full_name']) ?> (<?= htmlspecialchars($emp['user_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Default SLA TAT Hours <span class="text-danger">*</span></label>
                        <input type="number" name="default_tat_hours" class="form-control" value="24" min="1" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Sub-Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>
