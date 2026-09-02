<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Create Internal Task Ticket</h4>
            <p class="text-muted fs-7 mb-0">Assign internal task ticket to employee</p>
        </div>
        <a href="<?= base_url('tasks') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Tasks</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="<?= base_url('tasks/create') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                
                <div class="mb-3">
                    <label class="form-label fs-7 fw-bold">Task Title <span class="text-danger">*</span></label>
                    <input type="text" name="task_title" class="form-control" placeholder="e.g. Monthly Billing Reconciliation" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-bold">Assigned Employee <span class="text-danger">*</span></label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= $u['department'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-bold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-bold">Due Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="due_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime('+24 hours')) ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-bold">Activity</label>
                        <select name="activity_id" id="activity_id" class="form-select">
                            <option value="">Select Activity</option>
                            <?php foreach ($activities as $act): ?>
                                <option value="<?= $act['id'] ?>"><?= htmlspecialchars($act['activity_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-bold">Sub Activity</label>
                        <select name="sub_activity_id" id="sub_activity_id" class="form-select">
                            <option value="">Select Activity First</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-7 fw-bold">Description / Instructions</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Enter task instructions, scope, or requirements..."></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= base_url('tasks') ?>" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-check-circle me-2"></i>Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
