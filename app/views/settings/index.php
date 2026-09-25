<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">System Settings</h4>
            <p class="text-muted fs-7 mb-0">Super Admin Global System Configuration, SLA Defaults & Security Controls</p>
        </div>
    </div>

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
