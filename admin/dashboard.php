<?php
/**
 * Admin Dashboard - Final Version
 * Overview, Statistics & Daily Revenue Report
 */

require_once 'auth.php';
require_once 'db.php';

// ========================================
// 1. RINGKASAN STATISTIK
// ========================================

// Total User
$total_user = fetchOne("SELECT COUNT(*) as total FROM users")['total'];
$total_pelanggan = fetchOne("SELECT COUNT(*) as total FROM users WHERE role = 'pelanggan'")['total'];
$total_admin = fetchOne("SELECT COUNT(*) as total FROM users WHERE role = 'admin'")['total'];

// Total Lapangan
$total_lapangan = fetchOne("SELECT COUNT(*) as total FROM lapangan")['total'];
$active_lapangan = fetchOne("SELECT COUNT(*) as total FROM lapangan WHERE is_active = 1")['total'];

// Total Jadwal
$total_jadwal = fetchOne("SELECT COUNT(*) as total FROM jadwallapangan")['total'];

// Booking Statistics
$total_booking = fetchOne("SELECT COUNT(*) as total FROM bookings")['total'];
$booking_pending = fetchOne("SELECT COUNT(*) as total FROM bookings WHERE status_booking = 'pending'")['total'];
$booking_confirmed = fetchOne("SELECT COUNT(*) as total FROM bookings WHERE status_booking = 'confirmed'")['total'];
$booking_completed = fetchOne("SELECT COUNT(*) as total FROM bookings WHERE status_booking = 'completed'")['total'];
$booking_cancelled = fetchOne("SELECT COUNT(*) as total FROM bookings WHERE status_booking = 'cancelled'")['total'];

// Booking Aktif (Pending + Confirmed)
$booking_aktif = $booking_pending + $booking_confirmed;

// Payment Statistics
$payment_pending = fetchOne("SELECT COUNT(*) as total FROM payment WHERE status_payment = 'pending'")['total'];
$payment_success = fetchOne("SELECT COUNT(*) as total FROM payment WHERE status_payment = 'success'")['total'];

// Total Revenue
$total_revenue = fetchOne("SELECT SUM(jumlah_bayar) as total FROM payment WHERE status_payment = 'success'")['total'] ?? 0;

