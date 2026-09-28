<div class="container-fluid px-4 py-4">
    <!-- Welcome Header Banner -->
    <div class="card border-0 shadow-sm mb-4 bg-gradient-primary text-white rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0a4da2 100%);">
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
                    <h2 class="fw-bold mb-1">Welcome to DM-Ispark Control Panel</h2>
                    <p class="fs-7 text-white-50 mb-4">Business Operations & Performance Management System &bull; Select a module below to launch your workspace</p>

                    <!-- Search Filter Box -->
                    <div class="position-relative" style="max-width: 500px;">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
                        <input type="text" id="cpanelSearch" class="form-control form-control-lg ps-5 rounded-pill border-0 shadow-sm fs-7" placeholder="Type to filter module cards (e.g. Bucket, Roster, PKT, Error)...">
                    </div>
                </div>

                <div class="col-lg-4 mt-4 mt-lg-0 text-lg-end">
                    <div class="d-inline-flex flex-column gap-2 bg-white bg-opacity-10 p-3 rounded-4 text-start border border-white border-opacity-25">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="fas fa-inbox fs-5"></i>
                            </div>
                            <div>
                                <small class="text-white-50 d-block fs-8">My Bucket Tasks</small>
                                <strong class="fs-5 text-white"><?= $bucketCount ?> Tickets</strong>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 border-top border-white border-opacity-10 pt-2">
                            <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                <i class="fas fa-award fs-5"></i>
                            </div>
                            <div>
                                <small class="text-white-50 d-block fs-8">Certified Activities</small>
                                <strong class="fs-5 text-white"><?= $certifiedCount ?> Sub-Activities</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- cPanel Section 1: Main Operations -->
    <div class="cpanel-section mb-5">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-th-large text-primary"></i>Main Workflows & Operations
        </h5>
        
        <div class="row g-3">
            <!-- Card 1: Dashboard -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('dashboard') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-tachometer-alt fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Dashboard</h6>
                        <p class="text-muted fs-8 mb-0">Operational analytics, ticket metrics & live SLA radar.</p>
                    </div>
                </a>
            </div>

            <!-- Card 2: My Bucket -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tickets/my-bucket') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-user-clock fs-4"></i>
                            </div>
                            <span class="badge bg-danger rounded-pill"><?= $bucketCount ?></span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">My Bucket</h6>
                        <p class="text-muted fs-8 mb-0">Your active queue, allocated tickets & quick responses.</p>
                    </div>
                </a>
            </div>

            <!-- Card 3: Daily Roster -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('roster') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-calendar-alt fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Daily Roster & Planner</h6>
                        <p class="text-muted fs-8 mb-0">Attendance shifts, daily activity planning & roster allocation.</p>
                    </div>
                </a>
            </div>

            <!-- Card 4: Mail Tickets -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tickets') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-secondary-subtle text-secondary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-ticket-alt fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Mail Tickets</h6>
                        <p class="text-muted fs-8 mb-0">Global list of incoming email query and task tickets.</p>
                    </div>
                </a>
            </div>

            <!-- Card 5: Create Ticket -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tickets/create') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-success">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-plus-circle fs-4"></i>
                            </div>
                            <span class="badge bg-success">Outlook Drag & Drop</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Create Mail Ticket</h6>
                        <p class="text-muted fs-8 mb-0">Log query mail, drag & drop Outlook files, and calculate SLA TAT.</p>
                    </div>
                </a>
            </div>

            <!-- Card 6: Internal Tasks -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tasks') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-tasks fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Internal Tasks</h6>
                        <p class="text-muted fs-8 mb-0">Task creation, assignments, and maker-checker task queues.</p>
                    </div>
                </a>
            </div>

            <!-- Card 7: Hold List -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('hold') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-pause-circle fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Hold / Release List</h6>
                        <p class="text-muted fs-8 mb-0">Audit tickets placed on hold, reasons, and release timelines.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- cPanel Section 2: Trackers & Repository -->
    <div class="cpanel-section mb-5">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-database text-success"></i>Trackers & Information Repositories
        </h5>

        <div class="row g-3">
            <!-- Contact Manager -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('contacts') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-address-book fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Contact Manager</h6>
                        <p class="text-muted fs-8 mb-0">Client, internal team, and third-party contact organization.</p>
                    </div>
                </a>
            </div>

            <!-- Input Tracker -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tracker/input') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-inbox fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Input Tracker</h6>
                        <p class="text-muted fs-8 mb-0">Log incoming data sources, document references & assignments.</p>
                    </div>
                </a>
            </div>

            <!-- Delivery Tracker -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tracker/delivery') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-paper-plane fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Delivery Tracker</h6>
                        <p class="text-muted fs-8 mb-0">Dispatch records, email/courier modes & acknowledgments.</p>
                    </div>
                </a>
            </div>

            <!-- Process Updates Tracker -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tracker/process-updates') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-mail-bulk fs-4"></i>
                            </div>
                            <span class="badge bg-primary">Mail Preserver</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Process Updates Tracker</h6>
                        <p class="text-muted fs-8 mb-0">Preserve original Outlook mails, view HTML previews & billing notes.</p>
                    </div>
                </a>
            </div>

            <!-- Error Tracker -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('error-tracker') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-danger">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-exclamation-triangle fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Error Tracker</h6>
                        <p class="text-muted fs-8 mb-0">Error observations, internal/external audit logs & resolution analysis.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- cPanel Section 3: Training & PKT LMS -->
    <div class="cpanel-section mb-5">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-graduation-cap text-warning"></i>Training, PKT LMS & Certifications
        </h5>

        <div class="row g-3">
            <!-- Training Plan -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('training/plan') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-graduation-cap fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Training Plan & KT</h6>
                        <p class="text-muted fs-8 mb-0">Activity cards, sub-activity checklists & senior executive downloads.</p>
                    </div>
                </a>
            </div>

            <!-- PKT Test Center -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('training/pkt') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-clipboard-check fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">PKT Test Center</h6>
                        <p class="text-muted fs-8 mb-0">Online process knowledge MCQ exams, timers & scoring.</p>
                    </div>
                </a>
            </div>

            <!-- Practice Tasks -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('training/practice') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-file-signature fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Practice Files</h6>
                        <p class="text-muted fs-8 mb-0">Practice tasks & maker-checker error validation queue.</p>
                    </div>
                </a>
            </div>

            <!-- Certifications -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('training/certifications') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-award fs-4"></i>
                            </div>
                            <span class="badge bg-success"><?= $certifiedCount ?> Passed</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Certifications</h6>
                        <p class="text-muted fs-8 mb-0">Activity competency matrix & printable certificates.</p>
                    </div>
                </a>
            </div>

            <?php if (is_super_admin() || is_admin()): ?>
            <!-- Manage PKT Bank -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('training/manage-pkt') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-dark">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-dark text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-cogs fs-4"></i>
                            </div>
                            <span class="badge bg-dark">Excel Import</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Manage PKT Bank</h6>
                        <p class="text-muted fs-8 mb-0">Excel/CSV question import & raise published exams.</p>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- cPanel Section 4: Management & Administration -->
    <div class="cpanel-section mb-4">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-cogs text-secondary"></i>Management & Administration
        </h5>

        <div class="row g-3">
            <?php if (is_super_admin() || is_admin()): ?>
            <!-- Recurring Engine -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('recurring') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-redo fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Recurring Engine</h6>
                        <p class="text-muted fs-8 mb-0">Automated periodic task generation & schedules.</p>
                    </div>
                </a>
            </div>

            <!-- Employees & Skill Matrix -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('employees') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-users-cog fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Employees & Skill Matrix</h6>
                        <p class="text-muted fs-8 mb-0">User accounts, roles, and maker-checker activity mappings.</p>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (is_super_admin()): ?>
            <!-- Divisions Master -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('master/divisions') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-building fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Divisions Master</h6>
                        <p class="text-muted fs-8 mb-0">Manage operational divisions and organization structure.</p>
                    </div>
                </a>
            </div>

            <!-- Activities Master -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('master/activities') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-secondary-subtle text-secondary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-sitemap fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Activities Master</h6>
                        <p class="text-muted fs-8 mb-0">Define process activities under divisions.</p>
                    </div>
                </a>
            </div>

            <!-- Sub Activities Master -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('master/subactivities') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-list-ol fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Sub-Activities Master</h6>
                        <p class="text-muted fs-8 mb-0">SLA TAT hours & default employee allocations.</p>
                    </div>
                </a>
            </div>

            <!-- Categories Master -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('master/categories') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-tags fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Category Master</h6>
                        <p class="text-muted fs-8 mb-0">Global query categorizations & classifications.</p>
                    </div>
                </a>
            </div>

            <!-- Audit Logs -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('audit') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-history fs-4"></i>
                            </div>
                            <span class="badge bg-dark">Excel Dump</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">System Audit Logs</h6>
                        <p class="text-muted fs-8 mb-0">Full system audit trail & Excel data backup/import.</p>
                    </div>
                </a>
            </div>

            <!-- Settings -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('settings') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-dark text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-cog fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">System Settings</h6>
                        <p class="text-muted fs-8 mb-0">Global application settings & configuration.</p>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <!-- Reports & Export -->
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('reports') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fas fa-chart-bar fs-4"></i>
                            </div>
                            <i class="fas fa-arrow-right text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Reports & Export</h6>
                        <p class="text-muted fs-8 mb-0">Custom data filtering & CSV exports.</p>
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
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
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
