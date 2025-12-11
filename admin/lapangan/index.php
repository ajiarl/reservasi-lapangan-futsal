<?php
/**
 * List Lapangan
 * Admin Panel - Master Data Lapangan dengan Pencarian dan Filter
 */

require_once '../auth.php';
require_once '../db.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Handle Search and Filter
$search = trim($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? '';

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

// Build query with search and filter
$sql = "SELECT * FROM lapangan WHERE 1=1";
$params = [];

// Add search condition
if (!empty($search)) {
    $sql .= " AND (nama_lapangan LIKE ? OR jenis_lapangan LIKE ? OR fasilitas LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Add status filter
if ($filter_status !== '') {
    $sql .= " AND is_active = ?";
    $params[] = intval($filter_status);
}

$sql .= " ORDER BY lapangan_id DESC";

// Fetch all lapangan
$lapangan_list = fetchAll($sql, $params);
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

            <!-- Filter Card -->
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body">
                    <form method="GET" action="" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 15px; align-items: end;">
                        <div class="form-group" style="margin: 0;">
                            <label for="search" style="margin-bottom: 5px;">Cari Lapangan</label>
                            <input 
                                type="text" 
                                id="search"
                                name="search" 
                                class="form-control" 
                                placeholder="Nama, jenis, atau fasilitas"
                                value="<?php echo htmlspecialchars($search); ?>"
                            >
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label for="status" style="margin-bottom: 5px;">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Semua Status</option>
                                <option value="1" <?php echo ($filter_status === '1') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo ($filter_status === '0') ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="d-flex gap-10">
                            <button type="submit" class="btn btn-primary">🔍 Filter</button>
                            <?php if (!empty($search) || $filter_status !== ''): ?>
                                <a href="index.php" class="btn btn-secondary">✕ Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-between align-center">
                    <h3>Daftar Lapangan
                        <?php if (!empty($search) || $filter_status !== ''): ?>
                            <span style="font-size: 14px; color: #9D4EDD; font-weight: normal;">
                                (<?php echo count($lapangan_list); ?> hasil)
                            </span>
                        <?php endif; ?>
                    </h3>
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
                                        <td colspan="6" class="text-center">
                                            <?php if (!empty($search)): ?>
                                                Tidak ada hasil untuk pencarian "<?php echo htmlspecialchars($search); ?>"
                                            <?php else: ?>
                                                Belum ada data lapangan
                                            <?php endif; ?>
                                        </td>
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