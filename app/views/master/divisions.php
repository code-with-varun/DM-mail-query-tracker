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
                            <th>Action</th>
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
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary p-1 px-2 me-1" data-bs-toggle="modal" data-bs-target="#editDivModal<?= $d['id'] ?>" title="Edit Division">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="<?= base_url('master/divisions') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete division <?= htmlspecialchars($d['division_name']) ?>?');">
                                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="Delete Division">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal: Edit Division -->
                        <div class="modal fade" id="editDivModal<?= $d['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow">
                                    <form action="<?= base_url('master/divisions') ?>" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Division</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fs-7 fw-bold">Division Name <span class="text-danger">*</span></label>
                                                <input type="text" name="division_name" class="form-control" value="<?= htmlspecialchars($d['division_name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fs-7 fw-bold">Division Code <span class="text-danger">*</span></label>
                                                <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($d['code']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-bold">Update Division</button>
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

<!-- Modal: New Division -->
<div class="modal fade" id="divisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('master/divisions') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Create Business Division</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Division Name <span class="text-danger">*</span></label>
                        <input type="text" name="division_name" class="form-control" placeholder="e.g. Media Purchasing" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Division Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. MP" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Division</button>
                </div>
            </form>
        </div>
    </div>
</div>
