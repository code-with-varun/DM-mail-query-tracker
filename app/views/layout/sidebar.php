<!-- Sidebar -->
<div id="sidebar-wrapper">
    <div class="sidebar-brand">
        <div class="brand-title-box">
            <i class="fas fa-mail-bulk text-primary fs-5 flex-shrink-0"></i>
            <span class="brand-text">Mail Query Tracker</span>
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
            
            <a href="<?= base_url('tickets') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'tickets') && !str_contains($_SERVER['REQUEST_URI'], 'create')) ? 'active' : '' ?>" title="Query & Task Tickets">
                <i class="fas fa-ticket-alt"></i> <span>Query & Task Tickets</span>
            </a>
            
            <a href="<?= base_url('tickets/create') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'tickets/create')) ? 'active' : '' ?>" title="Create New Ticket">
                <i class="fas fa-plus-circle"></i> <span>Create New Ticket</span>
            </a>

            <a href="<?= base_url('tasks') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'tasks')) ? 'active' : '' ?>" title="Internal Tasks">
                <i class="fas fa-tasks"></i> <span>Internal Tasks</span>
            </a>

            <a href="<?= base_url('hold') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'hold')) ? 'active' : '' ?>" title="Hold / Release List">
                <i class="fas fa-pause-circle"></i> <span>Hold / Release List</span>
            </a>

            <div class="sidebar-heading">TRACKERS</div>
            <a href="<?= base_url('tracker/input') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'tracker/input')) ? 'active' : '' ?>" title="Input Tracker">
                <i class="fas fa-inbox"></i> <span>Input Tracker</span>
            </a>
            <a href="<?= base_url('tracker/delivery') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'tracker/delivery')) ? 'active' : '' ?>" title="Delivery Tracker">
                <i class="fas fa-paper-plane"></i> <span>Delivery Tracker</span>
            </a>

            <?php if (is_super_admin() || is_admin()): ?>
            <div class="sidebar-heading">MANAGEMENT</div>
            <a href="<?= base_url('recurring') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'recurring')) ? 'active' : '' ?>" title="Recurring Engine">
                <i class="fas fa-redo"></i> <span>Recurring Engine</span>
            </a>
            <a href="<?= base_url('employees') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'employees')) ? 'active' : '' ?>" title="Employees & Users">
                <i class="fas fa-users-cog"></i> <span>Employees & Users</span>
            </a>
            <?php endif; ?>

            <?php if (is_super_admin()): ?>
            <div class="sidebar-heading">ADMINISTRATION</div>
            <a href="<?= base_url('master/activities') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'master/activities')) ? 'active' : '' ?>" title="Activities Master">
                <i class="fas fa-sitemap"></i> <span>Activities Master</span>
            </a>
            <a href="<?= base_url('master/divisions') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'master/divisions')) ? 'active' : '' ?>" title="Divisions Master">
                <i class="fas fa-building"></i> <span>Divisions Master</span>
            </a>
            <a href="<?= base_url('audit') ?>" class="nav-link <?= (str_contains($_SERVER['REQUEST_URI'], 'audit')) ? 'active' : '' ?>" title="System Audit Logs">
                <i class="fas fa-history"></i> <span>System Audit Logs</span>
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
