<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Mail Query & Task Tickets</h4>
            <p class="text-muted fs-7 mb-0">Centralized Query Register and SLA Tracker</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if (is_super_admin() || is_admin()): ?>
                <a href="<?= base_url('tickets/template') ?>" class="btn btn-outline-success btn-sm fw-bold" title="Download Excel CSV Template">
                    <i class="fas fa-file-excel me-1"></i>Download Template
                </a>
                <a href="<?= base_url('tickets/import_view') ?>" class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#bulkImportModal" title="Import Tickets from Excel/CSV">
                    <i class="fas fa-file-upload me-1"></i>Bulk Import
                </a>
            <?php endif; ?>
            <a href="<?= base_url('tickets/create') ?>" class="btn btn-primary btn-sm fw-bold ms-1">
                <i class="fas fa-plus-circle me-1"></i>New Ticket
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="<?= base_url('tickets') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ticket #, subject, agency..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <?php foreach (['New', 'Assigned', 'In Progress', 'Pending', 'Waiting for Customer', 'Waiting for Internal Team', 'On Hold', 'Released', 'Completed', 'Closed', 'Cancelled'] as $st): ?>
                            <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="activity_id" class="form-select form-select-sm">
                        <option value="">All Activities</option>
                        <?php foreach ($activities as $act): ?>
                            <option value="<?= $act['id'] ?>" <?= ($filters['activity_id'] ?? '') == $act['id'] ? 'selected' : '' ?>><?= htmlspecialchars($act['activity_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="allocated_to" class="form-select form-select-sm">
                        <option value="">All Assignees</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($filters['allocated_to'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?= base_url('tickets') ?>" class="btn btn-sm btn-light border"><i class="fas fa-undo"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Listing Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket #</th>
                            <th>Type</th>
                            <th>Received Date</th>
                            <th>From / Agency</th>
                            <th>Subject</th>
                            <th>Activity</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>TAT SLA</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td class="fw-bold text-primary"><?= htmlspecialchars($t['ticket_number']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($t['ticket_type']) ?></span></td>
                            <td class="fs-8"><?= format_datetime($t['received_datetime']) ?></td>
                            <td>
                                <div class="fw-bold fs-7"><?= htmlspecialchars($t['from_address']) ?></div>
                                <?php if (!empty($t['agency_code'])): ?>
                                    <small class="text-muted">Agency: <?= htmlspecialchars($t['agency_code']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold fs-7"><?= htmlspecialchars($t['subject']) ?></div>
                            </td>
                            <td class="fs-8">
                                <div><?= htmlspecialchars($t['activity_name'] ?? 'N/A') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($t['sub_activity_name'] ?? '') ?></small>
                            </td>
                            <td class="fs-8"><?= htmlspecialchars($t['allocated_user_name'] ?? 'Unassigned') ?></td>
                            <td><?= get_status_badge($t['status']) ?></td>
                            <td><?= get_tat_badge($t['tat_datetime'], $t['status']) ?></td>
                            <td>
                                <a href="<?= base_url('tickets/view/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Import Modal -->
<?php if (is_super_admin() || is_admin()): ?>
<div class="modal fade" id="bulkImportModal" tabindex="-1" aria-labelledby="bulkImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('tickets/import') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="bulkImportModalLabel"><i class="fas fa-file-excel me-2"></i>Bulk Ticket Excel / CSV Import</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm fs-7 mb-3">
                        <i class="fas fa-info-circle me-1"></i>
                        Download the official template to dump your query and task tickets, then upload the completed <strong>.csv</strong> or <strong>.xlsx</strong> file below.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold fs-7">Select CSV / Excel File <span class="text-danger">*</span></label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv, .xlsx, .xls" required>
                    </div>

                    <div class="p-3 bg-light rounded border fs-8 text-muted">
                        <div class="fw-bold text-dark mb-1"><i class="fas fa-list-check me-1 text-success"></i>Supported Column Headers:</div>
                        <ul class="mb-0 ps-3">
                            <li><strong>Ticket Type</strong>: Query Ticket / Task Ticket</li>
                            <li><strong>Activity Name</strong>: Agency Billing, Empanelment, Inhouse, Vendor</li>
                            <li><strong>Priority</strong>: Low, Medium, High, Critical</li>
                            <li><strong>Allocated Employee Code</strong>: EMP002, EMP003, etc.</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <a href="<?= base_url('tickets/template') ?>" class="btn btn-outline-secondary me-auto btn-sm fw-bold">
                        <i class="fas fa-download me-1"></i>Download Template
                    </a>
                    <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold"><i class="fas fa-upload me-1"></i>Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
