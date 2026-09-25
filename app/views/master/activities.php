<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Activities Master</h4>
            <p class="text-muted fs-7 mb-0">Manage parent activities and division associations</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary fw-bold btn-sm" data-bs-toggle="modal" data-bs-target="#activityModal">
                <i class="fas fa-plus me-1"></i>Add New Activity
            </button>
        </div>
    </div>

    <!-- Master Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sitemap me-2 text-primary"></i>Activities Master Register</h6>
            <a href="<?= base_url('master/export_activities') ?>" class="btn btn-outline-success btn-sm fw-bold">
                <i class="fas fa-file-csv me-1"></i>Export CSV
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">#</th>
                            <th>Activity Name</th>
                            <th>Parent Division</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 180px;" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $index = 1; foreach ($activities as $act): ?>
                        <tr>
                            <td class="fw-bold text-muted"><?= $index++ ?></td>
                            <td class="fw-bold text-dark">
                                <i class="fas fa-folder me-2 text-primary"></i>
                                <?= htmlspecialchars($act['activity_name']) ?>
                            </td>
                            <td>
                                <?php if (!empty($act['division_code'])): ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($act['division_code']) ?> - <?= htmlspecialchars($act['division_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted fs-8">General / All Divisions</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $act['status'] === 'Active' ? 'success' : 'secondary' ?>"><?= $act['status'] ?></span>
                            </td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2 me-1" data-bs-toggle="modal" data-bs-target="#editActModal<?= $act['id'] ?>" title="Edit Activity">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </button>
                                <form action="<?= base_url('master/activities') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete activity <?= htmlspecialchars($act['activity_name']) ?>?');">
                                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $act['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="Delete Activity">
                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal: Edit Activity -->
                        <div class="modal fade" id="editActModal<?= $act['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <form action="<?= base_url('master/activities') ?>" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $act['id'] ?>">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Activity</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fs-7 fw-bold">Parent Division</label>
                                                <select name="division_id" class="form-select">
                                                    <option value="">Select Division (Optional)</option>
                                                    <?php foreach ($divisions as $div): ?>
                                                        <option value="<?= $div['id'] ?>" <?= ($act['division_id'] ?? '') == $div['id'] ? 'selected' : '' ?>><?= htmlspecialchars($div['division_name']) ?> (<?= htmlspecialchars($div['code']) ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fs-7 fw-bold">Activity Name <span class="text-danger">*</span></label>
                                                <input type="text" name="activity_name" class="form-control" value="<?= htmlspecialchars($act['activity_name']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-bold">Update Activity</button>
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
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-folder-plus me-2"></i>Create New Activity</h5>
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
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>
