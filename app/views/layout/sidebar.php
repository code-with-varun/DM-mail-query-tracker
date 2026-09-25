<!-- Sidebar -->
<div id="sidebar-wrapper">
    <div class="sidebar-brand">
        <div class="brand-title-box d-flex align-items-center gap-2">
            <img src="<?= base_url('public/assets/logo/Datamatics-Responsive-Logo.png') ?>" alt="Datamatics" class="brand-logo-img" style="height: 32px; width: 32px; object-fit: contain;">
            <span class="brand-text fs-7 fw-bold text-white">Datamatics B-OPMS</span>
        </div>
        <!-- Hamburger Collapse Toggle Button on Sidebar Top -->
        <button class="btn btn-sm text-secondary border-0 p-1 ms-auto flex-shrink-0" id="sidebarToggle" type="button" title="Toggle Sidebar Collapse">
            <i class="fas fa-bars fs-6"></i>
        </button>
    </div>
    
    <div class="nav-menu-container">
        <div class="list-group list-group-flush py-2">
            <div class="sidebar-heading">MAIN MENU</div>
            
            <a href="<?= base_url('dashboard') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'dashboard')) ? 'active' : '' ?>" title="Dashboard">
                <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
            </a>

            <a href="<?= base_url('tickets/my-bucket') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'tickets/my-bucket')) ? 'active' : '' ?>" title="My Bucket">
                <i class="fas fa-user-clock"></i> <span>My Bucket</span>
            </a>

            <a href="<?= base_url('roster') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'roster')) ? 'active' : '' ?>" title="Daily Roster & Planner">
                <i class="fas fa-calendar-alt"></i> <span>Daily Roster</span>
            </a>
            
            <a href="<?= base_url('tickets') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'tickets') && !str_contains($_SERVER['REQUEST_URI'], 'create') && !str_contains($_SERVER['REQUEST_URI'], 'my-bucket')) ? 'active' : '' ?>" title="Mail Tickets">
                <i class="fas fa-ticket-alt"></i> <span>Mail Tickets</span>
            </a>
            
            <a href="<?= base_url('tickets/create') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'tickets/create')) ? 'active' : '' ?>" title="Create Mail Ticket">
                <i class="fas fa-plus-circle"></i> <span>Create Mail Ticket</span>
            </a>

            <a href="<?= base_url('tasks') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'tasks')) ? 'active' : '' ?>" title="Internal Tasks">
                <i class="fas fa-tasks"></i> <span>Internal Tasks</span>
            </a>

            <a href="<?= base_url('hold') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'hold')) ? 'active' : '' ?>" title="Hold / Release List">
                <i class="fas fa-pause-circle"></i> <span>Hold / Release List</span>
            </a>

            <div class="sidebar-heading">TRACKERS</div>
            <a href="<?= base_url('contacts') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'contacts')) ? 'active' : '' ?>" title="Contact Manager">
                <i class="fas fa-address-book"></i> <span>Contact Manager</span>
            </a>
            <a href="<?= base_url('tracker/input') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'tracker/input')) ? 'active' : '' ?>" title="Input Tracker">
                <i class="fas fa-inbox"></i> <span>Input Tracker</span>
            </a>
            <a href="<?= base_url('tracker/delivery') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'tracker/delivery')) ? 'active' : '' ?>" title="Delivery Tracker">
                <i class="fas fa-paper-plane"></i> <span>Delivery Tracker</span>
            </a>
            <a href="<?= base_url('error-tracker') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'error-tracker')) ? 'active' : '' ?>" title="Error Tracker">
                <i class="fas fa-exclamation-triangle"></i> <span>Error Tracker</span>
            </a>

            <?php if (is_super_admin() || is_admin()): ?>
            <div class="sidebar-heading">MANAGEMENT</div>
            <a href="<?= base_url('recurring') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'recurring')) ? 'active' : '' ?>" title="Recurring Engine">
                <i class="fas fa-redo"></i> <span>Recurring Engine</span>
            </a>
            <a href="<?= base_url('employees') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'employees')) ? 'active' : '' ?>" title="Employees & Skill Matrix">
                <i class="fas fa-users-cog"></i> <span>Employees & Skill Matrix</span>
            </a>
            <?php endif; ?>

            <?php if (is_super_admin()): ?>
            <div class="sidebar-heading">ADMINISTRATION</div>
            <a href="<?= base_url('master/divisions') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'master/divisions')) ? 'active' : '' ?>" title="Divisions Master">
                <i class="fas fa-building"></i> <span>Divisions Master</span>
            </a>
            <a href="<?= base_url('master/activities') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'master/activities') && !str_contains($_SERVER['REQUEST_URI'], 'subactivities')) ? 'active' : '' ?>" title="Activities Master">
                <i class="fas fa-sitemap"></i> <span>Activities Master</span>
            </a>
            <a href="<?= base_url('master/subactivities') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'master/subactivities')) ? 'active' : '' ?>" title="Sub-Activities Master">
                <i class="fas fa-list-ol"></i> <span>Sub-Activities Master</span>
            </a>
            <a href="<?= base_url('master/categories') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'master/categories')) ? 'active' : '' ?>" title="Category Master">
                <i class="fas fa-tags"></i> <span>Category Master</span>
            </a>
            <a href="<?= base_url('audit') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'audit')) ? 'active' : '' ?>" title="System Audit Logs">
                <i class="fas fa-history"></i> <span>System Audit Logs</span>
            </a>
            <a href="<?= base_url('settings') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'settings')) ? 'active' : '' ?>" title="System Settings">
                <i class="fas fa-cog"></i> <span>System Settings</span>
            </a>
            <?php endif; ?>

            <div class="sidebar-heading">REPORTS</div>
            <a href="<?= base_url('reports') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'reports')) ? 'active' : '' ?>" title="Reports & Export">
                <i class="fas fa-chart-bar"></i> <span>Reports & Export</span>
            </a>
        </div>
    </div>
</div>
<!-- /#sidebar-wrapper -->

<!-- Page Content Wrapper -->
<div id="page-content-wrapper">
