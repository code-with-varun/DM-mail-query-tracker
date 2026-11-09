<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">Divisions Master</h4>
            <p class="text-muted fs-7 mb-0">Manage corporate business divisions and codes</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= base_url('master/export_divisions') ?>" class="btn btn-outline-success btn-sm fw-bold">
                <i class="fas fa-file-excel me-1"></i>Export CSV
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#importDivisionsModal">
                <i class="fas fa-file-upload me-1"></i>Import Divisions CSV
            </button>
            <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#divisionModal">
                <i class="fas fa-plus-circle me-1"></i>New Division
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Project / Team</th>
                            <th>Division Name</th>
                            <th>Division Code</th>
                            <th>Assigned Team Leader</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($divisions as $d): ?>
                        <tr>
                            <td><?= $d['id'] ?></td>
                            <td>
                                <?php if (!empty($d['project_name'])): ?>
                                    <span class="badge bg-info text-dark"><i class="fas fa-project-diagram me-1"></i><?= htmlspecialchars($d['project_name']) ?> (<?= htmlspecialchars($d['project_code']) ?>)</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Unmapped</span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-dark"><?= htmlspecialchars($d['division_name']) ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($d['code']) ?></span></td>
                            <td>
                                <?php if (!empty($d['tl_name'])): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold">
                                        <i class="fas fa-user-tie me-1"></i><?= htmlspecialchars($d['tl_name']) ?> (<?= htmlspecialchars($d['tl_code']) ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted fs-8 fst-italic">Unassigned</span>
                                <?php endif; ?>
                            </td>
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
                                                <label class="form-label fs-7 fw-bold">Parent Project / Team</label>
                                                <select name="project_id" class="form-select">
                                                    <option value="">-- None / General --</option>
                                                    <?php foreach ($projects as $p): ?>
                                                    <option value="<?= $p['id'] ?>" <?= ($d['project_id'] ?? null) == $p['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($p['project_name']) ?> (<?= htmlspecialchars($p['code']) ?>)
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fs-7 fw-bold">Assigned Team Leader</label>
                                                <select name="team_leader_id" class="form-select">
                                                    <option value="">-- None / Unassigned --</option>
                                                    <?php foreach ($teamLeaders as $tl): ?>
                                                    <option value="<?= $tl['id'] ?>" <?= ($d['team_leader_id'] ?? null) == $tl['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($tl['full_name']) ?> (<?= htmlspecialchars($tl['user_code']) ?>)
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
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
                        <label class="form-label fs-7 fw-bold">Parent Project / Team</label>
                        <select name="project_id" class="form-select">
                            <option value="">-- None / General --</option>
                            <?php foreach ($projects as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['project_name']) ?> (<?= htmlspecialchars($p['code']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Assigned Team Leader</label>
                        <select name="team_leader_id" class="form-select">
                            <option value="">-- None / Unassigned --</option>
                            <?php foreach ($teamLeaders as $tl): ?>
                            <option value="<?= $tl['id'] ?>">
                                <?= htmlspecialchars($tl['full_name']) ?> (<?= htmlspecialchars($tl['user_code']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
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

<!-- Modal: Bulk Import Divisions -->
<div class="modal fade" id="importDivisionsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="<?= base_url('master/import_divisions') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-upload me-2"></i>Bulk Import Divisions CSV</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-2xs mb-3 fs-8">
                        <i class="fas fa-info-circle me-1"></i> Upload a CSV file containing division records. Existing division codes will be automatically updated.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="import_file" class="form-control" accept=".csv" required>
                    </div>

                    <div class="border p-3 rounded bg-light fs-8">
                        <div class="fw-bold text-dark mb-1"><i class="fas fa-table me-1 text-primary"></i>Expected CSV Headers:</div>
                        <code>Division Code, Division Name, Status</code>
                        <div class="mt-2 text-muted fs-9">
                            Sample: <code>DIV-OPS, Operations Division, Active</code>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 d-flex justify-content-between">
                    <a href="<?= base_url('master/download_template_divisions') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                        <i class="fas fa-download me-1"></i>Download CSV Template
                    </a>
                    <div>
                        <button type="button" class="btn btn-light border fw-bold me-1" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-upload me-1"></i>Upload & Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
