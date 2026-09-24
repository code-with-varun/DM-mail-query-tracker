<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? APP_NAME) ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('public/assets/logo/Datamatics-Responsive-Logo.png') ?>">
    <!-- Offline Bundled CSS Assets -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/bootstrap.min.css?v=2.2') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/fontawesome.min.css?v=2.2') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/dataTables.bootstrap5.css?v=2.2') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/admin-style.css?v=' . time()) ?>">
    <script src="<?= base_url('public/assets/js/jquery.min.js') ?>"></script>
    <script src="<?= base_url('public/assets/js/chart.min.js') ?>"></script>
    <script>
        const BASE_URL = "<?= base_url() ?>";
    </script>
</head>
<body>
<div id="wrapper">
