<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Activities & Sub-Activities Master</h4>
            <p class="text-muted fs-7 mb-0">Configure parent business activities and SLA TAT hours per sub-activity</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#activityModal">
                <i class="fas fa-plus me-1"></i>New Parent Activity
            </button>
            <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#subActivityModal">
                <i class="fas fa-plus-circle me-1"></i>New Sub-Activity
            </button>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($activities as $act): ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-folder me-2"></i><?= htmlspecialchars($act['activity_name']) ?></h6>
                        <small class="text-muted fs-8"><?= htmlspecialchars($act['description'] ?? 'No description') ?></small>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-light border p-1 px-2 text-primary" data-bs-toggle="modal" data-bs-target="#editActModal<?= $act['id'] ?>" title="Edit Parent Activity">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form action="<?= base_url('master/activities') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete activity <?= htmlspecialchars($act['activity_name']) ?>?');">
                            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                            <input type="hidden" name="form_type" value="activity">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $act['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-light border p-1 px-2 text-danger" title="Delete Activity">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush fs-7">
                        <?php if (empty($act['sub_activities'])): ?>
                            <li class="list-group-item text-muted text-center py-3">No Sub-Activities defined</li>
                        <?php else: ?>
                            <?php foreach ($act['sub_activities'] as $sa): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span><i class="fas fa-level-up-alt fa-rotate-90 me-2 text-secondary"></i><?= htmlspecialchars($sa['sub_activity_name']) ?></span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-dark"><i class="fas fa-clock me-1 text-warning"></i><?= $sa['default_tat_hours'] ?> Hours SLA</span>
                                    <button type="button" class="btn btn-sm btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#editSubActModal<?= $sa['id'] ?>" title="Edit Sub-Activity">
                                        <i class="fas fa-edit fs-7"></i>
                                    </button>
                                    <form action="<?= base_url('master/activities') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete sub-activity <?= htmlspecialchars($sa['sub_activity_name']) ?>?');">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="form_type" value="sub_activity">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $sa['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-link p-0 text-danger" title="Delete Sub-Activity">
                                            <i class="fas fa-trash-alt fs-7"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>

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
                                                    <label class="form-label fs-7 fw-bold">Parent Activity <span class="text-danger">*</span></label>
                                                    <select name="activity_id" class="form-select" required>
                                                        <?php foreach ($activities as $pAct): ?>
                                                            <option value="<?= $pAct['id'] ?>" <?= $pAct['id'] == $act['id'] ? 'selected' : '' ?>><?= htmlspecialchars($pAct['activity_name']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fs-7 fw-bold">Sub-Activity Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="sub_activity_name" class="form-control" value="<?= htmlspecialchars($sa['sub_activity_name']) ?>" required>
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
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Modal: Edit Parent Activity -->
        <div class="modal fade" id="editActModal<?= $act['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content border-0 shadow">
                    <form action="<?= base_url('master/activities') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                        <input type="hidden" name="form_type" value="activity">
                        <input type="hidden" name="id" value="<?= $act['id'] ?>">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Parent Activity</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fs-7 fw-bold">Activity Name <span class="text-danger">*</span></label>
                                <input type="text" name="activity_name" class="form-control" value="<?= htmlspecialchars($act['activity_name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fs-7 fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($act['description'] ?? '') ?></textarea>
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
