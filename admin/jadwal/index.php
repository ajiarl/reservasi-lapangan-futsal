<?php
/**
 * List Jadwal Lapangan
 * Admin Panel - Master Data Jadwal dengan Pencarian dan Filter
 */

require_once '../auth.php';
require_once '../db.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Handle Search and Filter
$search = trim($_GET['search'] ?? '');
$filter_lapangan = intval($_GET['lapangan'] ?? 0);
$filter_hari = trim($_GET['hari'] ?? '');

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

// Fetch lapangan for filter dropdown
$lapangan_options = fetchAll("SELECT lapangan_id, nama_lapangan FROM lapangan WHERE is_active = 1 ORDER BY nama_lapangan");

// Build query with JOIN, search and filter
$sql = "SELECT j.*, l.nama_lapangan, l.jenis_lapangan
        FROM jadwallapangan j
        JOIN lapangan l ON j.Lapangan_lapangan_id = l.lapangan_id
        WHERE 1=1";
$params = [];

// Add search condition
if (!empty($search)) {
    $sql .= " AND (l.nama_lapangan LIKE ? OR l.jenis_lapangan LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

// Add lapangan filter
if ($filter_lapangan > 0) {
    $sql .= " AND j.Lapangan_lapangan_id = ?";
    $params[] = $filter_lapangan;
}

// Add hari filter
if (!empty($filter_hari)) {
    $sql .= " AND j.hari = ?";
    $params[] = $filter_hari;
}

$sql .= " ORDER BY l.nama_lapangan, 
         FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
         j.jam_mulai_slot";

// Fetch all jadwal
$jadwal_list = fetchAll($sql, $params);
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

            <!-- Filter Card -->
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body">
                    <form method="GET" action="" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 15px; align-items: end;">
                        <div class="form-group" style="margin: 0;">
                            <label for="search" style="margin-bottom: 5px;">Cari Lapangan</label>
                            <input 
                                type="text" 
                                id="search"
                                name="search" 
                                class="form-control" 
                                placeholder="Nama atau jenis lapangan"
                                value="<?php echo htmlspecialchars($search); ?>"
                            >
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label for="lapangan" style="margin-bottom: 5px;">Lapangan</label>
                            <select name="lapangan" id="lapangan" class="form-control">
                                <option value="">Semua Lapangan</option>
                                <?php foreach ($lapangan_options as $lap): ?>
                                    <option value="<?php echo $lap['lapangan_id']; ?>" 
                                        <?php echo ($filter_lapangan == $lap['lapangan_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($lap['nama_lapangan']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label for="hari" style="margin-bottom: 5px;">Hari</label>
                            <select name="hari" id="hari" class="form-control">
                                <option value="">Semua Hari</option>
                                <?php 
                                $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                foreach ($hari_list as $h): 
                                ?>
                                    <option value="<?php echo $h; ?>" <?php echo ($filter_hari == $h) ? 'selected' : ''; ?>>
                                        <?php echo $h; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="d-flex gap-10">
                            <button type="submit" class="btn btn-primary">🔍 Filter</button>
                            <?php if (!empty($search) || $filter_lapangan > 0 || !empty($filter_hari)): ?>
                                <a href="index.php" class="btn btn-secondary">✕ Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-between align-center">
                    <h3>Daftar Jadwal Slot Waktu
                        <?php if (!empty($search) || $filter_lapangan > 0 || !empty($filter_hari)): ?>
                            <span style="font-size: 14px; color: #9D4EDD; font-weight: normal;">
                                (<?php echo count($jadwal_list); ?> hasil)
                            </span>
                        <?php endif; ?>
                    </h3>
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
                                        <td colspan="8" class="text-center">
                                            <?php if (!empty($search)): ?>
                                                Tidak ada hasil untuk pencarian "<?php echo htmlspecialchars($search); ?>"
                                            <?php else: ?>
                                                Belum ada data jadwal
                                            <?php endif; ?>
                                        </td>
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