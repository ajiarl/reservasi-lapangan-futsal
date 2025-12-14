<?php
require_once '../auth.php';

$page = 'payment';
$page_title = 'Data Payment';
$base_url = '..';

// Konfirmasi Lunas
if (isset($_GET['confirm'])) {
    $id = intval($_GET['confirm']);
    q("UPDATE payment SET status_payment='Lunas', tgl_pembayaran=NOW() WHERE payment_id=?", [$id]);
    
    // NOTE:
    // Status booking tidak diatur di aplikasi
    // Seluruh perubahan status booking ditangani oleh trigger database

    
    header('Location: index.php?success=Payment berhasil dikonfirmasi');
    exit;
}

// Filter
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT p.*, b.tgl_booking, u.nama_pelanggan, u.no_hp 
        FROM payment p
        JOIN bookings b ON p.Bookings_booking_id = b.booking_id
        JOIN users u ON b.users_user_id = u.user_id
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (u.nama_pelanggan LIKE ? OR u.no_hp LIKE ? OR p.payment_id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $sql .= " AND p.status_payment=?";
    $params[] = $status;
}

$sql .= " ORDER BY p.payment_id DESC";
$payments = fetchAll($sql, $params);

// Stats
$pending = fetchOne("SELECT COUNT(*) as t FROM payment WHERE status_payment='Pending'")['t'];
$dp = fetchOne("SELECT COALESCE(SUM(jumlah_bayar), 0) as t FROM payment WHERE status_payment='DP'")['t'];
$lunas = fetchOne("SELECT COALESCE(SUM(jumlah_bayar), 0) as t FROM payment WHERE status_payment='Lunas'")['t'];

require_once '../includes/header.php';
?>

<div class="card">
    <form method="GET" style="display:grid; grid-template-columns:1fr 200px auto; gap:15px; align-items:end;">
        <div class="form-group" style="margin:0;">
            <label>Cari Payment</label>
            <input type="text" name="search" class="form-control" placeholder="Nama, no HP, ID payment" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="form-group" style="margin:0;">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">Semua</option>
                <option value="Pending" <?= $status=='Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="DP" <?= $status=='DP' ? 'selected' : '' ?>>DP</option>
                <option value="Lunas" <?= $status=='Lunas' ? 'selected' : '' ?>>Lunas</option>
            </select>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($search || $status): ?>
                <a href="index.php" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Stats -->
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:20px;">
    <div class="card" style="text-align:center;">
        <div style="font-size:36px; margin-bottom:10px;">⏳</div>
        <h2><?= $pending ?></h2>
        <p>Jumlah Payment Pending</p>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:36px; margin-bottom:10px;">💰</div>
        <h2>Rp <?= number_format($dp, 0, ',', '.') ?></h2>
        <p>Total DP</p>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:36px; margin-bottom:10px;">✓</div>
        <h2>Rp <?= number_format($lunas, 0, ',', '.') ?></h2>
        <p>Total Lunas</p>
    </div>
</div>

<div class="card">
    <div class="d-flex justify-between" style="margin-bottom: 15px;">
        <h3>Daftar Payment</h3>
        <a href="tambah_payment.php" class="btn btn-primary">Tambah</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Booking</th>
                <th>Pelanggan</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Tgl Bayar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
            <?php else: ?>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td>#<?= $p['payment_id'] ?></td>
                        <td><a href="../booking/detail.php?id=<?= $p['Bookings_booking_id'] ?>" style="color:#333;">#<?= $p['Bookings_booking_id'] ?></a></td>
                        <td><?= htmlspecialchars($p['nama_pelanggan']) ?></td>
                        <td>Rp <?= number_format($p['jumlah_bayar'], 0, ',', '.') ?></td>
                        <td>
                            <?php 
                            $metode_display = [
                                'CASH' => 'Cash',
                                'TRANSFER' => 'Transfer',
                                'QRIS' => 'QRIS'
                            ];
                            echo $metode_display[$p['metode_pembayaran']] ?? $p['metode_pembayaran'];
                            ?>
                        </td>
                        <td>
                            <?php
                            $badge_class = [
                                'Lunas' => 'success',
                                'DP' => 'warning',
                                'Pending' => 'danger'
                            ];
                            $class = $badge_class[$p['status_payment']] ?? 'secondary';
                            ?>
                            <span class="badge badge-<?= $class ?>">
                                <?= $p['status_payment'] ?>
                            </span>
                        </td>
                        <td><?= $p['tgl_pembayaran'] ? date('d/m/Y H:i', strtotime($p['tgl_pembayaran'])) : '-' ?></td>
                        <td>
                            <a href="edit_payment.php?id=<?= $p['payment_id'] ?>" class="btn btn-edit btn-sm">Edit</a>
                            <a href="hapus_payment.php?id=<?= $p['payment_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>