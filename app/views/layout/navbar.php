<?php
/**
 * Main Top Navbar
 * Quick Navigation (Home, Dashboard) + Universal Dynamic Breadcrumbs + Notifications & User Profile
 */
$currentRoute = trim($_GET['url'] ?? '', '/');
$isHome = ($currentRoute === '' || $currentRoute === 'home');
$isDashboard = ($currentRoute === 'dashboard');

// Dynamic Universal Breadcrumbs
$breadcrumbs = [];
if ($isHome) {
    $breadcrumbs[] = ['title' => 'Home', 'url' => base_url('home'), 'active' => true];
} elseif ($isDashboard) {
    $breadcrumbs[] = ['title' => 'Home', 'url' => base_url('home'), 'active' => false];
    $breadcrumbs[] = ['title' => 'Dashboard', 'url' => base_url('dashboard'), 'active' => true];
} else {
    $breadcrumbs[] = ['title' => 'Home', 'url' => base_url('home'), 'active' => false];

    if (str_starts_with($currentRoute, 'tickets/my-bucket')) {
        $breadcrumbs[] = ['title' => 'Tasks & Workspace', 'url' => base_url('tickets')];
        $breadcrumbs[] = ['title' => 'My Bucket', 'url' => base_url('tickets/my-bucket'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'roster')) {
        $breadcrumbs[] = ['title' => 'Tasks & Workspace', 'url' => base_url('tickets')];
        $breadcrumbs[] = ['title' => 'Daily Roster', 'url' => base_url('roster'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'tickets/create')) {
        $breadcrumbs[] = ['title' => 'Tasks & Workspace', 'url' => base_url('tickets')];
        $breadcrumbs[] = ['title' => 'Create Mail Ticket', 'url' => base_url('tickets/create'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'tickets/edit/')) {
        $breadcrumbs[] = ['title' => 'Tasks & Workspace', 'url' => base_url('tickets')];
        $breadcrumbs[] = ['title' => 'Edit Ticket', 'url' => '#', 'active' => true];
    } elseif (str_starts_with($currentRoute, 'tickets/view/')) {
        $breadcrumbs[] = ['title' => 'Tasks & Workspace', 'url' => base_url('tickets')];
        $breadcrumbs[] = ['title' => 'Ticket Details', 'url' => '#', 'active' => true];
    } elseif (str_starts_with($currentRoute, 'tickets/import')) {
        $breadcrumbs[] = ['title' => 'Tasks & Workspace', 'url' => base_url('tickets')];
        $breadcrumbs[] = ['title' => 'Bulk Import', 'url' => base_url('tickets/import'), 'active' => true];
    } elseif ($currentRoute === 'tickets') {
        $breadcrumbs[] = ['title' => 'Tasks & Workspace', 'url' => base_url('tickets')];
        $breadcrumbs[] = ['title' => 'All Tasks', 'url' => base_url('tickets'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'tasks')) {
        $breadcrumbs[] = ['title' => 'Tasks & Workspace', 'url' => base_url('tickets')];
        $breadcrumbs[] = ['title' => 'Internal Tasks', 'url' => base_url('tasks'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'lifecycle')) {
        $breadcrumbs[] = ['title' => 'Tasks & Workspace', 'url' => base_url('tickets')];
        $breadcrumbs[] = ['title' => 'Lifecycle Tracker', 'url' => base_url('lifecycle'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'performance/scorecard')) {
        $breadcrumbs[] = ['title' => 'Performance', 'url' => base_url('performance/scorecard')];
        $breadcrumbs[] = ['title' => 'Performance Scorecard', 'url' => base_url('performance/scorecard'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'performance/analytics')) {
        $breadcrumbs[] = ['title' => 'Performance', 'url' => base_url('performance/scorecard')];
        $breadcrumbs[] = ['title' => 'Performance Analytics', 'url' => base_url('performance/analytics'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'performance/contest')) {
        $breadcrumbs[] = ['title' => 'Performance', 'url' => base_url('performance/scorecard')];
        $breadcrumbs[] = ['title' => 'Grand Prix Contest', 'url' => base_url('performance/contest'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'performance/awards')) {
        $breadcrumbs[] = ['title' => 'Performance', 'url' => base_url('performance/scorecard')];
        $breadcrumbs[] = ['title' => 'Awards & Honours Hub', 'url' => base_url('performance/awards'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'performance/config')) {
        $breadcrumbs[] = ['title' => 'Performance', 'url' => base_url('performance/scorecard')];
        $breadcrumbs[] = ['title' => 'Team Metric Config', 'url' => base_url('performance/config'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'performance/production')) {
        $breadcrumbs[] = ['title' => 'Trackers', 'url' => base_url('performance/production')];
        $breadcrumbs[] = ['title' => 'Daily Production Logger', 'url' => base_url('performance/production'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'reports/production')) {
        $breadcrumbs[] = ['title' => 'Reports', 'url' => base_url('reports')];
        $breadcrumbs[] = ['title' => 'Daywise Count', 'url' => base_url('reports/production'), 'active' => true];
    } elseif ($currentRoute === 'reports') {
        $breadcrumbs[] = ['title' => 'Reports', 'url' => base_url('reports')];
        $breadcrumbs[] = ['title' => 'Reports & Export', 'url' => base_url('reports'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'tracker/timesheet')) {
        $breadcrumbs[] = ['title' => 'Trackers', 'url' => base_url('tracker/timesheet')];
        $breadcrumbs[] = ['title' => 'Timesheet Tracker', 'url' => base_url('tracker/timesheet'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'tracker/query') || $currentRoute === 'query-tracker') {
        $breadcrumbs[] = ['title' => 'Trackers', 'url' => base_url('tracker/query')];
        $breadcrumbs[] = ['title' => 'Query Tracker', 'url' => base_url('tracker/query'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'hold')) {
        $breadcrumbs[] = ['title' => 'Trackers', 'url' => base_url('hold')];
        $breadcrumbs[] = ['title' => 'Hold / Release List', 'url' => base_url('hold'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'tracker/input')) {
        $breadcrumbs[] = ['title' => 'Trackers', 'url' => base_url('tracker/input')];
        $breadcrumbs[] = ['title' => 'Input Tracker', 'url' => base_url('tracker/input'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'tracker/delivery')) {
        $breadcrumbs[] = ['title' => 'Trackers', 'url' => base_url('tracker/delivery')];
        $breadcrumbs[] = ['title' => 'Delivery Tracker', 'url' => base_url('tracker/delivery'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'tracker/process-updates')) {
        $breadcrumbs[] = ['title' => 'Trackers', 'url' => base_url('tracker/process-updates')];
        $breadcrumbs[] = ['title' => 'Process Updates', 'url' => base_url('tracker/process-updates'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'error-tracker')) {
        $breadcrumbs[] = ['title' => 'Trackers', 'url' => base_url('error-tracker')];
        $breadcrumbs[] = ['title' => 'Error Tracker', 'url' => base_url('error-tracker'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'contacts')) {
        $breadcrumbs[] = ['title' => 'People', 'url' => base_url('contacts')];
        $breadcrumbs[] = ['title' => 'Contacts Directory', 'url' => base_url('contacts'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'employees/skill-matrix')) {
        $breadcrumbs[] = ['title' => 'People', 'url' => base_url('employees')];
        $breadcrumbs[] = ['title' => 'Skill Matrix', 'url' => base_url('employees/skill-matrix'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'employees/create')) {
        $breadcrumbs[] = ['title' => 'People', 'url' => base_url('employees')];
        $breadcrumbs[] = ['title' => 'Create Employee', 'url' => base_url('employees/create'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'employees/edit')) {
        $breadcrumbs[] = ['title' => 'People', 'url' => base_url('employees')];
        $breadcrumbs[] = ['title' => 'Edit Employee', 'url' => '#', 'active' => true];
    } elseif (str_starts_with($currentRoute, 'employees')) {
        $breadcrumbs[] = ['title' => 'People', 'url' => base_url('employees')];
        $breadcrumbs[] = ['title' => 'Employee Accounts', 'url' => base_url('employees'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'training/plan')) {
        $breadcrumbs[] = ['title' => 'Training', 'url' => base_url('training/plan')];
        $breadcrumbs[] = ['title' => 'Training Plans', 'url' => base_url('training/plan'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'training/pkt')) {
        $breadcrumbs[] = ['title' => 'Training', 'url' => base_url('training/plan')];
        $breadcrumbs[] = ['title' => 'Process Knowledge Test', 'url' => base_url('training/pkt'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'training/practice')) {
        $breadcrumbs[] = ['title' => 'Training', 'url' => base_url('training/plan')];
        $breadcrumbs[] = ['title' => 'Practice Simulation', 'url' => base_url('training/practice'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'training/certifications')) {
        $breadcrumbs[] = ['title' => 'Training', 'url' => base_url('training/plan')];
        $breadcrumbs[] = ['title' => 'Certifications', 'url' => base_url('training/certifications'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'training/manage-pkt')) {
        $breadcrumbs[] = ['title' => 'Training', 'url' => base_url('training/plan')];
        $breadcrumbs[] = ['title' => 'Manage PKT', 'url' => base_url('training/manage-pkt'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'workflows')) {
        $breadcrumbs[] = ['title' => 'Workflows', 'url' => base_url('workflows')];
        $breadcrumbs[] = ['title' => 'Workflow Management', 'url' => base_url('workflows'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'queues')) {
        $breadcrumbs[] = ['title' => 'Workflows', 'url' => base_url('workflows')];
        $breadcrumbs[] = ['title' => 'Process Queues', 'url' => base_url('queues'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'master/hierarchy')) {
        $breadcrumbs[] = ['title' => 'Administration', 'url' => base_url('master/hierarchy')];
        $breadcrumbs[] = ['title' => 'Org Hierarchy Tree', 'url' => base_url('master/hierarchy'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'master/projects')) {
        $breadcrumbs[] = ['title' => 'Administration', 'url' => base_url('master/hierarchy')];
        $breadcrumbs[] = ['title' => 'Projects & Teams', 'url' => base_url('master/projects'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'master/divisions')) {
        $breadcrumbs[] = ['title' => 'Administration', 'url' => base_url('master/hierarchy')];
        $breadcrumbs[] = ['title' => 'Divisions Master', 'url' => base_url('master/divisions'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'master/activities')) {
        $breadcrumbs[] = ['title' => 'Administration', 'url' => base_url('master/hierarchy')];
        $breadcrumbs[] = ['title' => 'Activities Master', 'url' => base_url('master/activities'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'master/subactivities')) {
        $breadcrumbs[] = ['title' => 'Administration', 'url' => base_url('master/hierarchy')];
        $breadcrumbs[] = ['title' => 'Sub-Activities Master', 'url' => base_url('master/subactivities'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'master/categories')) {
        $breadcrumbs[] = ['title' => 'Administration', 'url' => base_url('master/hierarchy')];
        $breadcrumbs[] = ['title' => 'Categories Master', 'url' => base_url('master/categories'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'audit')) {
        $breadcrumbs[] = ['title' => 'Administration', 'url' => base_url('master/hierarchy')];
        $breadcrumbs[] = ['title' => 'Audit Logs', 'url' => base_url('audit'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'access-rights')) {
        $breadcrumbs[] = ['title' => 'Administration', 'url' => base_url('master/hierarchy')];
        $breadcrumbs[] = ['title' => 'Access Rights', 'url' => base_url('access-rights'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'settings')) {
        $breadcrumbs[] = ['title' => 'Administration', 'url' => base_url('master/hierarchy')];
        $breadcrumbs[] = ['title' => 'System Settings', 'url' => base_url('settings'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'profile')) {
        $breadcrumbs[] = ['title' => 'User', 'url' => base_url('profile')];
        $breadcrumbs[] = ['title' => 'My Profile', 'url' => base_url('profile'), 'active' => true];
    } elseif (str_starts_with($currentRoute, 'change-password')) {
        $breadcrumbs[] = ['title' => 'User', 'url' => base_url('profile')];
        $breadcrumbs[] = ['title' => 'Change Password', 'url' => base_url('change-password'), 'active' => true];
    } else {
        $parts = explode('/', $currentRoute);
        if (count($parts) > 1) {
            $breadcrumbs[] = ['title' => ucwords(str_replace(['-', '_'], ' ', $parts[0])), 'url' => base_url($parts[0])];
            $breadcrumbs[] = ['title' => htmlspecialchars($title ?? ucwords(str_replace(['-', '_'], ' ', $parts[1]))), 'url' => '#', 'active' => true];
        } else {
            $breadcrumbs[] = ['title' => htmlspecialchars($title ?? ucwords(str_replace(['-', '_'], ' ', $parts[0]))), 'url' => '#', 'active' => true];
        }
    }
}
?>

<!-- Main Top Navbar -->
<nav class="navbar navbar-expand-lg main-navbar">
    <div class="container-fluid px-3 d-flex justify-content-between align-items-center">
        <!-- Left: Quick Action Nav (Home, Dashboard) + Universal Dynamic Breadcrumbs -->
        <div class="d-flex align-items-center flex-wrap gap-2 ms-1">
            <!-- Home & Dashboard Quick Nav Buttons -->
            <div class="navbar-quick-nav d-flex align-items-center gap-1">
                <a href="<?= base_url('home') ?>" 
                   class="btn btn-sm <?= $isHome ? 'btn-primary' : '' ?> d-inline-flex align-items-center gap-1" 
                   title="Home Hub & Control Panel">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="<?= base_url('dashboard') ?>" 
                   class="btn btn-sm <?= $isDashboard ? 'btn-primary' : '' ?> d-inline-flex align-items-center gap-1" 
                   title="Executive Analytics & Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <!-- Vertical Separator Divider -->
            <span class="vr mx-2 text-muted opacity-50 d-none d-sm-inline-block" style="height: 18px;"></span>

            <!-- Dynamic Breadcrumbs Across All Pages -->
            <nav aria-label="breadcrumb" class="navbar-breadcrumbs d-none d-sm-flex align-items-center">
                <ol class="breadcrumb mb-0 align-items-center">
                    <?php foreach ($breadcrumbs as $idx => $bc): ?>
                        <?php if (!empty($bc['active']) || $idx === count($breadcrumbs) - 1): ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?= $bc['title'] ?>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item">
                                <a href="<?= $bc['url'] ?>"><?= $bc['title'] ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        </div>

        <!-- Right: Notifications & User Profile -->
        <div class="d-flex align-items-center gap-3">
            <!-- Notification Bell Dropdown -->
            <div class="dropdown notification-bell">
                <button class="btn btn-icon btn-ghost-secondary position-relative rounded-circle border bg-white" type="button" data-bs-toggle="dropdown" title="Notifications" style="width: 36px; height: 36px; padding: 0;">
                    <i class="fas fa-bell fs-6 text-secondary"></i>
                    <span id="notif-count-badge" class="badge bg-danger badge-pill badge-count d-none">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-sm" style="width: 320px;" id="notif-dropdown">
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Notifications</span>
                        <a href="<?= base_url('api/mark-notification-read') ?>" class="text-primary fs-8 text-decoration-none" onclick="event.preventDefault(); $.post(this.href, function(){ location.reload(); });">Mark all read</a>
                    </div>
                    <div id="notif-dropdown-list" style="max-height: 280px; overflow-y: auto;">
                        <div class="dropdown-item text-center text-muted py-3">Loading notifications...</div>
                    </div>
                </div>
            </div>

            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <a class="d-flex align-items-center gap-2 text-decoration-none text-dark dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <div class="user-avatar shadow-sm">
                        <?= strtoupper(substr(Session::get('full_name') ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="d-none d-md-block text-start me-1">
                        <div class="fw-bold fs-7 mb-0 text-dark"><?= htmlspecialchars(Session::get('full_name') ?? '') ?></div>
                        <small class="text-muted fs-8 d-block">
                            <?= htmlspecialchars(Session::get('role_name') ?? '') ?>
                            <?php if (!empty(Session::get('designation'))): ?>
                                <span class="text-secondary">&bull; <?= htmlspecialchars(Session::get('designation')) ?></span>
                            <?php endif; ?>
                        </small>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="<?= base_url('profile') ?>"><i class="fas fa-user-cog me-2 text-secondary"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="<?= base_url('change-password') ?>"><i class="fas fa-key me-2 text-secondary"></i>Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger fw-bold" href="<?= base_url('logout') ?>"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</a></li>
                </ul>
            </div>

            <!-- Dedicated Direct Logout Header Button -->
            <a href="<?= base_url('logout') ?>" class="btn btn-outline-danger btn-sm fw-bold px-3 ms-1 d-inline-flex align-items-center gap-1" title="Sign Out">
                <i class="fas fa-sign-out-alt fs-6"></i><span class="d-none d-sm-inline">Logout</span>
            </a>
        </div>
    </div>
</nav>

<!-- Floating Toast Notifications Container -->
<div class="toast-container-mqt">
    <?php $flash = Session::getFlash(); ?>
    <?php if ($flash): ?>
    <div class="toast toast-mqt fade show align-items-center border-0 text-white bg-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'danger' ? 'danger' : 'warning') ?>" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2 fs-7 fw-bold">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'danger' ? 'exclamation-circle' : 'info-circle') ?> fs-5"></i>
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>
</div>
