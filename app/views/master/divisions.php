<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Divisions Master</h4>
            <p class="text-muted fs-7 mb-0">Manage corporate business divisions and codes</p>
        </div>
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#divisionModal">
            <i class="fas fa-plus-circle me-1"></i>New Division
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Division Name</th>
                            <th>Division Code</th>
                            <th>Status</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($divisions as $d): ?>
                        <tr>
                            <td><?= $d['id'] ?></td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($d['division_name']) ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($d['code']) ?></span></td>
                            <td><span class="badge bg-success"><?= htmlspecialchars($d['status']) ?></span></td>
                            <td class="fs-8 text-muted"><?= format_datetime($d['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: New Division -->
<div class="modal fade" id="divisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('master/divisions') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create Business Division</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Division Name <span class="text-danger">*</span></label>
                        <input type="text" name="division_name" class="form-control" placeholder="e.g. Media Purchasing" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Division Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. MP" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Division</button>
                </div>
            </form>
        </div>
    </div>
</div>
