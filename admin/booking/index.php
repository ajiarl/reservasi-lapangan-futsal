<?php
/**
 * List Bookings
 * Admin Panel - Data Booking
 */

require_once '../auth.php';
require_once '../db.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Handle Search
$search = trim($_GET['search'] ?? '');
$start_date = trim($_GET['start_date'] ?? '');
$end_date = trim($_GET['end_date'] ?? '');

// Handle Cancel Booking
if (isset($_GET['cancel'])) {
    $booking_id = intval($_GET['cancel']);
    
    // Update status to cancelled
    $update = q("UPDATE bookings SET status_booking = 'cancelled' WHERE booking_id = ?", [$booking_id]);
    
    if ($update) {
        // Also update payment status if exists
        q("UPDATE payment SET status_payment = 'cancelled' WHERE Bookings_booking_id = ?", [$booking_id]);
        header('Location: index.php?success=Booking berhasil dibatalkan!');
    } else {
        header('Location: index.php?error=Gagal membatalkan booking!');
    }
    exit;
}

// Fetch all bookings with user info
$sql = "SELECT 
        b.*,
        u.nama_pelanggan,
        u.no_hp,
        u.email,
        (SELECT COUNT(*) FROM booking_detail WHERE booking_id = b.booking_id) as total_slot
    FROM bookings b
    JOIN users u ON b.users_user_id = u.user_id
    WHERE 1=1";

$params = [];

// Add search condition if search query exists
if (!empty($search)) {
    $sql .= " AND (u.nama_pelanggan LIKE ? OR u.email LIKE ? OR u.no_hp LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Add date range filter using BETWEEN
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND b.tgl_booking BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
} elseif (!empty($start_date)) {
    $sql .= " AND b.tgl_booking >= ?";
    $params[] = $start_date;
} elseif (!empty($end_date)) {
    $sql .= " AND b.tgl_booking <= ?";
    $params[] = $end_date;
}

$sql .= " ORDER BY b.booking_id DESC";

$bookings = fetchAll($sql, $params);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Booking - Admin Panel</title>
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
            <li><a href="../jadwal/index.php">Data Jadwal</a></li>
            <li><a href="index.php" class="active">Data Booking</a></li>
            <li><a href="../user/index.php">Data User</a></li>
            <li><a href="../payment/index.php">Data Payment</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Data Booking</h1>
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
                    <form method="GET" action="" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: end;">
                        <div class="form-group" style="margin: 0;">
                            <label for="search" style="margin-bottom: 5px;">Cari Pelanggan</label>
                            <input 
                                type="text" 
                                id="search"
                                name="search" 
                                class="form-control" 
                                placeholder="Nama, email, atau no HP"
                                value="<?php echo htmlspecialchars($search); ?>"
                            >
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label for="start_date" style="margin-bottom: 5px;">Tanggal Dari</label>
                            <input 
                                type="date" 
                                id="start_date"
                                name="start_date" 
                                class="form-control" 
                                value="<?php echo htmlspecialchars($start_date); ?>"
                            >
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label for="end_date" style="margin-bottom: 5px;">Tanggal Sampai</label>
                            <input 
                                type="date" 
                                id="end_date"
                                name="end_date" 
                                class="form-control" 
                                value="<?php echo htmlspecialchars($end_date); ?>"
                            >
                        </div>
                        <div class="d-flex gap-10">
                            <button type="submit" class="btn btn-primary">🔍 Filter</button>
                            <?php if (!empty($search) || !empty($start_date) || !empty($end_date)): ?>
                                <a href="index.php" class="btn btn-secondary">✕ Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-between align-center">
                    <h3>Daftar Booking
                        <?php if (!empty($search) || !empty($start_date) || !empty($end_date)): ?>
                            <span style="font-size: 14px; color: #9D4EDD; font-weight: normal;">
                                (<?php echo count($bookings); ?> hasil)
                            </span>
                        <?php endif; ?>
                    </h3>
                    <a href="create_manual.php" class="btn btn-primary">+ Booking Manual</a>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Pelanggan</th>
                                    <th>No HP</th>
                                    <th>Email</th>
                                    <th>Tanggal</th>
                                    <th>Total Slot</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($bookings)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <?php if (!empty($search)): ?>
                                                Tidak ada hasil untuk pencarian "<?php echo htmlspecialchars($search); ?>"
                                            <?php else: ?>
                                                Belum ada data booking
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo $booking['booking_id']; ?></td>
                                            <td><?php echo htmlspecialchars($booking['nama_pelanggan']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['no_hp']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($booking['tgl_booking'])); ?></td>
                                            <td class="text-center"><?php echo $booking['total_slot']; ?> slot</td>
                                            <td>Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php
                                                $badge_class = 'badge-info';
                                                $status_text = ucfirst($booking['status_booking']);
                                                
                                                switch($booking['status_booking']) {
                                                    case 'pending':
                                                        $badge_class = 'badge-warning';
                                                        $status_text = 'Pending';
                                                        break;
                                                    case 'confirmed':
                                                        $badge_class = 'badge-info';
                                                        $status_text = 'Confirmed';
                                                        break;
                                                    case 'completed':
                                                        $badge_class = 'badge-success';
                                                        $status_text = 'Completed';
                                                        break;
                                                    case 'cancelled':
                                                        $badge_class = 'badge-danger';
                                                        $status_text = 'Cancelled';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="detail.php?id=<?php echo $booking['booking_id']; ?>" 
                                                   class="btn btn-primary btn-small">Detail</a>
                                                
                                                <?php if ($booking['status_booking'] != 'cancelled' && $booking['status_booking'] != 'completed'): ?>
                                                    <a href="index.php?cancel=<?php echo $booking['booking_id']; ?>" 
                                                       class="btn btn-danger btn-small" 
                                                       onclick="return confirm('Yakin ingin membatalkan booking ini?')">Batalkan</a>
                                                <?php endif; ?>
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