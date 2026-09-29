<div class="container-fluid px-4 py-4">
    <!-- Header Banner -->
    <div class="card border-0 shadow-sm mb-4 text-white rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0a4da2 100%);">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-white text-primary fw-bold px-3 py-1 fs-8 rounded-pill">
                            <i class="fas fa-shield-alt me-1"></i><?= htmlspecialchars($user['role_name'] ?? 'User') ?>
                        </span>
                        <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-1 fs-8 rounded-pill">
                            <i class="fas fa-building me-1"></i><?= htmlspecialchars($user['department'] ?? 'Operations') ?>
                        </span>
                    </div>
                    <h2 class="fw-bold mb-1">DM-Ispark Launchpad & Quick Links</h2>
                    <p class="fs-7 text-white-50 mb-4">Business Operations & Performance Management System &bull; Select a module below to launch your workspace</p>

                    <!-- Search Filter Box -->
                    <div class="position-relative" style="max-width: 500px;">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
                        <input type="text" id="cpanelSearch" class="form-control form-control-lg ps-5 rounded-pill border-0 shadow-sm fs-7" placeholder="Type to filter menus (e.g., Bucket, Training, Roster, Error)...">
                    </div>
                </div>

                <div class="col-lg-4 mt-4 mt-lg-0 text-lg-end">
                    <div class="d-inline-flex flex-column gap-2 bg-white bg-opacity-10 p-3 rounded-4 text-start border border-white border-opacity-25">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="fas fa-th-large fs-5"></i>
                            </div>
                            <div>
                                <strong class="fs-6 text-white d-block">Quick Access Hub</strong>
                                <small class="text-white-50 fs-8">Direct shortcuts to all system modules</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Group 1: Task Management & Operations -->
    <div class="cpanel-section mb-4">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-tasks text-primary"></i>Task & Workflows Management
        </h5>
        
        <div class="row g-3">
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('dashboard') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-tachometer-alt fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Dashboard</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Analytics, ticket metrics & SLA radar</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tickets/my-bucket') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-user-clock fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">My Bucket</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Your active allocated tickets & responses</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('roster') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-calendar-alt fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Daily Roster & Planner</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Shifts, daily activity planning & attendance</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tickets') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-secondary-subtle text-secondary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-ticket-alt fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Mail Tickets</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Global incoming email & query tickets</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tickets/create') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-success">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-plus-circle fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Create Mail Ticket</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Log ticket & drag-and-drop Outlook mail</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tasks') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-tasks fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Internal Tasks</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Task assignments & maker-checker queue</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('hold') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-pause-circle fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Hold / Release List</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Hold tickets register & quick release actions</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Group 2: Trackers & Utilities -->
    <div class="cpanel-section mb-4">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-database text-success"></i>Trackers & Utilities
        </h5>

        <div class="row g-3">
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('contacts') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-address-book fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Contact Manager</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Client & internal team contact directory</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tracker/input') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-inbox fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Input Tracker</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Incoming data sources & assignments</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tracker/delivery') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-paper-plane fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Delivery Tracker</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Dispatch records & delivery modes</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tracker/process-updates') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-primary">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-mail-bulk fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Process Updates Tracker</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Original mail reference & HTML previews</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('error-tracker') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-danger">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-exclamation-triangle fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Error Tracker</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Error observations & audit resolution logs</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Group 3: Knowledge & Certification LMS -->
    <div class="cpanel-section mb-4">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-graduation-cap text-warning"></i>Knowledge & Certification LMS
        </h5>

        <div class="row g-3">
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('training/plan') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-graduation-cap fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Training Plan & KT</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Activity checklist & executive downloads</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('training/pkt') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-clipboard-check fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">PKT Test Center</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Online MCQ process knowledge exams</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('training/practice') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-file-signature fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Practice Files</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Maker-checker evaluation & error logging</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('training/certifications') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-award fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Certifications</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Competency matrix & printable certificates</small>
                        </div>
                    </div>
                </a>
            </div>

            <?php if (is_super_admin() || is_admin()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('training/manage-pkt') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-dark">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-dark text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-file-import fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Manage PKT Question Bank</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Excel question import & create tests</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Group 4: Management & Administration -->
    <div class="cpanel-section mb-4">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-cogs text-secondary"></i>Management & Administration
        </h5>

        <div class="row g-3">
            <?php if (is_super_admin() || is_admin()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('recurring') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-redo fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Recurring Engine</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Automated periodic task schedules</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('employees') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-users-cog fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Employees & Skill Matrix</h6>
                            <small class="text-muted fs-8 d-block text-truncate">User accounts & maker-checker mapping</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (is_super_admin()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('master/divisions') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-building fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Divisions Master</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Operational divisions structure</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('master/activities') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-secondary-subtle text-secondary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-sitemap fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Activities Master</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Process activities under divisions</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('master/subactivities') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-list-ol fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Sub-Activities Master</h6>
                            <small class="text-muted fs-8 d-block text-truncate">SLA TAT & default employee mapping</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('master/categories') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-tags fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Category Master</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Query categories & classifications</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('audit') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-history fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">System Audit Logs</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Audit trail & Excel data backup</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('settings') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-dark text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-cog fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">System Settings</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Global application settings</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('reports') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-chart-bar fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Reports & Export</h6>
                            <small class="text-muted fs-8 d-block text-truncate">Custom data filtering & CSV exports</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('cpanelSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var q = this.value.toLowerCase().trim();
            var cards = document.querySelectorAll('.cpanel-card-wrapper');
            cards.forEach(function(card) {
                var text = card.innerText.toLowerCase();
                if (text.includes(q)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>
