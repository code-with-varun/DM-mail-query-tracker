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
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush fs-7">
                        <?php if (empty($act['sub_activities'])): ?>
                            <li class="list-group-item text-muted text-center py-3">No Sub-Activities defined</li>
                        <?php else: ?>
                            <?php foreach ($act['sub_activities'] as $sa): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span><i class="fas fa-level-up-alt fa-rotate-90 me-2 text-secondary"></i><?= htmlspecialchars($sa['sub_activity_name']) ?></span>
                                <span class="badge bg-dark"><i class="fas fa-clock me-1 text-warning"></i><?= $sa['default_tat_hours'] ?> Hours SLA</span>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal: New Parent Activity -->
<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('master/activities') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="form_type" value="activity">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create Parent Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Activity Name <span class="text-danger">*</span></label>
                        <input type="text" name="activity_name" class="form-control" placeholder="e.g. Legal Compliance" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
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
        <div class="modal-content">
            <form action="<?= base_url('master/activities') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="form_type" value="sub_activity">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create Sub-Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Sub-Activity</button>
                </div>
            </form>
        </div>
    </div>
</div>
