<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Input Tracker</h4>
            <p class="text-muted fs-7 mb-0">Incoming Document & Physical Mail Register</p>
        </div>
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#inputModal">
            <i class="fas fa-plus-circle me-1"></i>Log Input Item
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Source</th>
                            <th>Received Date</th>
                            <th>Received From</th>
                            <th>Document Ref / Bill #</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Logged By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($l['source']) ?></span></td>
                            <td class="fs-8"><?= format_datetime($l['received_date']) ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($l['received_from']) ?></td>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($l['document_reference']) ?></td>
                            <td><?= htmlspecialchars($l['assigned_user_name'] ?? 'Unassigned') ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($l['status']) ?></span></td>
                            <td class="fs-8 text-muted"><?= htmlspecialchars($l['creator_name']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Log Input Item -->
<div class="modal fade" id="inputModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('tracker/input') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Log Input Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Source Channel <span class="text-danger">*</span></label>
                        <select name="source" class="form-select" required>
                            <option value="Physical Courier">Physical Courier</option>
                            <option value="Email Intake">Email Intake</option>
                            <option value="Hand Delivered">Hand Delivered</option>
                            <option value="Portal Upload">Portal Upload</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Received Date & Time</label>
                        <input type="datetime-local" name="received_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Received From <span class="text-danger">*</span></label>
                        <input type="text" name="received_from" class="form-control" placeholder="Agency or Client Name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Document Reference / Invoice # <span class="text-danger">*</span></label>
                        <input type="text" name="document_reference" class="form-control" placeholder="e.g. INV-2026-99" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Assign To Employee</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Log Input</button>
                </div>
            </form>
        </div>
    </div>
</div>
