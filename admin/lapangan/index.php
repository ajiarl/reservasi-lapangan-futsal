<?php
/**
 * List Lapangan
 * Admin Panel - Master Data Lapangan
 */

require_once '../auth.php';
require_once '../db.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Handle Delete
if (isset($_GET['delete'])) {
    $lapangan_id = intval($_GET['delete']);
    
    // Check if lapangan has jadwal
    $check = fetchOne("SELECT COUNT(*) as total FROM jadwallapangan WHERE Lapangan_lapangan_id = ?", [$lapangan_id]);
    
    if ($check['total'] > 0) {
        header('Location: index.php?error=Tidak dapat menghapus lapangan yang memiliki jadwal!');
        exit;
    }
    
    $delete = q("DELETE FROM lapangan WHERE lapangan_id = ?", [$lapangan_id]);
    
    if ($delete) {
        header('Location: index.php?success=Lapangan berhasil dihapus!');
    } else {
        header('Location: index.php?error=Gagal menghapus lapangan!');
    }
    exit;
}

// Fetch all lapangan
$lapangan_list = fetchAll("SELECT * FROM lapangan ORDER BY lapangan_id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Lapangan - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
            <p style="font-size: 13px; opacity: 0.8;">Futsal Reservation</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php">Dashboard</a></li>
            <li><a href="index.php" class="active">Data Lapangan</a></li>
            <li><a href="../jadwal/index.php">Data Jadwal</a></li>
            <li><a href="../booking/index.php">Data Booking</a></li>
            <li><a href="../user/index.php">Data User</a></li>
            <li><a href="../payment/index.php">Data Payment</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Master Data Lapangan</h1>
            <div class="header-right">
                <span>👤 <?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>

        <div class="container">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex justify-between align-center">
                    <h3>Daftar Lapangan</h3>
                    <a href="create.php" class="btn btn-primary">+ Tambah Lapangan</a>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Lapangan</th>
                                    <th>Jenis Lapangan</th>
                                    <th>Fasilitas</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($lapangan_list)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada data lapangan</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($lapangan_list as $lap): ?>
                                        <tr>
                                            <td><?php echo $lap['lapangan_id']; ?></td>
                                            <td><?php echo htmlspecialchars($lap['nama_lapangan']); ?></td>
                                            <td><?php echo htmlspecialchars($lap['jenis_lapangan']); ?></td>
                                            <td><?php echo htmlspecialchars($lap['fasilitas']); ?></td>
                                            <td>
                                                <?php if ($lap['is_active'] == 1): ?>
                                                    <span class="badge badge-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="edit.php?id=<?php echo $lap['lapangan_id']; ?>" class="btn btn-primary btn-small">Edit</a>
                                                <a href="index.php?delete=<?php echo $lap['lapangan_id']; ?>" 
                                                   class="btn btn-danger btn-small" 
                                                   onclick="return confirm('Yakin ingin menghapus lapangan ini?')">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>