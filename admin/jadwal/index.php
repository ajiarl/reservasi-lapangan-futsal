<?php
/**
 * List Jadwal Lapangan
 * Admin Panel - Master Data Jadwal
 */

require_once '../auth.php';
require_once '../db.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Handle Delete
if (isset($_GET['delete'])) {
    $jadwal_id = intval($_GET['delete']);
    
    // Check if jadwal has booking_detail
    $check = fetchOne("SELECT COUNT(*) as total FROM booking_detail WHERE jadwal_id = ?", [$jadwal_id]);
    
    if ($check['total'] > 0) {
        header('Location: index.php?error=Tidak dapat menghapus jadwal yang sudah dibooking!');
        exit;
    }
    
    $delete = q("DELETE FROM jadwallapangan WHERE jadwal_id = ?", [$jadwal_id]);
    
    if ($delete) {
        header('Location: index.php?success=Jadwal berhasil dihapus!');
    } else {
        header('Location: index.php?error=Gagal menghapus jadwal!');
    }
    exit;
}

// Fetch all jadwal with lapangan info
$jadwal_list = fetchAll("
    SELECT j.*, l.nama_lapangan, l.jenis_lapangan
    FROM jadwallapangan j
    JOIN lapangan l ON j.Lapangan_lapangan_id = l.lapangan_id
    ORDER BY l.nama_lapangan, 
             FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
             j.jam_mulai_slot
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Jadwal - Admin Panel</title>
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
            <li><a href="../lapangan/index.php">Data Lapangan</a></li>
            <li><a href="index.php" class="active">Data Jadwal</a></li>
            <li><a href="../booking/index.php">Data Booking</a></li>
            <li><a href="../user/index.php">Data User</a></li>
            <li><a href="../payment/index.php">Data Payment</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Master Data Jadwal Lapangan</h1>
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
                    <h3>Daftar Jadwal Slot Waktu</h3>
                    <a href="create.php" class="btn btn-primary">+ Tambah Jadwal</a>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Lapangan</th>
                                    <th>Jenis</th>
                                    <th>Hari</th>
                                    <th>Jam Mulai</th>
                                    <th>Jam Selesai</th>
                                    <th>Harga/Jam</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($jadwal_list)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">Belum ada data jadwal</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($jadwal_list as $jadwal): ?>
                                        <tr>
                                            <td><?php echo $jadwal['jadwal_id']; ?></td>
                                            <td><?php echo htmlspecialchars($jadwal['nama_lapangan']); ?></td>
                                            <td><?php echo htmlspecialchars($jadwal['jenis_lapangan']); ?></td>
                                            <td><?php echo htmlspecialchars($jadwal['hari']); ?></td>
                                            <td><?php echo date('H:i', strtotime($jadwal['jam_mulai_slot'])); ?></td>
                                            <td><?php echo date('H:i', strtotime($jadwal['jam_selesai_slot'])); ?></td>
                                            <td>Rp <?php echo number_format($jadwal['harga_perjam_slot'], 0, ',', '.'); ?></td>
                                            <td>
                                                <a href="edit.php?id=<?php echo $jadwal['jadwal_id']; ?>" class="btn btn-primary btn-small">Edit</a>
                                                <a href="index.php?delete=<?php echo $jadwal['jadwal_id']; ?>" 
                                                   class="btn btn-danger btn-small" 
                                                   onclick="return confirm('Yakin ingin menghapus jadwal ini?')">Hapus</a>
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