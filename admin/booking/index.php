<?php
require_once '../auth.php';

$page = 'booking';
$page_title = 'Data Booking';
$base_url = '..';

// Cancel
if (isset($_GET['cancel'])) {
    $id = intval($_GET['cancel']);
    q("UPDATE bookings SET status_booking='cancelled' WHERE booking_id=?", [$id]);
    q("UPDATE payment SET status_payment='cancelled' WHERE Bookings_booking_id=?", [$id]);
    header('Location: index.php?success=Booking dibatalkan');
    exit;
}

// Filter
$search = $_GET['search'] ?? '';
$start = $_GET['start_date'] ?? '';
$end = $_GET['end_date'] ?? '';

$sql = "SELECT b.*, u.nama_pelanggan, u.no_hp, u.email,
        (SELECT COUNT(*) FROM booking_detail WHERE booking_id=b.booking_id) as total_slot
        FROM bookings b
        JOIN users u ON b.users_user_id = u.user_id
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (u.nama_pelanggan LIKE ? OR u.email LIKE ? OR u.no_hp LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($start && $end) {
    $sql .= " AND b.tgl_booking BETWEEN ? AND ?";
    $params[] = $start;
    $params[] = $end;
} elseif ($start) {
    $sql .= " AND b.tgl_booking >= ?";
    $params[] = $start;
} elseif ($end) {
    $sql .= " AND b.tgl_booking <= ?";
    $params[] = $end;
}

$sql .= " ORDER BY b.booking_id DESC";
$bookings = fetchAll($sql, $params);

require_once '../includes/header.php';
?>

<div class="card">
    <form method="GET" style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:15px; align-items:end;">
        <div class="form-group" style="margin:0;">
            <label>Cari Pelanggan</label>
            <input type="text" name="search" class="form-control" placeholder="Nama, email, HP" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="form-group" style="margin:0;">
            <label>Tanggal Dari</label>
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start) ?>">
        </div>
        <div class="form-group" style="margin:0;">
            <label>Tanggal Sampai</label>
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end) ?>">
        </div>
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn">Filter</button>
            <?php if ($search || $start || $end): ?>
                <a href="index.php" class="btn">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="d-flex justify-between">
        <h3>Daftar Booking</h3>
        <a href="tambah_booking.php" class="btn">Tambah</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>No HP</th>
                <th>Tanggal</th>
                <th>Slot</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
            <?php else: ?>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td>#<?= $b['booking_id'] ?></td>
                        <td><?= htmlspecialchars($b['nama_pelanggan']) ?></td>
                        <td><?= htmlspecialchars($b['no_hp']) ?></td>
                        <td><?= date('d/m/Y', strtotime($b['tgl_booking'])) ?></td>
                        <td><?= $b['total_slot'] ?> slot</td>
                        <td>Rp <?= number_format($b['total_harga'], 0, ',', '.') ?></td>
                        <td>
                            <?php
                            $badge = ['pending'=>'warning','confirmed'=>'info','completed'=>'success','cancelled'=>'danger'];
                            $class = $badge[$b['status_booking']] ?? 'info';
                            ?>
                            <span class="badge badge-<?= $class ?>"><?= ucfirst($b['status_booking']) ?></span>
                        </td>
                        <td>
                            <a href="detail_booking.php?id=<?= $b['booking_id'] ?>" class="btn btn-sm">Detail</a>
                            <?php if (!in_array($b['status_booking'], ['cancelled','completed'])): ?>
                                <a href="index.php?cancel=<?= $b['booking_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin batalkan?')">Batalkan</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>