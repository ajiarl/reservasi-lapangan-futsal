<?php
/**
 * List Payment
 * Admin Panel - Data Payment
 */

require_once '../auth.php';
require_once '../db.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

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

// Fetch all payments with booking and user info
$payments = fetchAll("
    SELECT 
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
    ORDER BY p.payment_id DESC
");
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

            <div class="card">
                <div class="card-header">
                    <h3>Daftar Payment</h3>
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
                                        <td colspan="9" class="text-center">Belum ada data payment</td>
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