<?php
/**
 * Detail Booking
 * Admin Panel - Lihat Detail Booking & Slot
 */

require_once '../auth.php';
require_once '../db.php';

$booking_id = intval($_GET['id'] ?? 0);

if ($booking_id == 0) {
    header('Location: index.php?error=Booking ID tidak valid!');
    exit;
}

// Fetch booking data
$booking = fetchOne("
    SELECT 
        b.*,
        u.nama_pelanggan,
        u.no_hp,
        u.email,
        u.alamat
    FROM bookings b
    JOIN users u ON b.users_user_id = u.user_id
    WHERE b.booking_id = ?
", [$booking_id]);

if (!$booking) {
    header('Location: index.php?error=Booking tidak ditemukan!');
    exit;
}

// Fetch booking details (slots)
$booking_details = fetchAll("
    SELECT 
        bd.*,
        j.hari,
        j.jam_mulai_slot,
        j.jam_selesai_slot,
        l.nama_lapangan,
        l.jenis_lapangan
    FROM booking_detail bd
    JOIN jadwallapangan j ON bd.jadwal_id = j.jadwal_id
    JOIN lapangan l ON j.Lapangan_lapangan_id = l.lapangan_id
    WHERE bd.booking_id = ?
    ORDER BY j.hari, bd.jam_mulai
", [$booking_id]);

// Fetch payment data
$payment = fetchOne("
    SELECT * FROM payment 
    WHERE Bookings_booking_id = ?
", [$booking_id]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Booking #<?php echo $booking_id; ?> - Admin Panel</title>
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
            <h1>Detail Booking #<?php echo $booking_id; ?></h1>
            <div class="header-right">
                <span>👤 <?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>

        <div class="container">
            <!-- Booking Info -->
            <div class="card">
                <div class="card-header">
                    <h3>Informasi Booking</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div>
                            <p><strong>Booking ID:</strong> #<?php echo $booking['booking_id']; ?></p>
                            <p><strong>Nama Pelanggan:</strong> <?php echo htmlspecialchars($booking['nama_pelanggan']); ?></p>
                            <p><strong>No HP:</strong> <?php echo htmlspecialchars($booking['no_hp']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
                            <p><strong>Alamat:</strong> <?php echo htmlspecialchars($booking['alamat']); ?></p>
                        </div>
                        <div>
                            <p><strong>Tanggal Booking:</strong> <?php echo date('d F Y', strtotime($booking['tgl_booking'])); ?></p>
                            <p><strong>Total Harga:</strong> <span style="color: #5A189A; font-size: 18px; font-weight: 600;">Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></span></p>
                            <p><strong>Status Booking:</strong> 
                                <?php
                                $badge_class = 'badge-info';
                                switch($booking['status_booking']) {
                                    case 'pending': $badge_class = 'badge-warning'; break;
                                    case 'confirmed': $badge_class = 'badge-info'; break;
                                    case 'completed': $badge_class = 'badge-success'; break;
                                    case 'cancelled': $badge_class = 'badge-danger'; break;
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($booking['status_booking']); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Details (Slots) -->
            <div class="card">
                <div class="card-header">
                    <h3>Detail Slot Booking</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Lapangan</th>
                                    <th>Jenis</th>
                                    <th>Hari</th>
                                    <th>Jam Main</th>
                                    <th>Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($booking_details)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada detail slot</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($booking_details as $detail): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($detail['nama_lapangan']); ?></td>
                                            <td><?php echo htmlspecialchars($detail['jenis_lapangan']); ?></td>
                                            <td><?php echo htmlspecialchars($detail['hari']); ?></td>
                                            <td>
                                                <?php 
                                                echo date('H:i', strtotime($detail['jam_mulai'])); 
                                                echo ' - '; 
                                                echo date('H:i', strtotime($detail['jam_selesai'])); 
                                                ?>
                                            </td>
                                            <td>Rp <?php echo number_format($detail['harga'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Info -->
            <?php if ($payment): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Informasi Payment</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>Payment ID:</strong> #<?php echo $payment['payment_id']; ?></p>
                        <p><strong>Jumlah Bayar:</strong> Rp <?php echo number_format($payment['jumlah_bayar'], 0, ',', '.'); ?></p>
                        <p><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($payment['metode_pembayaran']); ?></p>
                        <p><strong>Status Payment:</strong> 
                            <?php
                            $payment_badge = 'badge-info';
                            if ($payment['status_payment'] == 'pending') $payment_badge = 'badge-warning';
                            elseif ($payment['status_payment'] == 'success') $payment_badge = 'badge-success';
                            elseif ($payment['status_payment'] == 'cancelled') $payment_badge = 'badge-danger';
                            ?>
                            <span class="badge <?php echo $payment_badge; ?>"><?php echo ucfirst($payment['status_payment']); ?></span>
                        </p>
                        <?php if ($payment['tgl_pembayaran']): ?>
                            <p><strong>Tanggal Pembayaran:</strong> <?php echo date('d F Y H:i', strtotime($payment['tgl_pembayaran'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    Belum ada data payment untuk booking ini.
                </div>
            <?php endif; ?>

            <!-- Back Button -->
            <div style="margin-top: 20px;">
                <a href="index.php" class="btn btn-secondary">← Kembali</a>
            </div>
        </div>
    </div>
</body>
</html>