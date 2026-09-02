<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? APP_NAME) ?></title>
    <!-- Offline Bundled CSS Assets -->
    <link rel="stylesheet" href="<?= base_url('public/assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/fontawesome.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/dataTables.bootstrap5.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/css/admin-style.css') ?>">
    <script>
        const BASE_URL = "<?= base_url() ?>";
    </script>
</head>
<body>
<div id="wrapper">
