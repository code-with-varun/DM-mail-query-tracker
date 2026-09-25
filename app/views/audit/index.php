<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">System Audit Logs & Project Reset</h4>
            <p class="text-muted fs-7 mb-0">Immutable system activity logs, database dumps, and Super Admin project reset</p>
        </div>
        <?php if (is_super_admin()): ?>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('audit/export-excel') ?>" class="btn btn-outline-success btn-sm fw-bold" title="Export all database tables into a multi-sheet Excel workbook">
                <i class="fas fa-file-excel me-1"></i>Export Master Excel (.xlsx)
            </a>
            <button type="button" class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#importExcelModal" title="Import multi-sheet Excel file into database tables">
                <i class="fas fa-file-import me-1"></i>Import Master Excel (.xlsx)
            </button>
            <a href="<?= base_url('audit/export-backup') ?>" class="btn btn-outline-primary btn-sm fw-bold" title="Download raw SQL dump">
                <i class="fas fa-database me-1"></i>SQL Dump (.sql)
            </a>
            <button type="button" class="btn btn-danger btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#resetSystemModal">
                <i class="fas fa-undo-alt me-1"></i>Reset System Data
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Info Card for Super Admin -->
    <?php if (is_super_admin()): ?>
    <div class="card border-primary shadow-sm mb-4 bg-light">
        <div class="card-body p-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-file-excel fs-2 text-success"></i>
                <div>
                    <strong class="d-block text-dark fs-6">Master Excel Data Backup & Multi-Sheet Restore</strong>
                    <span class="text-muted fs-7">
                        Export all system tables into a multi-sheet Excel workbook (`.xlsx`), or upload master Excel templates (like <code>DM-ISPARK-MASTER.xlsx</code>) to restore users, contacts, divisions, activities, sub-activities, and error logs after a system reset.
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Audit Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>IP Address</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $l): ?>
                        <tr>
                            <td class="fs-8 text-muted"><?= format_datetime($l['created_at']) ?></td>
                            <td class="fw-bold text-dark"><?= $l['full_name'] ? htmlspecialchars($l['full_name']) . " (" . $l['user_code'] . ")" : 'System / Unauthenticated' ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($l['module']) ?></span></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($l['action']) ?></span></td>
                            <td class="fs-8 font-monospace"><?= htmlspecialchars($l['ip_address'] ?? '127.0.0.1') ?></td>
                            <td class="fs-8">
                                <?php if ($l['new_values']): ?>
                                    <code><?= htmlspecialchars(substr($l['new_values'], 0, 100)) ?></code>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Full System Reset Confirmation -->
<?php if (is_super_admin()): ?>
<div class="modal fade" id="resetSystemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('audit/reset') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-radiation me-2"></i>Full Project System Reset</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger mb-3 fs-7">
                        <i class="fas fa-exclamation-triangle me-2 fs-5 float-start"></i>
                        <strong>WARNING: THIS ACTION CANNOT BE UNDONE!</strong><br>
                        This will permanently delete:
                        <ul class="mb-0 mt-1 ps-3">
                            <li>All Query Tickets & Task Tickets</li>
                            <li>Divisions, Activities, Sub-activities & Master Categories</li>
                            <li>Error Tracker Observations & Logs</li>
                            <li>Contact Directory & Delivery Trackers</li>
                            <li>All Comments & Status History</li>
                            <li>All Uploaded File Attachments</li>
                            <li>All Recurring Task Templates & History</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Type "RESET" to confirm <span class="text-danger">*</span></label>
                        <input type="text" name="confirm_text" class="form-control fw-bold" placeholder="Type RESET in capital letters" required autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Super Admin Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Enter Super Admin password" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">
                        <i class="fas fa-trash-restore me-1"></i>Confirm & Reset Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Import Master Excel Data -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('audit/import_excel') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-import me-2"></i>Import Master Excel Data (.xlsx)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info mb-3 fs-7">
                        <i class="fas fa-info-circle me-1 fs-5 float-start"></i>
                        Upload a multi-sheet Excel file (e.g. <code>DM-ISPARK-MASTER.xlsx</code> or exported master backup). Each sheet name must match a database table (e.g. <code>users</code>, <code>contacts</code>, <code>divisions</code>, <code>activities</code>, <code>sub_activities</code>, <code>error_tracker</code>).
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold text-dark">Select Excel File (.xlsx, .xls) <span class="text-danger">*</span></label>
                        <input type="file" name="excel_file" class="form-control" accept=".xlsx, .xls" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        <i class="fas fa-upload me-1"></i>Start Excel Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
