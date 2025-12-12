<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Admin Panel' ?></title>
    <link rel="stylesheet" href="<?= $base_url ?? '..' ?>/style.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="<?= $base_url ?? '..' ?>/dashboard.php" class="<?= $page=='dashboard'?'active':'' ?>">Dashboard</a></li>
            <li><a href="<?= $base_url ?? '..' ?>/lapangan/index.php" class="<?= $page=='lapangan'?'active':'' ?>">Lapangan</a></li>
            <li><a href="<?= $base_url ?? '..' ?>/jadwal/index.php" class="<?= $page=='jadwal'?'active':'' ?>">Jadwal</a></li>
            <li><a href="<?= $base_url ?? '..' ?>/booking/index.php" class="<?= $page=='booking'?'active':'' ?>">Booking</a></li>
            <li><a href="<?= $base_url ?? '..' ?>/user/index.php" class="<?= $page=='user'?'active':'' ?>">User</a></li>
            <li><a href="<?= $base_url ?? '..' ?>/payment/index.php" class="<?= $page=='payment'?'active':'' ?>">Payment</a></li>
            <li><a href="<?= $base_url ?? '..' ?>/logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1><?= $page_title ?? 'Admin Panel' ?></h1>
            <span>👤 <?= $admin_name ?></span>
        </div>

        <div class="container"><?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>