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
            if ($target === 'tracker/query') {
                return $currentRoute === 'tracker/query' || $currentRoute === 'query-tracker';
            }
            if ($target === 'master/activities') {
                return $currentRoute === 'master/activities';
            }
            return $currentRoute === $target || str_starts_with($currentRoute, $target . '/');
        }
    }
    ?>

    <div class="nav-menu-container">
        <div class="list-group list-group-flush py-2">
            
            <!-- SECTION 1: WORKSPACE & PLANNING -->
            <div class="sidebar-heading">WORKSPACE & PLANNING</div>
            <a href="<?= base_url('home') ?>" class="nav-link <?= is_sb_active('home', $currentRoute) ? 'active' : '' ?>" title="Home Hub & Launchpad">
                <i class="fas fa-home"></i> <span>Home</span>
            </a>
            <a href="<?= base_url('dashboard') ?>" class="nav-link <?= is_sb_active('dashboard', $currentRoute) ? 'active' : '' ?>" title="Dashboard">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>
            <a href="<?= base_url('tickets/my-bucket') ?>" class="nav-link <?= is_sb_active('tickets/my-bucket', $currentRoute) ? 'active' : '' ?>" title="My Bucket">
                <i class="fas fa-user-clock"></i> <span>My Bucket</span>
            </a>
            <a href="<?= base_url('roster') ?>" class="nav-link <?= is_sb_active('roster', $currentRoute) ? 'active' : '' ?>" title="Daily Roster & Planner">
                <i class="fas fa-calendar-alt"></i> <span>Daily Roster</span>
            </a>

            <!-- SECTION 2: TICKETS & TASKS -->
            <div class="sidebar-heading">TICKETS & TASKS</div>
            <a href="<?= base_url('tickets') ?>" class="nav-link <?= is_sb_active('tickets', $currentRoute) ? 'active' : '' ?>" title="Mail Tickets">
                <i class="fas fa-ticket-alt"></i> <span>Mail Tickets</span>
            </a>
            <a href="<?= base_url('tickets/create') ?>" class="nav-link <?= is_sb_active('tickets/create', $currentRoute) ? 'active' : '' ?>" title="Create Mail Ticket">
                <i class="fas fa-plus-circle"></i> <span>Create Mail Ticket</span>
            </a>
            <a href="<?= base_url('tasks') ?>" class="nav-link <?= is_sb_active('tasks', $currentRoute) ? 'active' : '' ?>" title="Internal Tasks">
                <i class="fas fa-tasks"></i> <span>Internal Tasks</span>
            </a>
            <a href="<?= base_url('hold') ?>" class="nav-link <?= is_sb_active('hold', $currentRoute) ? 'active' : '' ?>" title="Hold / Release List">
                <i class="fas fa-pause-circle"></i> <span>Hold / Release List</span>
            </a>

            <!-- SECTION 3: TRACKERS & REGISTERS -->
            <div class="sidebar-heading">TRACKERS & REGISTERS</div>
            <a href="<?= base_url('tracker/query') ?>" class="nav-link <?= is_sb_active('tracker/query', $currentRoute) ? 'active' : '' ?>" title="Query Tracker">
                <i class="fas fa-question-circle"></i> <span>Query Tracker</span>
            </a>
            <a href="<?= base_url('lifecycle') ?>" class="nav-link <?= is_sb_active('lifecycle', $currentRoute) ? 'active' : '' ?>" title="Process Lifecycle Tracker">
                <i class="fas fa-project-diagram"></i> <span>Lifecycle Tracker</span>
            </a>
            <a href="<?= base_url('tracker/input') ?>" class="nav-link <?= is_sb_active('tracker/input', $currentRoute) ? 'active' : '' ?>" title="Input Tracker">
                <i class="fas fa-inbox"></i> <span>Input Tracker</span>
            </a>
            <a href="<?= base_url('tracker/delivery') ?>" class="nav-link <?= is_sb_active('tracker/delivery', $currentRoute) ? 'active' : '' ?>" title="Delivery Tracker">
                <i class="fas fa-paper-plane"></i> <span>Delivery Tracker</span>
            </a>
            <a href="<?= base_url('tracker/process-updates') ?>" class="nav-link <?= is_sb_active('tracker/process-updates', $currentRoute) ? 'active' : '' ?>" title="Process Updates Tracker">
                <i class="fas fa-mail-bulk"></i> <span>Process Updates Tracker</span>
            </a>
            <a href="<?= base_url('error-tracker') ?>" class="nav-link <?= is_sb_active('error-tracker', $currentRoute) ? 'active' : '' ?>" title="Error Tracker">
                <i class="fas fa-exclamation-triangle"></i> <span>Error Tracker</span>
            </a>

            <!-- SECTION 4: DIRECTORIES & PEOPLE -->
            <div class="sidebar-heading">DIRECTORIES & PEOPLE</div>
            <a href="<?= base_url('contacts') ?>" class="nav-link <?= is_sb_active('contacts', $currentRoute) ? 'active' : '' ?>" title="Contact Manager">
                <i class="fas fa-address-book"></i> <span>Contact Manager</span>
            </a>
            <?php if (is_super_admin() || is_admin()): ?>
            <a href="<?= base_url('employees') ?>" class="nav-link <?= is_sb_active('employees', $currentRoute) ? 'active' : '' ?>" title="Employees & Skill Matrix">
                <i class="fas fa-users-cog"></i> <span>Employees & Skill Matrix</span>
            </a>
            <?php endif; ?>

            <!-- SECTION 5: TRAINING & PKT LMS -->
            <div class="sidebar-heading">TRAINING & PKT LMS</div>
            <a href="<?= base_url('training/plan') ?>" class="nav-link <?= is_sb_active('training/plan', $currentRoute) ? 'active' : '' ?>" title="Training Plan & KT">
                <i class="fas fa-graduation-cap"></i> <span>Training Plan & KT</span>
            </a>
            <a href="<?= base_url('training/pkt') ?>" class="nav-link <?= is_sb_active('training/pkt', $currentRoute) ? 'active' : '' ?>" title="PKT Test Center">
                <i class="fas fa-tasks"></i> <span>PKT Test Center</span>
            </a>
            <a href="<?= base_url('training/practice') ?>" class="nav-link <?= is_sb_active('training/practice', $currentRoute) ? 'active' : '' ?>" title="Practice Files">
                <i class="fas fa-file-signature"></i> <span>Practice Files</span>
            </a>
            <a href="<?= base_url('training/certifications') ?>" class="nav-link <?= is_sb_active('training/certifications', $currentRoute) ? 'active' : '' ?>" title="Certifications">
                <i class="fas fa-award"></i> <span>Certifications</span>
            </a>
            <?php if (is_super_admin() || is_admin()): ?>
            <a href="<?= base_url('training/manage-pkt') ?>" class="nav-link <?= is_sb_active('training/manage-pkt', $currentRoute) ? 'active' : '' ?>" title="Manage PKT Question Bank">
                <i class="fas fa-cogs"></i> <span>Manage PKT Bank</span>
            </a>
            <?php endif; ?>

            <!-- SECTION 6: MANAGEMENT & AUTOMATION -->
            <?php if (is_super_admin() || is_admin()): ?>
            <div class="sidebar-heading">MANAGEMENT & AUTOMATION</div>
            <a href="<?= base_url('recurring') ?>" class="nav-link <?= is_sb_active('recurring', $currentRoute) ? 'active' : '' ?>" title="Recurring Engine">
                <i class="fas fa-redo"></i> <span>Recurring Engine</span>
            </a>
            <?php endif; ?>

            <!-- SECTION 7: ADMINISTRATION & MASTERS -->
            <?php if (is_super_admin()): ?>
            <div class="sidebar-heading">ADMINISTRATION & MASTERS</div>
            <a href="<?= base_url('master/subactivities') ?>" class="nav-link <?= is_sb_active('master/subactivities', $currentRoute) ? 'active' : '' ?>" title="Sub-Activities Master">
                <i class="fas fa-list-ol"></i> <span>Sub-Activities Master</span>
            </a>
            <a href="<?= base_url('master/activities') ?>" class="nav-link <?= is_sb_active('master/activities', $currentRoute) ? 'active' : '' ?>" title="Activities Master">
                <i class="fas fa-sitemap"></i> <span>Activities Master</span>
            </a>
            <a href="<?= base_url('master/divisions') ?>" class="nav-link <?= is_sb_active('master/divisions', $currentRoute) ? 'active' : '' ?>" title="Divisions Master">
                <i class="fas fa-building"></i> <span>Divisions Master</span>
            </a>
            <a href="<?= base_url('master/categories') ?>" class="nav-link <?= is_sb_active('master/categories', $currentRoute) ? 'active' : '' ?>" title="Category Master">
                <i class="fas fa-tags"></i> <span>Category Master</span>
            </a>
            <a href="<?= base_url('audit') ?>" class="nav-link <?= is_sb_active('audit', $currentRoute) ? 'active' : '' ?>" title="System Audit Logs">
                <i class="fas fa-history"></i> <span>System Audit Logs</span>
            </a>
            <?php endif; ?>

            <!-- SECTION 8: REPORTS & ANALYTICS -->
            <div class="sidebar-heading">REPORTS & ANALYTICS</div>
            <a href="<?= base_url('reports') ?>" class="nav-link <?= is_sb_active('reports', $currentRoute) ? 'active' : '' ?>" title="Reports & Export">
                <i class="fas fa-chart-bar"></i> <span>Reports & Export</span>
            </a>
            <a href="<?= base_url('reports/production') ?>" class="nav-link <?= is_sb_active('reports/production', $currentRoute) ? 'active' : '' ?>" title="Daywise Production Tracker">
                <i class="fas fa-industry"></i> <span>Daywise Production</span>
            </a>


        </div>
    </div>
</div>
<!-- /#sidebar-wrapper -->

<!-- Page Content Wrapper -->
<div id="page-content-wrapper">


