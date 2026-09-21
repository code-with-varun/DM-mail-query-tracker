<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-exclamation-triangle text-primary me-2"></i>Error Tracker</h4>
            <p class="text-muted fs-7 mb-0">Track internal and external operational billing & audit error logs, observations, and resolutions</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('error-tracker/export') ?>" class="btn btn-outline-success fw-bold btn-sm shadow-sm">
                <i class="fas fa-file-excel me-1"></i>Export CSV
            </a>
            <button type="button" class="btn btn-outline-primary fw-bold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-upload me-1"></i>Import Previous Records
            </button>
            <button type="button" class="btn btn-primary fw-bold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#addErrorModal">
                <i class="fas fa-plus-circle me-1"></i>Log New Error
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="fas fa-bug fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fs-8 fw-bold text-uppercase">Total Logged Errors</small>
                        <h4 class="fw-bold mb-0 text-dark"><?= $stats['total'] ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning-dark rounded-3 p-3 me-3">
                        <i class="fas fa-building fs-4 text-warning"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fs-8 fw-bold text-uppercase">Internal Errors</small>
                        <h4 class="fw-bold mb-0 text-dark"><?= $stats['internal'] ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3 me-3">
                        <i class="fas fa-globe fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fs-8 fw-bold text-uppercase">External Errors</small>
                        <h4 class="fw-bold mb-0 text-dark"><?= $stats['external'] ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                        <i class="fas fa-check-circle fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fs-8 fw-bold text-uppercase">Resolved / Addressed</small>
                        <h4 class="fw-bold mb-0 text-dark"><?= $stats['resolved'] ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Data Table Register -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-table me-2 text-primary"></i>Error Observation Register</h6>
            <div class="d-flex align-items-center gap-2">
                <div class="btn-group btn-group-sm" role="group">
                    <a href="<?= base_url('error-tracker') ?>" class="btn <?= empty($filters['error_type']) ? 'btn-primary' : 'btn-outline-secondary' ?>">All Types</a>
                    <a href="<?= base_url('error-tracker?error_type=Internal') ?>" class="btn <?= ($filters['error_type'] ?? '') === 'Internal' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' ?>">Internal</a>
                    <a href="<?= base_url('error-tracker?error_type=External') ?>" class="btn <?= ($filters['error_type'] ?? '') === 'External' ? 'btn-danger fw-bold' : 'btn-outline-secondary' ?>">External</a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th style="min-width: 130px;">Billing Month</th>
                            <th style="min-width: 130px;">Checking Month</th>
                            <th style="min-width: 250px;">Error Observation</th>
                            <th style="min-width: 110px;">Error Type</th>
                            <th style="min-width: 140px;">Maker</th>
                            <th style="min-width: 140px;">Checker</th>
                            <th style="min-width: 260px;">Resolution / Solution</th>
                            <th style="min-width: 90px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($errors as $index => $err): ?>
                            <tr>
                                <td class="text-center text-muted fs-7 fw-semibold"><?= $index + 1 ?></td>
                                <td class="text-nowrap">
                                    <span class="badge bg-light text-dark border fw-bold px-2 py-1">
                                        <i class="far fa-calendar-alt me-1 text-primary"></i>
                                        <?= date('M Y', strtotime($err['billing_month'])) ?> (01)
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        <i class="far fa-calendar-check me-1 text-secondary"></i>
                                        <?= date('M Y', strtotime($err['checking_month'])) ?> (01)
                                    </span>
                                </td>
                                <td style="min-width: 250px; max-width: 360px; white-space: normal;">
                                    <span class="fw-bold text-dark d-block text-wrap mb-1"><?= htmlspecialchars($err['error_observation']) ?></span>
                                    <?php if (!empty($err['error_description'])): ?>
                                        <small class="text-muted fs-8 d-block text-wrap" title="<?= htmlspecialchars($err['error_description']) ?>">
                                            <?= htmlspecialchars($err['error_description']) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <?php if ($err['error_type'] === 'Internal'): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning-dark border border-warning text-dark px-2 py-1"><i class="fas fa-building me-1"></i>Internal</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1"><i class="fas fa-globe me-1"></i>External</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <?php if (!empty($err['maker_name'])): ?>
                                        <span class="fw-bold fs-8 text-dark"><i class="fas fa-user-edit me-1 text-secondary"></i><?= htmlspecialchars($err['maker_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted fs-8">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <?php if (!empty($err['checker_name'])): ?>
                                        <span class="fw-bold fs-8 text-dark"><i class="fas fa-user-check me-1 text-secondary"></i><?= htmlspecialchars($err['checker_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted fs-8">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="min-width: 260px; max-width: 360px; white-space: normal;">
                                    <?php if (!empty($err['resolution_solution'])): ?>
                                        <span class="text-success fs-8 fw-semibold d-block text-wrap">
                                            <i class="fas fa-check-circle me-1"></i><?= htmlspecialchars($err['resolution_solution']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted fs-8 text-nowrap"><i class="fas fa-clock me-1 text-warning"></i>Pending Resolution</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <div class="btn-group btn-group-sm">
                                        <!-- Edit Action (Accessible to anyone) -->
                                        <button type="button" class="btn btn-outline-primary btn-edit-error"
                                                data-id="<?= $err['id'] ?>"
                                                data-billing_month="<?= date('Y-m', strtotime($err['billing_month'])) ?>"
                                                data-checking_month="<?= date('Y-m', strtotime($err['checking_month'])) ?>"
                                                data-error_observation="<?= htmlspecialchars($err['error_observation'], ENT_QUOTES) ?>"
                                                data-error_description="<?= htmlspecialchars($err['error_description'] ?? '', ENT_QUOTES) ?>"
                                                data-resolution_solution="<?= htmlspecialchars($err['resolution_solution'] ?? '', ENT_QUOTES) ?>"
                                                data-error_type="<?= htmlspecialchars($err['error_type'], ENT_QUOTES) ?>"
                                                data-maker_id="<?= $err['maker_id'] ?? '' ?>"
                                                data-checker_id="<?= $err['checker_id'] ?? '' ?>"
                                                title="Edit Record">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <!-- Delete Action (Accessible to anyone) -->
                                        <button type="button" class="btn btn-outline-danger btn-delete-error"
                                                data-id="<?= $err['id'] ?>"
                                                data-observation="<?= htmlspecialchars($err['error_observation'], ENT_QUOTES) ?>"
                                                title="Delete Record">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Add New Error Record Modal -->
<div class="modal fade" id="addErrorModal" tabindex="-1" aria-labelledby="addErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="addErrorModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Log New Error Record
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('error-tracker/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Billing Month (Month-Year) <span class="text-danger">*</span></label>
                            <input type="month" name="billing_month" class="form-control" value="<?= date('Y-m') ?>" required>
                            <small class="text-muted fs-8">Date defaults to 01 of the selected month</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Checking Month (Month-Year) <span class="text-danger">*</span></label>
                            <input type="month" name="checking_month" class="form-control" value="<?= date('Y-m') ?>" required>
                            <small class="text-muted fs-8">Date defaults to 01 of the selected month</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Error Observation <span class="text-danger">*</span></label>
                        <input type="text" name="error_observation" class="form-control" placeholder="Enter key error observation title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Error Description</label>
                        <textarea name="error_description" class="form-control" rows="3" placeholder="Provide detailed description of the error observed..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Resolution / Solution</label>
                        <textarea name="resolution_solution" class="form-control" rows="3" placeholder="Enter resolution, corrective action taken, or solution..."></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">Error Type <span class="text-danger">*</span></label>
                            <select name="error_type" class="form-select" required>
                                <option value="Internal" selected>Internal</option>
                                <option value="External">External</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">Maker (Processor)</label>
                            <select name="maker_id" class="form-select">
                                <option value="">Select Maker Employee</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= $u['user_code'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">Checker (Auditor)</label>
                            <select name="checker_id" class="form-select">
                                <option value="">Select Checker Employee</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= $u['user_code'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-save me-1"></i>Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Edit Error Record Modal -->
<div class="modal fade" id="editErrorModal" tabindex="-1" aria-labelledby="editErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="editErrorModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Error Record
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('error-tracker/update') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="id" id="edit_error_id">
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Billing Month (Month-Year) <span class="text-danger">*</span></label>
                            <input type="month" name="billing_month" id="edit_billing_month" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Checking Month (Month-Year) <span class="text-danger">*</span></label>
                            <input type="month" name="checking_month" id="edit_checking_month" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Error Observation <span class="text-danger">*</span></label>
                        <input type="text" name="error_observation" id="edit_error_observation" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Error Description</label>
                        <textarea name="error_description" id="edit_error_description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Resolution / Solution</label>
                        <textarea name="resolution_solution" id="edit_resolution_solution" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">Error Type <span class="text-danger">*</span></label>
                            <select name="error_type" id="edit_error_type" class="form-select" required>
                                <option value="Internal">Internal</option>
                                <option value="External">External</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">Maker (Processor)</label>
                            <select name="maker_id" id="edit_maker_id" class="form-select">
                                <option value="">Select Maker Employee</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= $u['user_code'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark">Checker (Auditor)</label>
                            <select name="checker_id" id="edit_checker_id" class="form-select">
                                <option value="">Select Checker Employee</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= $u['user_code'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-save me-1"></i>Update Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Delete Confirmation Modal -->
<div class="modal fade" id="deleteErrorModal" tabindex="-1" aria-labelledby="deleteErrorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteErrorModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Confirmation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('error-tracker/delete') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="id" id="delete_error_id">
                <div class="modal-body p-4 text-center">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold text-dark">Are you sure?</h5>
                    <p class="text-muted mb-0">
                        You are about to delete error record <strong id="delete_observation_text" class="text-dark"></strong>.
                        This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer bg-light px-4 py-3 justify-content-center">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">
                        <i class="fas fa-trash-alt me-1"></i>Confirm Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 4: Bulk Import CSV Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="importModalLabel">
                    <i class="fas fa-file-upload me-2"></i>Import Previous Error Records
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('error-tracker/import') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Select a CSV file containing historical error logs. Dates will be automatically formatted to day 01.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="import_file" class="form-control" accept=".csv" required>
                    </div>

                    <div class="p-3 bg-light rounded border">
                        <small class="fw-bold d-block text-dark mb-1"><i class="fas fa-download me-1 text-primary"></i>Need a Template?</small>
                        <small class="text-muted d-block mb-2">Download sample formatted template with standard columns.</small>
                        <a href="<?= base_url('error-tracker/sample-template') ?>" class="btn btn-sm btn-outline-primary fw-bold">
                            <i class="fas fa-file-csv me-1"></i>Download Sample CSV Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-upload me-1"></i>Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for Modal Population -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Error Modal Listener
    document.querySelectorAll('.btn-edit-error').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('edit_error_id').value = this.dataset.id;
            document.getElementById('edit_billing_month').value = this.dataset.billing_month;
            document.getElementById('edit_checking_month').value = this.dataset.checking_month;
            document.getElementById('edit_error_observation').value = this.dataset.error_observation;
            document.getElementById('edit_error_description').value = this.dataset.error_description;
            document.getElementById('edit_resolution_solution').value = this.dataset.resolution_solution;
            document.getElementById('edit_error_type').value = this.dataset.error_type;
            document.getElementById('edit_maker_id').value = this.dataset.maker_id;
            document.getElementById('edit_checker_id').value = this.dataset.checker_id;
            
            var modal = new bootstrap.Modal(document.getElementById('editErrorModal'));
            modal.show();
        });
    });

    // Delete Error Modal Listener
    document.querySelectorAll('.btn-delete-error').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('delete_error_id').value = this.dataset.id;
            document.getElementById('delete_observation_text').textContent = this.dataset.observation;
            
            var modal = new bootstrap.Modal(document.getElementById('deleteErrorModal'));
            modal.show();
        });
    });
});
</script>
