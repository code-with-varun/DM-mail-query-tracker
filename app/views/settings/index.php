<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">System Settings & Data Management</h4>
            <p class="text-muted fs-7 mb-0">Super Admin Global System Configuration, Database Dumps, Master Excel Import/Export & System Reset Controls</p>
        </div>
        <?php if (is_super_admin()): ?>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= base_url('settings/export_excel') ?>" class="btn btn-outline-success btn-sm fw-bold" title="Export all database tables into a multi-sheet Excel workbook">
                <i class="fas fa-file-excel me-1"></i>Export Master Excel (.xlsx)
            </a>
            <button type="button" class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#importExcelModal" title="Import multi-sheet Excel file into database tables">
                <i class="fas fa-file-import me-1"></i>Import Master Excel (.xlsx)
            </button>
            <a href="<?= base_url('settings/export_backup') ?>" class="btn btn-outline-primary btn-sm fw-bold" title="Download raw SQL dump">
                <i class="fas fa-database me-1"></i>SQL Dump (.sql)
            </a>
            <button type="button" class="btn btn-danger btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#resetSystemModal">
                <i class="fas fa-undo-alt me-1"></i>Reset System Data
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Master Excel Data Backup & Restore Banner -->
    <?php if (is_super_admin()): ?>
    <div class="card border-primary shadow-sm mb-4 bg-light">
        <div class="card-body p-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-database fs-2 text-primary"></i>
                <div>
                    <strong class="d-block text-dark fs-6">Database Dumps, Master Excel Backup & Multi-Sheet Restore</strong>
                    <span class="text-muted fs-7">
                        Export all system tables into a multi-sheet Excel workbook (<code>.xlsx</code>) or SQL dump (<code>.sql</code>), or upload master Excel templates (like <code>DM-ISPARK-MASTER.xlsx</code>) to restore system tables and master records.
                    </span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form action="<?= base_url('settings') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
        
        <div class="row g-4">
            <!-- General Organization Settings -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sliders-h text-primary me-2"></i>General Application Settings</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold text-dark">Application Display Title</label>
                            <input type="text" name="app_title" class="form-control" value="<?= htmlspecialchars($settings['app_title']['value'] ?? 'Mail Query Tracker') ?>" required>
                            <small class="text-muted fs-8">App header branding title</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold text-dark">Organization / Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($settings['company_name']['value'] ?? 'Datamatics') ?>" required>
                            <small class="text-muted fs-8">Used in footer and generated reports</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold text-dark">Support / Admin Email</label>
                            <input type="email" name="support_email" class="form-control" value="<?= htmlspecialchars($settings['support_email']['value'] ?? 'support@datamatics.com') ?>" required>
                            <small class="text-muted fs-8">Contact email for system notifications</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SLA & Assignment Settings -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-clock text-warning me-2"></i>SLA & Ticket Assignment Rules</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold text-dark">Global Default SLA TAT (Hours)</label>
                            <input type="number" name="default_tat_hours" class="form-control" value="<?= htmlspecialchars($settings['default_tat_hours']['value'] ?? '24') ?>" min="1" required>
                            <small class="text-muted fs-8">Fallback TAT hours when sub-activity SLA is not set</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold text-dark">Session Inactivity Timeout (Minutes)</label>
                            <input type="number" name="session_timeout_minutes" class="form-control" value="<?= htmlspecialchars($settings['session_timeout_minutes']['value'] ?? '30') ?>" min="5" required>
                            <small class="text-muted fs-8">Auto-logout duration for inactive user sessions</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold text-dark">Auto-Assign Default Employee on Sub-Activity Select</label>
                            <select name="auto_assign_employee" class="form-select">
                                <option value="1" <?= ($settings['auto_assign_employee']['value'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled (Auto-select default employee from DB mapping)</option>
                                <option value="0" <?= ($settings['auto_assign_employee']['value'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled (Keep unassigned until manual selection)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Security & Preferences -->
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-shield-alt text-danger me-2"></i>Notifications & Maintenance Controls</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-bold text-dark">Ticket Assignment Alert Notifications</label>
                                <select name="notify_on_assignment" class="form-select">
                                    <option value="1" <?= ($settings['notify_on_assignment']['value'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled (Create notification alerts when assigned)</option>
                                    <option value="0" <?= ($settings['notify_on_assignment']['value'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-bold text-dark">System Maintenance Mode</label>
                                <select name="maintenance_mode" class="form-select">
                                    <option value="0" <?= ($settings['maintenance_mode']['value'] ?? '0') == '0' ? 'selected' : '' ?>>Disabled (Normal Operation)</option>
                                    <option value="1" <?= ($settings['maintenance_mode']['value'] ?? '0') == '1' ? 'selected' : '' ?>>Enabled (Restrict non-admin logins)</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary fw-bold px-4">
                                <i class="fas fa-save me-2"></i>Save System Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal: Full System Reset Confirmation -->
<?php if (is_super_admin()): ?>
<div class="modal fade" id="resetSystemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('settings/reset') ?>" method="POST">
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
            <form action="<?= base_url('settings/import_excel') ?>" method="POST" enctype="multipart/form-data">
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
