<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mail Query Tracker</title>
    <link rel="stylesheet" href="<?= base_url('public/assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/fontawesome.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/admin-style.css') ?>">
</head>
<body class="login-page">
    <div class="login-card card bg-white">
        <div class="card-header">
            <div class="brand-logo">
                <i class="fas fa-mail-bulk"></i>
            </div>
            <h4 class="fw-bold mb-1">Mail Query Tracker</h4>
            <p class="text-muted fs-7 mb-0">Enterprise Internal Portal Sign In</p>
        </div>
        <div class="card-body p-4 pt-2">
            <?php $flash = Session::getFlash(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?> fs-7 py-2" role="alert">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                
                <div class="mb-3">
                    <label class="form-label fs-7 fw-bold">Username / Email / Employee Code</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                        <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Enter username or email" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fs-7 fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Enter password" required>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label fs-7 text-secondary" for="rememberMe">Remember me on this system</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>
