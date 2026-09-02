<!-- Main Top Navbar -->
<nav class="navbar navbar-expand-lg main-navbar">
    <div class="container-fluid px-3 d-flex justify-content-between align-items-center">
        <span class="navbar-brand fw-bold fs-6 text-dark mb-0 ms-2">
            <?= htmlspecialchars($title ?? APP_NAME) ?>
        </span>

        <div class="d-flex align-items-center gap-3">
            <!-- Notification Bell Dropdown -->
            <div class="dropdown notification-bell">
                <button class="btn btn-light btn-sm position-relative rounded-circle p-2 border" type="button" data-bs-toggle="dropdown" title="Notifications">
                    <i class="fas fa-bell fs-6 text-secondary"></i>
                    <span id="notif-count-badge" class="badge bg-danger badge-count d-none">0</span>
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
                        <small class="text-muted fs-8 d-block"><?= htmlspecialchars(Session::get('role_name') ?? '') ?></small>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="<?= base_url('profile') ?>"><i class="fas fa-user-cog me-2 text-secondary"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="<?= base_url('profile#password') ?>"><i class="fas fa-key me-2 text-secondary"></i>Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger fw-bold" href="<?= base_url('logout') ?>"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</a></li>
                </ul>
            </div>

            <!-- Dedicated Direct Logout Header Button -->
            <a href="<?= base_url('logout') ?>" class="btn btn-outline-danger btn-sm fw-bold px-3 ms-1" title="Sign Out">
                <i class="fas fa-sign-out-alt me-1"></i><span class="d-none d-sm-inline">Logout</span>
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