// ========================================
// 2. TABEL PENDAPATAN HARIAN
// ========================================
$daily_revenue = fetchAll("
    SELECT 
        DATE(tgl_pembayaran) as tgl, 
        SUM(jumlah_bayar) as total,
        COUNT(*) as jumlah_transaksi
    FROM payment 
    WHERE status_payment = 'success'
    GROUP BY DATE(tgl_pembayaran)
    ORDER BY tgl DESC
    LIMIT 30
");

// Recent Bookings (5 latest)
$recent_bookings = fetchAll("
    SELECT b.*, u.nama_pelanggan, u.no_hp 
    FROM bookings b
    JOIN users u ON b.users_user_id = u.user_id
    ORDER BY b.booking_id DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
            <p style="font-size: 13px; opacity: 0.8;">Futsal Reservation</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="lapangan/index.php">Data Lapangan</a></li>
            <li><a href="jadwal/index.php">Data Jadwal</a></li>
            <li><a href="booking/index.php">Data Booking</a></li>
            <li><a href="user/index.php">Data User</a></li>
            <li><a href="payment/index.php">Data Payment</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Dashboard</h1>
            <div class="header-right">
                <span>👤 <?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>

        <div class="container">
            <!-- Welcome Message -->
            <div class="alert alert-info" style="margin-bottom: 30px;">
                <strong>Selamat datang, <?php echo htmlspecialchars($admin_name); ?>!</strong><br>
                Berikut adalah ringkasan sistem reservasi futsal Anda.
            </div>

            <!-- ========================================
                 SECTION 1: RINGKASAN STATISTIK
            ========================================= -->
            <h2 style="color: #5A189A; margin-bottom: 20px;">📊 Ringkasan Statistik</h2>
            
            <!-- Statistics Cards Row 1 -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Card: Total User -->
                <div class="card" style="text-align: center;">
                    <div style="font-size: 48px; color: #9D4EDD; margin-bottom: 10px;">👥</div>
                    <h3 style="color: #5A189A; margin-bottom: 5px;"><?php echo $total_user; ?></h3>
                    <p style="color: #666; font-size: 14px;">Total User</p>
                    <p style="color: #999; font-size: 13px; margin-top: 5px;">
                        <?php echo $total_pelanggan; ?> Pelanggan | <?php echo $total_admin; ?> Admin
                    </p>
                </div>

                <!-- Card: Booking Aktif -->
                <div class="card" style="text-align: center;">
                    <div style="font-size: 48px; color: #ff9800; margin-bottom: 10px;">📋</div>
                    <h3 style="color: #5A189A; margin-bottom: 5px;"><?php echo $booking_aktif; ?></h3>
                    <p style="color: #666; font-size: 14px;">Booking Aktif</p>
                    <p style="color: #999; font-size: 13px; margin-top: 5px;">
                        Pending: <?php echo $booking_pending; ?> | Confirmed: <?php echo $booking_confirmed; ?>
                    </p>
                </div>

                <!-- Card: Total Booking -->
                <div class="card" style="text-align: center;">
                    <div style="font-size: 48px; color: #9D4EDD; margin-bottom: 10px;">📊</div>
                    <h3 style="color: #5A189A; margin-bottom: 5px;"><?php echo $total_booking; ?></h3>
                    <p style="color: #666; font-size: 14px;">Total Booking</p>
                    <p style="color: #28a745; font-size: 13px; margin-top: 5px;">
                        Completed: <?php echo $booking_completed; ?>
                    </p>
                </div>

                <!-- Card: Total Lapangan -->
                <div class="card" style="text-align: center;">
                    <div style="font-size: 48px; color: #9D4EDD; margin-bottom: 10px;">⚽</div>
                    <h3 style="color: #5A189A; margin-bottom: 5px;"><?php echo $total_lapangan; ?></h3>
                    <p style="color: #666; font-size: 14px;">Total Lapangan</p>
                    <p style="color: #28a745; font-size: 13px; margin-top: 5px;">
                        <?php echo $active_lapangan; ?> Aktif
                    </p>
                </div>
            </div>

            <!-- Booking & Payment Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <!-- Booking Status Breakdown -->
                <div class="card">
                    <div class="card-header">
                        <h3>Status Booking</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span>Pending:</span>
                            <span class="badge badge-warning"><?php echo $booking_pending; ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span>Confirmed:</span>
                            <span class="badge badge-info"><?php echo $booking_confirmed; ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span>Completed:</span>
                            <span class="badge badge-success"><?php echo $booking_completed; ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                            <span>Cancelled:</span>
                            <span class="badge badge-danger"><?php echo $booking_cancelled; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Payment & Revenue -->
                <div class="card">
                    <div class="card-header">
                        <h3>Payment & Revenue</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span>Payment Pending:</span>
                            <span class="badge badge-warning"><?php echo $payment_pending; ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                            <span>Payment Success:</span>
                            <span class="badge badge-success"><?php echo $payment_success; ?></span>
                        </div>
                        <div style="padding: 15px 0; text-align: center; background: #f9f9f9; border-radius: 5px; margin-top: 10px;">
                            <p style="font-size: 13px; color: #666; margin-bottom: 5px;">Total Revenue (Lunas)</p>
                            <h2 style="color: #5A189A; margin: 0;">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================
                 SECTION 2: TABEL PENDAPATAN HARIAN
            ========================================= -->
            <h2 style="color: #5A189A; margin-bottom: 20px; margin-top: 40px;">💰 Laporan Pendapatan Harian</h2>
            
            <div class="card">
                <div class="card-header">
                    <h3>Pendapatan per Hari (30 Hari Terakhir)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($daily_revenue)): ?>
                        <div class="alert alert-info">
                            Belum ada data pendapatan yang lunas.
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jumlah Transaksi</th>
                                        <th class="text-right">Total Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $grand_total = 0;
                                    foreach ($daily_revenue as $revenue): 
                                        $grand_total += $revenue['total'];
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo date('d F Y', strtotime($revenue['tgl'])); ?></strong>
                                                <br>
                                                <small style="color: #999;"><?php echo date('l', strtotime($revenue['tgl'])); ?></small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info"><?php echo $revenue['jumlah_transaksi']; ?> transaksi</span>
                                            </td>
                                            <td class="text-right">
                                                <strong style="color: #5A189A;">
                                                    Rp <?php echo number_format($revenue['total'], 0, ',', '.'); ?>
                                                </strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <!-- Grand Total -->
                                    <tr style="background: #f9f9f9; font-weight: 600;">
                                        <td colspan="2" class="text-right">TOTAL KESELURUHAN:</td>
                                        <td class="text-right" style="color: #5A189A; font-size: 16px;">
                                            Rp <?php echo number_format($grand_total, 0, ',', '.'); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ========================================
                 SECTION 3: BOOKING TERBARU
            ========================================= -->
            <h2 style="color: #5A189A; margin-bottom: 20px; margin-top: 40px;">🔔 Booking Terbaru</h2>
            
            <div class="card">
                <div class="card-header">
                    <h3>5 Booking Terbaru</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Pelanggan</th>
                                    <th>No HP</th>
                                    <th>Tanggal Booking</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_bookings)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Belum ada booking</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <a href="booking/detail.php?id=<?php echo $booking['booking_id']; ?>" 
                                                   style="color: #9D4EDD; font-weight: 600;">
                                                    #<?php echo $booking['booking_id']; ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($booking['nama_pelanggan']); ?></td>
                                            <td><?php echo htmlspecialchars($booking['no_hp']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($booking['tgl_booking'])); ?></td>
                                            <td>Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php
                                                $badge_class = 'badge-info';
                                                if ($booking['status_booking'] == 'pending') $badge_class = 'badge-warning';
                                                elseif ($booking['status_booking'] == 'completed') $badge_class = 'badge-success';
                                                elseif ($booking['status_booking'] == 'cancelled') $badge_class = 'badge-danger';
                                                ?>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo ucfirst($booking['status_booking']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!empty($recent_bookings)): ?>
                        <div style="text-align: center; margin-top: 15px;">
                            <a href="booking/index.php" class="btn btn-primary btn-small">Lihat Semua Booking</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer Info -->
            <div style="text-align: center; margin-top: 40px; padding: 20px; color: #999; font-size: 13px;">
                <p>© 2024 Futsal Reservation System - Admin Panel</p>
                <p>Developed with ❤️ for efficient field management</p>
            </div>
        </div>
    </div>
</body>
</html>