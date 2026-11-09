<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DM-Ispark</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('public/assets/logo/Datamatics-Responsive-Logo.png') ?>">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/bootstrap.min.css?v=2.2') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/fontawesome.min.css?v=2.2') ?>">
    
    <style>
        * {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        body.login-page {
            min-height: 100vh;
            background: radial-gradient(circle at 50% 20%, #1e293b 0%, #0f172a 60%, #090d16 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
            position: relative;
            overflow-x: hidden;
        }

        /* Subtle animated background glow */
        body.login-page::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(225, 29, 72, 0.15) 0%, rgba(225, 29, 72, 0) 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
        }

        .login-wrapper {
            width: 100%;
            max-width: 410px;
            z-index: 1;
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .login-header {
            padding: 2.25rem 2rem 1.25rem 2rem;
            text-align: center;
            background: #ffffff;
        }

        .brand-logo-img {
            max-height: 48px;
            max-width: 200px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .brand-logo-img:hover {
            transform: scale(1.03);
        }

        .brand-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-top: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .brand-subtitle {
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 500;
            line-height: 1.4;
            margin-bottom: 0;
        }

        .login-body {
            padding: 0 2rem 2.25rem 2rem;
        }

        .form-label-custom {
            font-size: 0.8rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 0.4rem;
            display: block;
        }

        .input-group-custom {
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.2s ease;
            background-color: #f8fafc;
            overflow: hidden;
        }

        .input-group-custom:focus-within {
            border-color: #e11d48;
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.12);
        }

        .input-group-custom .input-group-text {
            background: transparent;
            border: none;
            color: #94a3b8;
            padding-left: 1rem;
            padding-right: 0.75rem;
            font-size: 0.95rem;
        }

        .input-group-custom:focus-within .input-group-text {
            color: #e11d48;
        }

        .input-group-custom .form-control {
            border: none;
            background: transparent;
            font-size: 0.88rem;
            font-weight: 600;
            color: #0f172a;
            padding: 0.7rem 1rem 0.7rem 0;
            box-shadow: none !important;
        }

        .input-group-custom .form-control::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .btn-toggle-pass {
            border: none;
            background: transparent;
            color: #94a3b8;
            padding: 0 1rem;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .btn-toggle-pass:hover {
            color: #475569;
        }

        .btn-login-submit {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.92rem;
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            width: 100%;
            box-shadow: 0 10px 20px -5px rgba(225, 29, 72, 0.4);
            transition: all 0.25 ease;
            cursor: pointer;
        }

        .btn-login-submit:hover {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            box-shadow: 0 14px 24px -4px rgba(225, 29, 72, 0.5);
            transform: translateY(-1.5px);
            color: #ffffff;
        }

        .btn-login-submit:active {
            transform: translateY(0);
            box-shadow: 0 6px 12px -3px rgba(225, 29, 72, 0.4);
        }

        .custom-checkbox .form-check-input {
            width: 1.05rem;
            height: 1.05rem;
            border-radius: 4px;
            border: 1.5px solid #cbd5e1;
            cursor: pointer;
        }

        .custom-checkbox .form-check-input:checked {
            background-color: #e11d48;
            border-color: #e11d48;
        }

        .custom-checkbox .form-check-label {
            font-size: 0.82rem;
            color: #64748b;
            font-weight: 500;
            cursor: pointer;
            user-select: none;
        }

        .login-footer-text {
            text-align: center;
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 1.5rem;
            font-weight: 500;
        }

        .login-footer-text a {
            color: #94a3b8;
            text-decoration: none;
        }

        .login-footer-text a:hover {
            color: #f43f5e;
        }
    </style>
</head>
<body class="login-page">

    <div class="login-wrapper">
        <div class="login-card">
            <!-- Card Header -->
            <div class="login-header">
                <div class="mb-2">
                    <img src="<?= base_url('public/assets/logo/datamatics.png') ?>" alt="Datamatics" class="brand-logo-img">
                </div>
                <div class="brand-title">DM-Ispark</div>
                <p class="brand-subtitle">Business Operations &amp; Performance Management</p>
            </div>

            <!-- Card Body -->
            <div class="login-body">
                <?php $flash = Session::getFlash(); ?>
                <?php if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] === 'danger' ? 'danger' : htmlspecialchars($flash['type']) ?> border-0 shadow-2xs fs-8 py-2 px-3 rounded-3 mb-3 d-flex align-items-center gap-2" role="alert">
                        <i class="fas fa-exclamation-circle flex-shrink-0"></i>
                        <div><?= htmlspecialchars($flash['message']) ?></div>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('login') ?>" method="POST" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                    
                    <div class="mb-3.5">
                        <label class="form-label-custom" for="usernameInput">Employee Code</label>
                        <div class="input-group-custom d-flex align-items-center">
                            <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                            <input type="text" name="username" id="usernameInput" class="form-control" placeholder="Enter your Employee Code" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3" style="margin-top: 1rem;">
                        <label class="form-label-custom" for="passwordInput">Password</label>
                        <div class="input-group-custom d-flex align-items-center">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Enter your password" required>
                            <button type="button" class="btn-toggle-pass" onclick="togglePasswordVisibility()" title="Toggle Password Visibility">
                                <i class="fas fa-eye" id="togglePassIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4 mt-3">
                        <div class="form-check custom-checkbox mb-0">
                            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me">
                            <label class="form-check-label" for="rememberMe">Remember me</label>
                        </div>
                    </div>

                    <button type="submit" class="btn-login-submit">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="login-footer-text">
            &copy; <?= date('Y') ?> <strong>Datamatics</strong> &bull; DM-Ispark System v2.5
        </div>
    </div>

    <script>
    function togglePasswordVisibility() {
        var pwd = document.getElementById('passwordInput');
        var icon = document.getElementById('togglePassIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            pwd.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
    </script>
</body>
</html>
