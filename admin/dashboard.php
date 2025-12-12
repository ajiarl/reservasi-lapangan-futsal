<?php
require_once 'auth.php';

$page = 'dashboard';
$page_title = 'Dashboard';
$base_url = '.';

// Stats
$stats = [
    'users' => fetchOne("SELECT COUNT(*) as c FROM users")['c'],
    'lapangan' => fetchOne("SELECT COUNT(*) as c FROM lapangan WHERE is_active=1")['c'],
    'bookings' => fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status_booking IN ('pending','confirmed')")['c'],
    'revenue' => fetchOne("SELECT SUM(jumlah_bayar) as c FROM payment WHERE status_payment='success'")['c'] ?? 0
];

// Recent bookings
$recent = fetchAll("
    SELECT b.*, u.nama_pelanggan 
    FROM bookings b
    JOIN users u ON b.users_user_id = u.user_id
    ORDER BY b.booking_id DESC
    LIMIT 5
");

require_once 'includes/header.php';
?>

<!-- Stats Cards -->
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:30px;">
    <div class="card" style="text-align:center;">
        <div style="font-size:36px; margin-bottom:10px;">👥</div>
        <h2><?= $stats['users'] ?></h2>
        <p>Total User</p>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:36px; margin-bottom:10px;">⚽</div>
        <h2><?= $stats['lapangan'] ?></h2>
        <p>Lapangan Aktif</p>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:36px; margin-bottom:10px;">📋</div>
        <h2><?= $stats['bookings'] ?></h2>
        <p>Booking Aktif</p>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:36px; margin-bottom:10px;">💰</div>
        <h2>Rp <?= number_format($stats['revenue'], 0, ',', '.') ?></h2>
        <p>Total Revenue</p>
    </div>
</div>

<!-- Recent Bookings -->
<div class="card">
    <h3>Booking Terbaru</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Tanggal</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recent)): ?>
                <tr><td colspan="5" class="text-center">Belum ada booking</td></tr>
            <?php else: ?>
                <?php foreach ($recent as $r): ?>
                    <tr>
                        <td>#<?= $r['booking_id'] ?></td>
                        <td><?= $r['nama_pelanggan'] ?></td>
                        <td><?= date('d/m/Y', strtotime($r['tgl_booking'])) ?></td>
                        <td>Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
                        <td><span class="badge badge-<?= $r['status_booking']=='pending'?'warning':'success' ?>"><?= $r['status_booking'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>