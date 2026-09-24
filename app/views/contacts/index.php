<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-address-book text-primary me-2"></i>Contact Manager</h4>
            <p class="text-muted fs-7 mb-0">Directory of Client, Internal, and Third Party organizational contacts</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary fw-bold shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#importContactModal">
                <i class="fas fa-file-upload me-1"></i>Import Contacts
            </button>
            <button type="button" class="btn btn-primary fw-bold shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#addContactModal">
                <i class="fas fa-user-plus me-1"></i>Add New Contact
            </button>
        </div>
    </div>

    <!-- Flash Notifications -->
    <?php $flash = Session::getFlash(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-2"></i>
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Quick Stats Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="<?= base_url('contacts') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm p-3 <?= empty($selectedOrg) ? 'border-start border-4 border-primary' : '' ?>">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                            <i class="fas fa-address-book fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fs-8 fw-bold text-uppercase">Total Contacts</small>
                            <h4 class="fw-bold mb-0 text-dark"><?= $stats['total'] ?></h4>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?= base_url('contacts?org=Client') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm p-3 <?= $selectedOrg === 'Client' ? 'border-start border-4 border-info' : '' ?>">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                            <i class="fas fa-user-tie fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fs-8 fw-bold text-uppercase">Client Contacts</small>
                            <h4 class="fw-bold mb-0 text-dark"><?= $stats['client'] ?></h4>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?= base_url('contacts?org=Internal') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm p-3 <?= $selectedOrg === 'Internal' ? 'border-start border-4 border-success' : '' ?>">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                            <i class="fas fa-building fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fs-8 fw-bold text-uppercase">Internal Contacts</small>
                            <h4 class="fw-bold mb-0 text-dark"><?= $stats['internal'] ?></h4>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="<?= base_url('contacts?org=Third Party') ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm p-3 <?= $selectedOrg === 'Third Party' ? 'border-start border-4 border-warning' : '' ?>">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                            <i class="fas fa-handshake fs-4"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block fs-8 fw-bold text-uppercase">Third Party</small>
                            <h4 class="fw-bold mb-0 text-dark"><?= $stats['third_party'] ?></h4>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Contacts Register Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-list me-2 text-primary"></i>Contacts Directory</h6>
                <?php if (!empty($selectedOrg)): ?>
                    <span class="badge bg-primary rounded-pill px-3">Filter: <?= htmlspecialchars($selectedOrg) ?></span>
                    <a href="<?= base_url('contacts') ?>" class="text-muted fs-8 ms-1"><i class="fas fa-times-circle"></i> Clear Filter</a>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- Org Type Filter Pill Buttons -->
                <div class="btn-group btn-group-sm" role="group">
                    <a href="<?= base_url('contacts') ?>" class="btn <?= empty($selectedOrg) ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
                    <a href="<?= base_url('contacts?org=Client') ?>" class="btn <?= $selectedOrg === 'Client' ? 'btn-info text-white' : 'btn-outline-secondary' ?>">Client</a>
                    <a href="<?= base_url('contacts?org=Internal') ?>" class="btn <?= $selectedOrg === 'Internal' ? 'btn-success' : 'btn-outline-secondary' ?>">Internal</a>
                    <a href="<?= base_url('contacts?org=Third Party') ?>" class="btn <?= $selectedOrg === 'Third Party' ? 'btn-warning text-dark' : 'btn-outline-secondary' ?>">Third Party</a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Contact Name</th>
                            <th>Organisation Type</th>
                            <th>Contact Number</th>
                            <th>Email Address</th>
                            <th>Remark Notes</th>
                            <th>Added By</th>
                            <th style="width: 100px;" class="text-end text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $index => $c): ?>
                                <tr>
                                    <td class="text-muted fs-7"><?= $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light text-primary rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold border" style="width:36px; height:36px;">
                                                <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block"><?= htmlspecialchars($c['name']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <?php if ($c['organisation_type'] === 'Client'): ?>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1"><i class="fas fa-user-tie me-1"></i>Client</span>
                                        <?php elseif ($c['organisation_type'] === 'Internal'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1"><i class="fas fa-building me-1"></i>Internal</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning-dark border border-warning text-dark px-2 py-1"><i class="fas fa-handshake me-1"></i>Third Party</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <?php if (!empty($c['contact_number'])): ?>
                                            <a href="tel:<?= htmlspecialchars($c['contact_number']) ?>" class="text-decoration-none text-dark">
                                                <i class="fas fa-phone-alt me-1 text-secondary fs-8"></i><?= htmlspecialchars($c['contact_number']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted fs-8">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <?php if (!empty($c['email'])): ?>
                                            <a href="mailto:<?= htmlspecialchars($c['email']) ?>" class="text-decoration-none text-primary">
                                                <i class="fas fa-envelope me-1 fs-8"></i><?= htmlspecialchars($c['email']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted fs-8">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['remarks'])): ?>
                                            <span class="text-secondary fs-7 text-truncate d-inline-block" style="max-width: 220px;" title="<?= htmlspecialchars($c['remarks']) ?>">
                                                <?= htmlspecialchars($c['remarks']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted fs-8">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <small class="text-muted d-block fs-8">
                                            <i class="fas fa-user me-1"></i><?= htmlspecialchars($c['creator_name'] ?? 'System') ?>
                                        </small>
                                        <small class="text-muted fs-8" title="<?= $c['created_at'] ?>">
                                            <?= date('d M Y', strtotime($c['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Edit Contact Button (Available for any logged in user) -->
                                            <button type="button" class="btn btn-outline-primary btn-edit-contact" 
                                                    data-id="<?= $c['id'] ?>"
                                                    data-name="<?= htmlspecialchars($c['name'], ENT_QUOTES) ?>"
                                                    data-contact_number="<?= htmlspecialchars($c['contact_number'] ?? '', ENT_QUOTES) ?>"
                                                    data-email="<?= htmlspecialchars($c['email'] ?? '', ENT_QUOTES) ?>"
                                                    data-organisation_type="<?= htmlspecialchars($c['organisation_type'], ENT_QUOTES) ?>"
                                                    data-remarks="<?= htmlspecialchars($c['remarks'] ?? '', ENT_QUOTES) ?>"
                                                    title="Edit Contact">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <!-- Delete Contact Button (ONLY for Admin / Super Admin) -->
                                            <?php if (is_admin()): ?>
                                                <button type="button" class="btn btn-outline-danger btn-delete-contact" 
                                                        data-id="<?= $c['id'] ?>"
                                                        data-name="<?= htmlspecialchars($c['name'], ENT_QUOTES) ?>"
                                                        title="Delete Contact">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            <?php endif; ?>
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

<!-- Modal 1: Add New Contact Modal -->
<div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="addContactModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New Contact
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('contacts/store') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Contact Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. John Doe / Acme Corp" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control" placeholder="e.g. +91 9876543210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Mail ID / Email</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. john@example.com">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Organisation Type <span class="text-danger">*</span></label>
                        <select name="organisation_type" class="form-select" required>
                            <option value="Client" selected>Client</option>
                            <option value="Internal">Internal</option>
                            <option value="Third Party">Third Party</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Remark Notes</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter key notes, department, or location details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-save me-1"></i>Save Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Edit Contact Modal -->
<div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="editContactModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Contact
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('contacts/update') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="id" id="edit_contact_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Contact Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_contact_name" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Contact Number</label>
                            <input type="text" name="contact_number" id="edit_contact_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Mail ID / Email</label>
                            <input type="email" name="email" id="edit_contact_email" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Organisation Type <span class="text-danger">*</span></label>
                        <select name="organisation_type" id="edit_contact_organisation_type" class="form-select" required>
                            <option value="Client">Client</option>
                            <option value="Internal">Internal</option>
                            <option value="Third Party">Third Party</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Remark Notes</label>
                        <textarea name="remarks" id="edit_contact_remarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-save me-1"></i>Update Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Delete Contact Confirmation Modal (Admin Only) -->
<?php if (is_admin()): ?>
<div class="modal fade" id="deleteContactModal" tabindex="-1" aria-labelledby="deleteContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteContactModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Contact Confirmation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('contacts/delete') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="id" id="delete_contact_id">
                <div class="modal-body p-4 text-center">
                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold text-dark">Are you sure?</h5>
                    <p class="text-muted mb-0">
                        You are about to delete contact <strong id="delete_contact_name_text" class="text-dark"></strong>.
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
<?php endif; ?>

<!-- Modal 4: Bulk Import Contacts CSV Modal -->
<div class="modal fade" id="importContactModal" tabindex="-1" aria-labelledby="importContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="importContactModalLabel">
                    <i class="fas fa-file-upload me-2"></i>Import Contacts via CSV
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('contacts/import') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm mb-3 fs-7">
                        <i class="fas fa-info-circle me-2"></i>
                        Upload a CSV file containing contact records. Columns: Name, Organisation Type (Client/Internal/Third Party), Contact Number, Email, Remark Notes.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Select CSV File <span class="text-danger">*</span></label>
                        <input type="file" name="import_file" class="form-control" accept=".csv" required>
                    </div>

                    <div class="p-3 bg-light rounded border">
                        <small class="fw-bold d-block text-dark mb-1"><i class="fas fa-download me-1 text-primary"></i>Sample Template</small>
                        <small class="text-muted d-block mb-2">Download a sample formatted CSV template for contact bulk import.</small>
                        <a href="<?= base_url('contacts/sample-template') ?>" class="btn btn-sm btn-outline-primary fw-bold">
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
    // Edit Contact Modal Listener
    document.querySelectorAll('.btn-edit-contact').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('edit_contact_id').value = this.dataset.id;
            document.getElementById('edit_contact_name').value = this.dataset.name;
            document.getElementById('edit_contact_number').value = this.dataset.contact_number;
            document.getElementById('edit_contact_email').value = this.dataset.email;
            document.getElementById('edit_contact_organisation_type').value = this.dataset.organisation_type;
            document.getElementById('edit_contact_remarks').value = this.dataset.remarks;
            
            var modal = new bootstrap.Modal(document.getElementById('editContactModal'));
            modal.show();
        });
    });

    // Delete Contact Modal Listener
    document.querySelectorAll('.btn-delete-contact').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('delete_contact_id').value = this.dataset.id;
            document.getElementById('delete_contact_name_text').textContent = this.dataset.name;
            
            var modal = new bootstrap.Modal(document.getElementById('deleteContactModal'));
            modal.show();
        });
    });
});
</script>
