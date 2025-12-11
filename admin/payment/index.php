<?php
/**
 * List Payment
 * Admin Panel - Data Payment dengan Pencarian dan Filter
 */

require_once '../auth.php';
require_once '../db.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Handle Search and Filter
$search = trim($_GET['search'] ?? '');
$filter_status = trim($_GET['status'] ?? '');
$filter_metode = trim($_GET['metode'] ?? '');
$start_date = trim($_GET['start_date'] ?? '');
$end_date = trim($_GET['end_date'] ?? '');

// Handle Konfirmasi Lunas
if (isset($_GET['confirm'])) {
    $payment_id = intval($_GET['confirm']);
    
    // Update status payment to success and set tgl_pembayaran
    $update = q("
        UPDATE payment 
        SET status_payment = 'success', tgl_pembayaran = NOW() 
        WHERE payment_id = ?
    ", [$payment_id]);
    
    if ($update) {
        // Also update booking status to confirmed
        $payment_data = fetchOne("SELECT Bookings_booking_id FROM payment WHERE payment_id = ?", [$payment_id]);
        if ($payment_data) {
            q("UPDATE bookings SET status_booking = 'confirmed' WHERE booking_id = ?", [$payment_data['Bookings_booking_id']]);
        }
        
        header('Location: index.php?success=Payment berhasil dikonfirmasi!');
    } else {
        header('Location: index.php?error=Gagal mengkonfirmasi payment!');
    }
    exit;
}

// Build query with JOIN, search and filter
$sql = "SELECT 
        p.*,
        b.tgl_booking,
        b.total_harga as booking_total,
        b.status_booking,
        u.nama_pelanggan,
        u.no_hp,
        u.email
    FROM payment p
    JOIN bookings b ON p.Bookings_booking_id = b.booking_id
    JOIN users u ON b.users_user_id = u.user_id
    WHERE 1=1";

$params = [];

// Add search condition
if (!empty($search)) {
    $sql .= " AND (u.nama_pelanggan LIKE ? OR u.email LIKE ? OR u.no_hp LIKE ? OR p.payment_id LIKE ? OR b.booking_id LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Add status filter
if (!empty($filter_status)) {
    $sql .= " AND p.status_payment = ?";
    $params[] = $filter_status;
}

// Add metode filter
if (!empty($filter_metode)) {
    $sql .= " AND p.metode_pembayaran = ?";
    $params[] = $filter_metode;
}

// Add date range filter
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND p.tgl_pembayaran BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
} elseif (!empty($start_date)) {
    $sql .= " AND p.tgl_pembayaran >= ?";
    $params[] = $start_date;
} elseif (!empty($end_date)) {
    $sql .= " AND p.tgl_pembayaran <= ?";
    $params[] = $end_date;
}

$sql .= " ORDER BY p.payment_id DESC";

// Fetch all payments
$payments = fetchAll($sql, $params);

// Get unique metode pembayaran for filter
$metode_list = fetchAll("SELECT DISTINCT metode_pembayaran FROM payment ORDER BY metode_pembayaran");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Payment - Admin Panel</title>
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
            <li><a href="../booking/index.php">Data Booking</a></li>
            <li><a href="../user/index.php">Data User</a></li>
            <li><a href="index.php" class="active">Data Payment</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Data Payment</h1>
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
                    <form method="GET" action="">
                        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                            <div class="form-group" style="margin: 0;">
                                <label for="search" style="margin-bottom: 5px;">Cari Payment</label>
                                <input 
                                    type="text" 
                                    id="search"
                                    name="search" 
                                    class="form-control" 
                                    placeholder="Nama, email, no HP, ID payment, atau ID booking"
                                    value="<?php echo htmlspecialchars($search); ?>"
                                >
                            </div>
                            <div class="form-group" style="margin: 0;">
                                <label for="status" style="margin-bottom: 5px;">Status Payment</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">Semua Status</option>
                                    <option value="pending" <?php echo ($filter_status === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="success" <?php echo ($filter_status === 'success') ? 'selected' : ''; ?>>Lunas</option>
                                    <option value="cancelled" <?php echo ($filter_status === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin: 0;">
                                <label for="metode" style="margin-bottom: 5px;">Metode Pembayaran</label>
                                <select name="metode" id="metode" class="form-control">
                                    <option value="">Semua Metode</option>
                                    <?php foreach ($metode_list as $metode): ?>
                                        <option value="<?php echo htmlspecialchars($metode['metode_pembayaran']); ?>"
                                            <?php echo ($filter_metode === $metode['metode_pembayaran']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($metode['metode_pembayaran']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: end;">
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
                                <?php if (!empty($search) || !empty($filter_status) || !empty($filter_metode) || !empty($start_date) || !empty($end_date)): ?>
                                    <a href="index.php" class="btn btn-secondary">✕ Reset</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Daftar Payment
                        <?php if (!empty($search) || !empty($filter_status) || !empty($filter_metode) || !empty($start_date) || !empty($end_date)): ?>
                            <span style="font-size: 14px; color: #9D4EDD; font-weight: normal;">
                                (<?php echo count($payments); ?> hasil)
                            </span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Payment ID</th>
                                    <th>Booking ID</th>
                                    <th>Pelanggan</th>
                                    <th>No HP</th>
                                    <th>Jumlah Bayar</th>
                                    <th>Metode</th>
                                    <th>Status Payment</th>
                                    <th>Tgl Pembayaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($payments)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <?php if (!empty($search)): ?>
                                                Tidak ada hasil untuk pencarian "<?php echo htmlspecialchars($search); ?>"
                                            <?php else: ?>
                                                Belum ada data payment
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td>#<?php echo $payment['payment_id']; ?></td>
                                            <td>
                                                <a href="../booking/detail.php?id=<?php echo $payment['Bookings_booking_id']; ?>" 
                                                   style="color: #9D4EDD; font-weight: 500;">
                                                    #<?php echo $payment['Bookings_booking_id']; ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($payment['nama_pelanggan']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['no_hp']); ?></td>
                                            <td>Rp <?php echo number_format($payment['jumlah_bayar'], 0, ',', '.'); ?></td>
                                            <td><?php echo htmlspecialchars($payment['metode_pembayaran']); ?></td>
                                            <td>
                                                <?php
                                                $badge_class = 'badge-info';
                                                $status_text = ucfirst($payment['status_payment']);
                                                
                                                switch($payment['status_payment']) {
                                                    case 'pending':
                                                        $badge_class = 'badge-warning';
                                                        $status_text = 'Pending';
                                                        break;
                                                    case 'success':
                                                        $badge_class = 'badge-success';
                                                        $status_text = 'Lunas';
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
                                                <?php 
                                                if ($payment['tgl_pembayaran']) {
                                                    echo date('d/m/Y H:i', strtotime($payment['tgl_pembayaran']));
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($payment['status_payment'] == 'pending'): ?>
                                                    <a href="index.php?confirm=<?php echo $payment['payment_id']; ?>" 
                                                       class="btn btn-success btn-small" 
                                                       onclick="return confirm('Konfirmasi payment ini sebagai LUNAS?')">
                                                        ✓ Konfirmasi Lunas
                                                    </a>
                                                <?php else: ?>
                                                    <span style="color: #999; font-size: 13px;">-</span>
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

            <!-- Payment Statistics -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
                <div class="card" style="text-align: center;">
                    <div style="font-size: 36px; color: #9D4EDD; margin-bottom: 10px;">💰</div>
                    <h3 style="color: #5A189A; margin-bottom: 5px;">
                        Rp <?php 
                            $total_pending = fetchOne("SELECT SUM(jumlah_bayar) as total FROM payment WHERE status_payment = 'pending'")['total'] ?? 0;
                            echo number_format($total_pending, 0, ',', '.'); 
                        ?>
                    </h3>
                    <p style="color: #666; font-size: 14px;">Total Pending</p>
                </div>

                <div class="card" style="text-align: center;">
                    <div style="font-size: 36px; color: #28a745; margin-bottom: 10px;">✓</div>
                    <h3 style="color: #5A189A; margin-bottom: 5px;">
                        Rp <?php 
                            $total_success = fetchOne("SELECT SUM(jumlah_bayar) as total FROM payment WHERE status_payment = 'success'")['total'] ?? 0;
                            echo number_format($total_success, 0, ',', '.'); 
                        ?>
                    </h3>
                    <p style="color: #666; font-size: 14px;">Total Revenue (Lunas)</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>