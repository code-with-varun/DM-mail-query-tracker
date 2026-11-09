<!-- Sidebar -->
<div id="sidebar-wrapper">
    <div class="sidebar-brand">
        <div class="brand-title-box d-flex align-items-center gap-2">
            <img src="<?= base_url('public/assets/logo/Datamatics-Responsive-Logo.png') ?>" alt="Datamatics" class="brand-logo-img" style="height: 32px; width: 32px; object-fit: contain;">
            <span class="brand-text fs-7 fw-bold text-white" title="Business Operations & Performance Management System">DM-Ispark</span>
        </div>
        <!-- Hamburger Collapse Toggle Button on Sidebar Top -->
        <button class="btn btn-sm text-secondary border-0 p-1 ms-auto flex-shrink-0" id="sidebarToggle" type="button" title="Toggle Sidebar Collapse">
            <i class="fas fa-bars fs-6"></i>
        </button>
    </div>
    
    <?php
    $currentRoute = trim($_GET['url'] ?? '', '/');
    if (!function_exists('is_sb_active')) {
        function is_sb_active(string $target, string $currentRoute): bool {
            if ($target === 'home') {
                return $currentRoute === '' || $currentRoute === 'home';
            }
            if ($target === 'tickets') {
                return $currentRoute === 'tickets';
            }
            if ($target === 'reports') {
                return $currentRoute === 'reports';
            }
            if ($target === 'tracker/query') {
                return $currentRoute === 'tracker/query' || $currentRoute === 'query-tracker';
            }
            if ($target === 'master/activities') {
                return $currentRoute === 'master/activities';
            }
            return $currentRoute === $target || str_starts_with($currentRoute, $target . '/');
        }
    }
    // Detect active groups to auto-expand the section containing current page
    $isTasksWorkspaceActive = is_sb_active('home', $currentRoute) || is_sb_active('dashboard', $currentRoute) || is_sb_active('tickets/my-bucket', $currentRoute) || is_sb_active('roster', $currentRoute) || is_sb_active('tickets', $currentRoute) || is_sb_active('tickets/create', $currentRoute) || is_sb_active('tasks', $currentRoute) || is_sb_active('lifecycle', $currentRoute);
    $isPerformanceActive = is_sb_active('performance/scorecard', $currentRoute) || is_sb_active('performance/analytics', $currentRoute) || is_sb_active('performance/contest', $currentRoute) || is_sb_active('performance/awards', $currentRoute) || is_sb_active('performance/config', $currentRoute);
    $isReportsActive = is_sb_active('reports', $currentRoute) || is_sb_active('reports/production', $currentRoute);
    $isTrackersActive = is_sb_active('performance/production', $currentRoute) || is_sb_active('tracker/timesheet', $currentRoute) || is_sb_active('tracker/query', $currentRoute) || is_sb_active('hold', $currentRoute) || is_sb_active('tracker/input', $currentRoute) || is_sb_active('tracker/delivery', $currentRoute) || is_sb_active('tracker/process-updates', $currentRoute) || is_sb_active('error-tracker', $currentRoute);
    $isDirectoriesActive = is_sb_active('contacts', $currentRoute) || is_sb_active('employees', $currentRoute) || is_sb_active('employees/skill-matrix', $currentRoute);
    $isTrainingActive = is_sb_active('training/plan', $currentRoute) || is_sb_active('training/pkt', $currentRoute) || is_sb_active('training/practice', $currentRoute) || is_sb_active('training/certifications', $currentRoute) || is_sb_active('training/manage-pkt', $currentRoute);
    $isWorkflowsActive = is_sb_active('workflows', $currentRoute) || is_sb_active('queues', $currentRoute);
    $isAdministrationActive = is_sb_active('master/hierarchy', $currentRoute) || is_sb_active('master/projects', $currentRoute) || is_sb_active('master/divisions', $currentRoute) || is_sb_active('master/activities', $currentRoute) || is_sb_active('master/subactivities', $currentRoute) || is_sb_active('master/categories', $currentRoute) || is_sb_active('audit', $currentRoute) || is_sb_active('access-rights', $currentRoute) || is_sb_active('settings', $currentRoute);

    // Default to Tasks & Workspace expanded if on root or unspecified route
    if (!$isTasksWorkspaceActive && !$isPerformanceActive && !$isReportsActive && !$isTrackersActive && !$isDirectoriesActive && !$isTrainingActive && !$isWorkflowsActive && !$isAdministrationActive) {
        $isTasksWorkspaceActive = true;
    }
    ?>

    <div class="nav-menu-container">
        <div class="list-group list-group-flush py-2" id="sidebar-menu-accordion">
            
            <!-- SECTION 1: TASKS & WORKSPACE (MERGED) -->
            <?php 
            $tasksWorkspaceAllowed = (can_access_page('my_bucket') && is_ticket_workflow_enabled()) || can_access_page('roster') || (can_access_page('all_tasks') && is_ticket_workflow_enabled()) || (can_access_page('create_ticket') && is_ticket_workflow_enabled()) || (can_access_page('internal_tasks') && is_ticket_workflow_enabled()) || (can_access_page('lifecycle') && is_ticket_workflow_enabled());
            if ($tasksWorkspaceAllowed): 
            ?>
            <div class="sidebar-group">
                <div class="sidebar-heading <?= $isTasksWorkspaceActive ? 'has-active-child' : 'collapsed' ?>" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#sb-group-workspace" 
                     role="button" 
                     aria-expanded="<?= $isTasksWorkspaceActive ? 'true' : 'false' ?>"
                     aria-controls="sb-group-workspace"
                     title="Click to toggle Tasks & Workspace">
                    <span class="group-title">
                        <i class="fas fa-tasks group-icon"></i>
                        <span>TASKS & WORKSPACE</span>
                    </span>
                    <i class="fas fa-chevron-down group-arrow"></i>
                </div>
                <div class="collapse <?= $isTasksWorkspaceActive ? 'show' : '' ?> sidebar-submenu" id="sb-group-workspace" data-bs-parent="#sidebar-menu-accordion">
                    <?php if (can_access_page('my_bucket') && is_ticket_workflow_enabled()): ?>
                    <a href="<?= base_url('tickets/my-bucket') ?>" class="nav-link <?= is_sb_active('tickets/my-bucket', $currentRoute) ? 'active' : '' ?>" title="My Bucket">
                        <i class="fas fa-user-clock"></i> <span>My Bucket</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('all_tasks') && is_ticket_workflow_enabled()): ?>
                    <a href="<?= base_url('tickets') ?>" class="nav-link <?= is_sb_active('tickets', $currentRoute) ? 'active' : '' ?>" title="All Tasks (Common Access)">
                        <i class="fas fa-list-alt"></i> <span>All Tasks</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('create_ticket') && is_ticket_workflow_enabled()): ?>
                    <a href="<?= base_url('tickets/create') ?>" class="nav-link <?= is_sb_active('tickets/create', $currentRoute) ? 'active' : '' ?>" title="Create Mail Ticket">
                        <i class="fas fa-plus-circle"></i> <span>Create Mail Ticket</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('internal_tasks') && is_ticket_workflow_enabled()): ?>
                    <a href="<?= base_url('tasks') ?>" class="nav-link <?= is_sb_active('tasks', $currentRoute) ? 'active' : '' ?>" title="Internal Tasks">
                        <i class="fas fa-clipboard-check"></i> <span>Internal Tasks</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('lifecycle') && is_ticket_workflow_enabled()): ?>
                    <a href="<?= base_url('lifecycle') ?>" class="nav-link <?= is_sb_active('lifecycle', $currentRoute) ? 'active' : '' ?>" title="Process Lifecycle Tracker">
                        <i class="fas fa-project-diagram"></i> <span>Lifecycle Tracker</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('roster')): ?>
                    <a href="<?= base_url('roster') ?>" class="nav-link <?= is_sb_active('roster', $currentRoute) ? 'active' : '' ?>" title="Daily Roster & Planner">
                        <i class="fas fa-calendar-alt"></i> <span>Daily Roster</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- SECTION 2: PERFORMANCE -->
            <?php if (can_access_module('performance')): ?>
            <div class="sidebar-group">
                <div class="sidebar-heading <?= $isPerformanceActive ? 'has-active-child' : 'collapsed' ?>" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#sb-group-performance" 
                     role="button" 
                     aria-expanded="<?= $isPerformanceActive ? 'true' : 'false' ?>"
                     aria-controls="sb-group-performance"
                     title="Click to toggle Performance">
                    <span class="group-title">
                        <i class="fas fa-chart-line group-icon"></i>
                        <span>PERFORMANCE</span>
                    </span>
                    <i class="fas fa-chevron-down group-arrow"></i>
                </div>
                <div class="collapse <?= $isPerformanceActive ? 'show' : '' ?> sidebar-submenu" id="sb-group-performance" data-bs-parent="#sidebar-menu-accordion">
                    <?php if (can_access_page('performance_scorecard')): ?>
                    <a href="<?= base_url('performance/scorecard') ?>" class="nav-link <?= is_sb_active('performance/scorecard', $currentRoute) ? 'active' : '' ?>" title="Employee Performance Scorecard">
                        <i class="fas fa-trophy"></i> <span>Performance Scorecard</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('performance_analytics')): ?>
                    <a href="<?= base_url('performance/analytics') ?>" class="nav-link <?= is_sb_active('performance/analytics', $currentRoute) ? 'active' : '' ?>" title="Multi-Horizon Performance Analytics">
                        <i class="fas fa-chart-area"></i> <span>Performance Analytics</span>
                    </a>
                    <?php endif; ?>
                    <?php if ((is_super_admin() || is_senior_management()) && can_access_page('performance_contest')): ?>
                    <a href="<?= base_url('performance/contest') ?>" class="nav-link <?= is_sb_active('performance/contest', $currentRoute) ? 'active' : '' ?>" title="Gamified Grand Prix Performance Contest & Slideshow">
                        <i class="fas fa-flag-checkered text-warning"></i> <span>Grand Prix Contest</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('performance_awards')): ?>
                    <a href="<?= base_url('performance/awards') ?>" class="nav-link <?= is_sb_active('performance/awards', $currentRoute) ? 'active' : '' ?>" title="Datamatics Annual Achievers Awards & Monthly Recognition">
                        <i class="fas fa-medal text-warning"></i> <span>Awards & Honours Hub</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('team_kpi_config')): ?>
                    <a href="<?= base_url('performance/config') ?>" class="nav-link <?= is_sb_active('performance/config', $currentRoute) ? 'active' : '' ?>" title="Team Metric Weightage Configurator">
                        <i class="fas fa-sliders-h"></i> <span>Team Metric Config</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- SECTION 3: REPORTS -->
            <?php if (can_access_module('reports')): ?>
            <div class="sidebar-group">
                <div class="sidebar-heading <?= $isReportsActive ? 'has-active-child' : 'collapsed' ?>" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#sb-group-reports" 
                     role="button" 
                     aria-expanded="<?= $isReportsActive ? 'true' : 'false' ?>"
                     aria-controls="sb-group-reports"
                     title="Click to toggle Reports">
                    <span class="group-title">
                        <i class="fas fa-chart-bar group-icon"></i>
                        <span>REPORTS</span>
                    </span>
                    <i class="fas fa-chevron-down group-arrow"></i>
                </div>
                <div class="collapse <?= $isReportsActive ? 'show' : '' ?> sidebar-submenu" id="sb-group-reports" data-bs-parent="#sidebar-menu-accordion">
                    <?php if (can_access_page('reports_export')): ?>
                    <a href="<?= base_url('reports') ?>" class="nav-link <?= is_sb_active('reports', $currentRoute) ? 'active' : '' ?>" title="Reports & Export">
                        <i class="fas fa-file-export"></i> <span>Reports & Export</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('daywise_count') && is_production_count_enabled()): ?>
                    <a href="<?= base_url('reports/production') ?>" class="nav-link <?= is_sb_active('reports/production', $currentRoute) ? 'active' : '' ?>" title="Daywise Count Tracker">
                        <i class="fas fa-industry"></i> <span>Daywise Count</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- SECTION 5: TRACKERS -->
            <?php if (can_access_module('trackers')): ?>
            <div class="sidebar-group">
                <div class="sidebar-heading <?= $isTrackersActive ? 'has-active-child' : 'collapsed' ?>" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#sb-group-trackers" 
                     role="button" 
                     aria-expanded="<?= $isTrackersActive ? 'true' : 'false' ?>"
                     aria-controls="sb-group-trackers"
                     title="Click to toggle Trackers">
                    <span class="group-title">
                        <i class="fas fa-clipboard-list group-icon"></i>
                        <span>TRACKERS</span>
                    </span>
                    <i class="fas fa-chevron-down group-arrow"></i>
                </div>
                <div class="collapse <?= $isTrackersActive ? 'show' : '' ?> sidebar-submenu" id="sb-group-trackers" data-bs-parent="#sidebar-menu-accordion">
                    <?php if (can_access_page('daily_production') && is_production_count_enabled()): ?>
                    <a href="<?= base_url('performance/production') ?>" class="nav-link <?= is_sb_active('performance/production', $currentRoute) ? 'active' : '' ?>" title="Daily Production & Count Logger">
                        <i class="fas fa-calculator"></i> <span>Daily Production Logger</span>
                    </a>
                    <?php endif; ?>
                    <a href="<?= base_url('tracker/timesheet') ?>" class="nav-link <?= is_sb_active('tracker/timesheet', $currentRoute) ? 'active' : '' ?>" title="Daily Timesheet & Effort Tracker">
                        <i class="fas fa-business-time"></i> <span>Timesheet Tracker</span>
                    </a>
                    <?php if (can_access_page('query_tracker') && is_ticket_workflow_enabled()): ?>
                    <a href="<?= base_url('tracker/query') ?>" class="nav-link <?= is_sb_active('tracker/query', $currentRoute) ? 'active' : '' ?>" title="Query Tracker">
                        <i class="fas fa-question-circle"></i> <span>Query Tracker</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('hold_list') && is_ticket_workflow_enabled()): ?>
                    <a href="<?= base_url('hold') ?>" class="nav-link <?= is_sb_active('hold', $currentRoute) ? 'active' : '' ?>" title="Hold / Release List">
                        <i class="fas fa-pause-circle"></i> <span>Hold / Release List</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('input_tracker')): ?>
                    <a href="<?= base_url('tracker/input') ?>" class="nav-link <?= is_sb_active('tracker/input', $currentRoute) ? 'active' : '' ?>" title="Input Tracker">
                        <i class="fas fa-inbox"></i> <span>Input Tracker</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('delivery_tracker')): ?>
                    <a href="<?= base_url('tracker/delivery') ?>" class="nav-link <?= is_sb_active('tracker/delivery', $currentRoute) ? 'active' : '' ?>" title="Delivery Tracker">
                        <i class="fas fa-paper-plane"></i> <span>Delivery Tracker</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('process_updates')): ?>
                    <a href="<?= base_url('tracker/process-updates') ?>" class="nav-link <?= is_sb_active('tracker/process-updates', $currentRoute) ? 'active' : '' ?>" title="Process Updates Tracker">
                        <i class="fas fa-mail-bulk"></i> <span>Process Updates Tracker</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('error_tracker')): ?>
                    <a href="<?= base_url('error-tracker') ?>" class="nav-link <?= is_sb_active('error-tracker', $currentRoute) ? 'active' : '' ?>" title="Error Tracker">
                        <i class="fas fa-exclamation-triangle"></i> <span>Error Tracker</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- SECTION 6: PEOPLE -->
            <?php if (can_access_module('directories')): ?>
            <div class="sidebar-group">
                <div class="sidebar-heading <?= $isDirectoriesActive ? 'has-active-child' : 'collapsed' ?>" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#sb-group-directories" 
                     role="button" 
                     aria-expanded="<?= $isDirectoriesActive ? 'true' : 'false' ?>"
                     aria-controls="sb-group-directories"
                     title="Click to toggle People">
                    <span class="group-title">
                        <i class="fas fa-users group-icon"></i>
                        <span>PEOPLE</span>
                    </span>
                    <i class="fas fa-chevron-down group-arrow"></i>
                </div>
                <div class="collapse <?= $isDirectoriesActive ? 'show' : '' ?> sidebar-submenu" id="sb-group-directories" data-bs-parent="#sidebar-menu-accordion">
                    <?php if (can_access_page('contacts')): ?>
                    <a href="<?= base_url('contacts') ?>" class="nav-link <?= is_sb_active('contacts', $currentRoute) ? 'active' : '' ?>" title="Contact Manager">
                        <i class="fas fa-address-book"></i> <span>Contact Manager</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('employees')): ?>
                    <a href="<?= base_url('employees') ?>" class="nav-link <?= is_sb_active('employees', $currentRoute) ? 'active' : '' ?>" title="Employee & User Management">
                        <i class="fas fa-users-cog"></i> <span>Employee Accounts</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('skill_matrix')): ?>
                    <a href="<?= base_url('employees/skill-matrix') ?>" class="nav-link <?= is_sb_active('employees/skill-matrix', $currentRoute) ? 'active' : '' ?>" title="Dedicated Skill Matrix Alignment">
                        <i class="fas fa-network-wired"></i> <span>Skill Matrix Hub</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- SECTION 7: TRAINING -->
            <?php if (can_access_module('training')): ?>
            <div class="sidebar-group">
                <div class="sidebar-heading <?= $isTrainingActive ? 'has-active-child' : 'collapsed' ?>" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#sb-group-training" 
                     role="button" 
                     aria-expanded="<?= $isTrainingActive ? 'true' : 'false' ?>"
                     aria-controls="sb-group-training"
                     title="Click to toggle Training">
                    <span class="group-title">
                        <i class="fas fa-graduation-cap group-icon"></i>
                        <span>TRAINING</span>
                    </span>
                    <i class="fas fa-chevron-down group-arrow"></i>
                </div>
                <div class="collapse <?= $isTrainingActive ? 'show' : '' ?> sidebar-submenu" id="sb-group-training" data-bs-parent="#sidebar-menu-accordion">
                    <?php if (can_access_page('training_plan')): ?>
                    <a href="<?= base_url('training/plan') ?>" class="nav-link <?= is_sb_active('training/plan', $currentRoute) ? 'active' : '' ?>" title="Training Plan & KT">
                        <i class="fas fa-book-reader"></i> <span>Training Plan & KT</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('pkt_test')): ?>
                    <a href="<?= base_url('training/pkt') ?>" class="nav-link <?= is_sb_active('training/pkt', $currentRoute) ? 'active' : '' ?>" title="PKT Test Center">
                        <i class="fas fa-tasks"></i> <span>PKT Test Center</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('practice_files')): ?>
                    <a href="<?= base_url('training/practice') ?>" class="nav-link <?= is_sb_active('training/practice', $currentRoute) ? 'active' : '' ?>" title="Practice Files">
                        <i class="fas fa-file-signature"></i> <span>Practice Files</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('certifications')): ?>
                    <a href="<?= base_url('training/certifications') ?>" class="nav-link <?= is_sb_active('training/certifications', $currentRoute) ? 'active' : '' ?>" title="Certifications">
                        <i class="fas fa-award"></i> <span>Certifications</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('manage_pkt')): ?>
                    <a href="<?= base_url('training/manage-pkt') ?>" class="nav-link <?= is_sb_active('training/manage-pkt', $currentRoute) ? 'active' : '' ?>" title="Manage PKT Question Bank">
                        <i class="fas fa-cogs"></i> <span>Manage PKT Bank</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- SECTION 8: WORKFLOWS -->
            <?php if (can_access_module('workflows') && is_ticket_workflow_enabled()): ?>
            <div class="sidebar-group">
                <div class="sidebar-heading <?= $isWorkflowsActive ? 'has-active-child' : 'collapsed' ?>" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#sb-group-workflows" 
                     role="button" 
                     aria-expanded="<?= $isWorkflowsActive ? 'true' : 'false' ?>"
                     aria-controls="sb-group-workflows"
                     title="Click to toggle Workflows">
                    <span class="group-title">
                        <i class="fas fa-project-diagram group-icon"></i>
                        <span>WORKFLOWS</span>
                    </span>
                    <i class="fas fa-chevron-down group-arrow"></i>
                </div>
                <div class="collapse <?= $isWorkflowsActive ? 'show' : '' ?> sidebar-submenu" id="sb-group-workflows" data-bs-parent="#sidebar-menu-accordion">
                    <?php if (can_access_page('workflows')): ?>
                    <a href="<?= base_url('workflows') ?>" class="nav-link <?= is_sb_active('workflows', $currentRoute) ? 'active' : '' ?>" title="Workflow & Queue Management">
                        <i class="fas fa-code-branch"></i> <span>Workflow Management</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('queues')): ?>
                    <a href="<?= base_url('queues') ?>" class="nav-link <?= is_sb_active('queues', $currentRoute) ? 'active' : '' ?>" title="Process Queues Master">
                        <i class="fas fa-cubes"></i> <span>Process Queues</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- SECTION 9: ADMINISTRATION -->
            <?php if (can_access_module('administration')): ?>
            <div class="sidebar-group">
                <div class="sidebar-heading <?= $isAdministrationActive ? 'has-active-child' : 'collapsed' ?>" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#sb-group-administration" 
                     role="button" 
                     aria-expanded="<?= $isAdministrationActive ? 'true' : 'false' ?>"
                     aria-controls="sb-group-administration"
                     title="Click to toggle Administration">
                    <span class="group-title">
                        <i class="fas fa-tools group-icon"></i>
                        <span>ADMINISTRATION</span>
                    </span>
                    <i class="fas fa-chevron-down group-arrow"></i>
                </div>
                <div class="collapse <?= $isAdministrationActive ? 'show' : '' ?> sidebar-submenu" id="sb-group-administration" data-bs-parent="#sidebar-menu-accordion">
                    <?php if (can_access_page('org_hierarchy')): ?>
                    <a href="<?= base_url('master/hierarchy') ?>" class="nav-link <?= is_sb_active('master/hierarchy', $currentRoute) ? 'active' : '' ?>" title="Organizational Hierarchy Tree">
                        <i class="fas fa-sitemap"></i> <span>Org Hierarchy Tree</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('master_projects')): ?>
                    <a href="<?= base_url('master/projects') ?>" class="nav-link <?= is_sb_active('master/projects', $currentRoute) ? 'active' : '' ?>" title="Projects & Teams Master">
                        <i class="fas fa-project-diagram"></i> <span>Projects & Teams</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('master_divisions')): ?>
                    <a href="<?= base_url('master/divisions') ?>" class="nav-link <?= is_sb_active('master/divisions', $currentRoute) ? 'active' : '' ?>" title="Divisions Master">
                        <i class="fas fa-building"></i> <span>Divisions Master</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('master_activities')): ?>
                    <a href="<?= base_url('master/activities') ?>" class="nav-link <?= is_sb_active('master/activities', $currentRoute) ? 'active' : '' ?>" title="Activities Master">
                        <i class="fas fa-stream"></i> <span>Activities Master</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('master_subactivities')): ?>
                    <a href="<?= base_url('master/subactivities') ?>" class="nav-link <?= is_sb_active('master/subactivities', $currentRoute) ? 'active' : '' ?>" title="Sub-Activities Master">
                        <i class="fas fa-list-ol"></i> <span>Sub-Activities Master</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('master_categories')): ?>
                    <a href="<?= base_url('master/categories') ?>" class="nav-link <?= is_sb_active('master/categories', $currentRoute) ? 'active' : '' ?>" title="Category Master">
                        <i class="fas fa-tags"></i> <span>Category Master</span>
                    </a>
                    <?php endif; ?>
                    <?php if (can_access_page('audit_logs')): ?>
                    <a href="<?= base_url('audit') ?>" class="nav-link <?= is_sb_active('audit', $currentRoute) ? 'active' : '' ?>" title="System Audit Logs">
                        <i class="fas fa-history"></i> <span>System Audit Logs</span>
                    </a>
                    <?php endif; ?>
                    <?php if (is_super_admin() || can_access_page('access_rights')): ?>
                    <a href="<?= base_url('access-rights') ?>" class="nav-link <?= is_sb_active('access-rights', $currentRoute) ? 'active' : '' ?>" title="Role & User Page Access Rights Management">
                        <i class="fas fa-user-shield"></i> <span>Access Rights</span>
                    </a>
                    <?php endif; ?>
                    <?php if (is_super_admin() || can_access_page('settings')): ?>
                    <a href="<?= base_url('settings') ?>" class="nav-link <?= is_sb_active('settings', $currentRoute) ? 'active' : '' ?>" title="System Settings & Maintenance Controls">
                        <i class="fas fa-cog"></i> <span>System Settings</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
<!-- /#sidebar-wrapper -->

<!-- Page Content Wrapper -->
<div id="page-content-wrapper">
