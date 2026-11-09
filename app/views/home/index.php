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
                        <input type="text" id="cpanelSearch" class="form-control form-control-lg ps-5 rounded-pill border-0 shadow-sm fs-7" placeholder="Type to filter menus (e.g. Analytics, Scorecard, Roster, Error, Settings)...">
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
                                <small class="text-white-50 fs-8">Role-based shortcuts to your active modules</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Group 1: Tasks -->
    <div class="cpanel-section mb-4">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-tasks text-primary"></i>Tasks
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
                            <small class="text-muted fs-8 d-block">Analytics & SLA metrics</small>
                        </div>
                    </div>
                </a>
            </div>

            <?php if (can_access_tasks() && is_ticket_workflow_enabled()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tickets/my-bucket') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-user-clock fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">My Bucket</h6>
                            <small class="text-muted fs-8 d-block">Active assigned tickets</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (!is_senior_management()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('roster') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-calendar-alt fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Daily Roster & Planner</h6>
                            <small class="text-muted fs-8 d-block">Shifts & daily task planning</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (can_access_tasks() && is_ticket_workflow_enabled()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tickets') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-secondary-subtle text-secondary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-ticket-alt fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">All Tasks</h6>
                            <small class="text-muted fs-8 d-block">Common task register for all users</small>
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
                            <small class="text-muted fs-8 d-block">Log ticket & Outlook files</small>
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
                            <small class="text-muted fs-8 d-block">Maker-checker task queue</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('lifecycle') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-primary">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-project-diagram fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Process Lifecycle Tracker</h6>
                            <small class="text-muted fs-8 d-block">12-stage ticket lifecycle & timestamps</small>
                        </div>
                    </div>
                </a>
            </div>

            <?php if (can_manage_workflows()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('workflows') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-primary">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-project-diagram fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Workflow Management</h6>
                            <small class="text-muted fs-8 d-block">Manage category & queue pipelines</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('queues') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-cubes fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Process Queues</h6>
                            <small class="text-muted fs-8 d-block">Process queue master records</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Group 2: Trackers -->
    <?php if (can_access_tasks()): ?>
    <div class="cpanel-section mb-4">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-database text-success"></i>Trackers
        </h5>

        <div class="row g-3">
            <?php if (is_production_count_enabled()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('performance/production') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-success">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-calculator fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Daily Production Logger</h6>
                            <small class="text-muted fs-8 d-block">Log daily unit count & target %</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (is_ticket_workflow_enabled()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tracker/query') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-info">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-question-circle fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Query Tracker</h6>
                            <small class="text-muted fs-8 d-block">Query tickets & customer SLA</small>
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
                            <small class="text-muted fs-8 d-block">Tickets on hold & release</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('tracker/input') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-inbox fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Input Tracker</h6>
                            <small class="text-muted fs-8 d-block">Incoming data & files</small>
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
                            <small class="text-muted fs-8 d-block">Dispatch & courier records</small>
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
                            <small class="text-muted fs-8 d-block">Mail archive & updates</small>
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
                            <small class="text-muted fs-8 d-block">Maker-checker error logs</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Group 3: Performance -->
    <div class="cpanel-section mb-4">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-trophy text-warning"></i>Performance
        </h5>

        <div class="row g-3">
            <?php if (can_view_analytics()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('performance/scorecard') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-warning">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-trophy fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Performance Scorecard</h6>
                            <small class="text-muted fs-8 d-block">Weighted scorecard & KPIs</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>



            <?php if (can_view_employees_and_skills()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('performance/config') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-sliders-h fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Team Metric Config</h6>
                            <small class="text-muted fs-8 d-block">Dynamic weightages & 100 pt targets</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (can_view_analytics()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('performance/analytics') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-chart-line fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Performance Analytics</h6>
                            <small class="text-muted fs-8 d-block">Multi-horizon performance trends</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if ((is_super_admin() || is_senior_management()) && can_access_page('performance_contest')): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('performance/contest') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-warning">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-flag-checkered fs-5 text-warning"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Grand Prix Contest</h6>
                            <small class="text-muted fs-8 d-block">Racing leaderboard & TV slideshow</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Group 4: Training -->
    <?php if (can_access_pkt()): ?>
    <div class="cpanel-section mb-4">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-graduation-cap text-info"></i>Training
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
                            <small class="text-muted fs-8 d-block">Activity checklist & KT files</small>
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
                            <small class="text-muted fs-8 d-block">Process MCQ knowledge tests</small>
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
                            <small class="text-muted fs-8 d-block">Maker-checker practice queue</small>
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
                            <small class="text-muted fs-8 d-block">Competency & certificates</small>
                        </div>
                    </div>
                </a>
            </div>

            <?php if (can_manage_pkt_bank()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('training/manage-pkt') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-dark">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-dark text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-file-import fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Manage PKT Question Bank</h6>
                            <small class="text-muted fs-8 d-block">Import Excel questions & tests</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Group 5: People -->
    <div class="cpanel-section mb-4">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-users text-primary"></i>People
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
                            <small class="text-muted fs-8 d-block">Client & team directory</small>
                        </div>
                    </div>
                </a>
            </div>

            <?php if (can_view_employees_and_skills()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('employees') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-users-cog fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Employee Accounts</h6>
                            <small class="text-muted fs-8 d-block">User accounts & roles</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('employees/skill-matrix') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-warning">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-sliders-h fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Skill Matrix Hub</h6>
                            <small class="text-muted fs-8 d-block">Skill levels & activity competency matrix</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Group 6: Administration -->
    <?php if (can_view_org_hierarchy() || can_manage_masters() || is_super_admin() || is_senior_management()): ?>
    <div class="cpanel-section mb-4">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-cogs text-secondary"></i>Administration
        </h5>

        <div class="row g-3">
            <?php if (can_view_org_hierarchy()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('master/hierarchy') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-info">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-sitemap fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Org Hierarchy Tree</h6>
                            <small class="text-muted fs-8 d-block">Organizational structure tree</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (can_manage_masters()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('master/projects') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-project-diagram fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Projects & Teams</h6>
                            <small class="text-muted fs-8 d-block">Configure projects & workflow mode</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('master/divisions') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-building fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Divisions Master</h6>
                            <small class="text-muted fs-8 d-block">Manage divisions structure</small>
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
                            <small class="text-muted fs-8 d-block">Manage process activities</small>
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
                            <small class="text-muted fs-8 d-block">Configure sub-activities & SLA</small>
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
                            <small class="text-muted fs-8 d-block">Manage ticket categories</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (is_super_admin() || is_senior_management()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('audit') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-history fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">System Audit Logs</h6>
                            <small class="text-muted fs-8 d-block">Audit log records & activity history</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>

            <?php if (is_super_admin()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('access-rights') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-primary">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-user-shield fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Access Rights Control</h6>
                            <small class="text-muted fs-8 d-block">Role & user page permissions</small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('settings') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3 border-start border-4 border-dark">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-dark text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-cog fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">System Settings</h6>
                            <small class="text-muted fs-8 d-block">Global config, dumps & system reset</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Group 7: Reports -->
    <?php if (can_view_reports()): ?>
    <div class="cpanel-section mb-4">
        <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-chart-bar text-primary"></i>Reports
        </h5>

        <div class="row g-3">
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('reports') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-chart-bar fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Reports & Export</h6>
                            <small class="text-muted fs-8 d-block">Custom reports & CSV export</small>
                        </div>
                    </div>
                </a>
            </div>

            <?php if (is_production_count_enabled()): ?>
            <div class="col-md-6 col-lg-3 cpanel-card-wrapper">
                <a href="<?= base_url('reports/production') ?>" class="card border-0 shadow-sm h-100 text-decoration-none hover-lift rounded-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="icon-box bg-info-subtle text-info-emphasis rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="fas fa-industry fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold text-dark mb-0 fs-7">Daywise Count</h6>
                            <small class="text-muted fs-8 d-block">Daily production count report</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
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
