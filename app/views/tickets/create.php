<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Create New Query Ticket</h4>
            <p class="text-muted fs-7 mb-0">Log incoming email query, allocate employee, and calculate SLA TAT</p>
        </div>
        <a href="<?= base_url('tickets') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Tickets</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="<?= base_url('tickets/create') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-bold">Ticket Type <span class="text-danger">*</span></label>
                        <select name="ticket_type" class="form-select" required>
                            <option value="Query Ticket" selected>Query Ticket</option>
                            <option value="Task Ticket">Task Ticket</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-bold">Received Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="received_datetime" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-bold">From Address / Sender <span class="text-danger">*</span></label>
                        <input type="text" name="from_address" class="form-control" placeholder="client@agency.com or Sender Name" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fs-7 fw-bold">Email Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-control" placeholder="Enter query email subject line" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-bold">Division</label>
                        <select name="division_id" class="form-select">
                            <option value="">Select Division</option>
                            <?php foreach ($divisions as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['division_name']) ?> (<?= $d['code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Dynamic Dependent Dropdown: Activity -->
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-bold">Activity <span class="text-danger">*</span></label>
                        <select name="activity_id" id="activity_id" class="form-select" required>
                            <option value="">Select Parent Activity</option>
                            <?php foreach ($activities as $act): ?>
                                <option value="<?= $act['id'] ?>"><?= htmlspecialchars($act['activity_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Dynamic Dependent Dropdown: Sub-Activity (Populated via AJAX) -->
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-bold">Sub Activity <span class="text-danger">*</span></label>
                        <select name="sub_activity_id" id="sub_activity_id" class="form-select" required>
                            <option value="">Select Activity First</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-bold">Allocated To (Employee)</label>
                        <select name="allocated_to" class="form-select">
                            <option value="">Unassigned (Open Pool)</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= $u['department'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-bold">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-bold">TAT Target Date & Time</label>
                        <input type="datetime-local" name="tat_datetime" id="tat_datetime" class="form-control" placeholder="Auto calculated from SLA">
                        <small class="text-muted fs-8">Leaves empty to auto-calculate based on Sub-Activity SLA</small>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-bold">Agency Code</label>
                        <input type="text" name="agency_code" class="form-control" placeholder="e.g. AGC-9940">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-bold">Manager Name</label>
                        <input type="text" name="manager_name" class="form-control" placeholder="Reporting Manager Name">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fs-7 fw-bold">Remarks / Description</label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="Enter query details, initial notes, or instructions..."></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-7 fw-bold">Attachment Upload (Optional)</label>
                    <input type="file" name="attachment" class="form-control">
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= base_url('tickets') ?>" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><i class="fas fa-check-circle me-2"></i>Create Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>
