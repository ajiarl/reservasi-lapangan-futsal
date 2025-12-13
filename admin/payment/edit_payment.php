<?php
require_once '../auth.php';

$page = 'payment';
$page_title = 'Edit Payment';
$base_url = '..';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php?error=ID tidak valid');
    exit;
}

$payment = fetchOne("
    SELECT p.*, b.tgl_booking, b.total_harga, u.nama_pelanggan 
    FROM payment p
    JOIN bookings b ON p.Bookings_booking_id = b.booking_id
    JOIN users u ON b.users_user_id = u.user_id
    WHERE p.payment_id=?
", [$id]);

if (!$payment) {
    header('Location: index.php?error=Payment tidak ditemukan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jumlah = floatval($_POST['jumlah_bayar']);
    $metode = trim($_POST['metode_pembayaran']);
    $status = $_POST['status_payment'];
    
    // Update tgl_pembayaran jika status berubah ke Lunas
    if ($status == 'Lunas' && $payment['status_payment'] != 'Lunas') {
        $sql = "UPDATE payment SET jumlah_bayar=?, metode_pembayaran=?, status_payment=?, tgl_pembayaran=NOW() WHERE payment_id=?";
        $result = q($sql, [$jumlah, $metode, $status, $id]);
        
        // Update booking status ke Berlangsung
        q("UPDATE bookings SET status_booking='Berlangsung' WHERE booking_id=?", [$payment['Bookings_booking_id']]);
    } else {
        $sql = "UPDATE payment SET jumlah_bayar=?, metode_pembayaran=?, status_payment=? WHERE payment_id=?";
        $result = q($sql, [$jumlah, $metode, $status, $id]);
    }
    
    if ($result) {
        header('Location: index.php?success=Payment berhasil diupdate');
        exit;
    }
    
    header('Location: index.php?error=Gagal update payment');
    exit;
}

require_once '../includes/header.php';
?>

<div class="card">
    <h3 style="margin-bottom:20px;">Form Edit Payment</h3>
    <form method="POST" style="max-width:600px;">
        <div class="alert" style="background:#f0f0f0; padding:10px; margin-bottom:20px; border-radius:4px;">
            <strong>Booking:</strong> #<?= $payment['Bookings_booking_id'] ?> - <?= htmlspecialchars($payment['nama_pelanggan']) ?><br>
            <strong>Total Booking:</strong> Rp <?= number_format($payment['total_harga'], 0, ',', '.') ?>
        </div>
        
        <div class="form-group">
            <label>Jumlah Bayar *</label>
            <input type="number" name="jumlah_bayar" class="form-control" step="0.01" value="<?= $payment['jumlah_bayar'] ?>" required>
        </div>
        
        <div class="form-group">
            <label>Metode Pembayaran *</label>
            <select name="metode_pembayaran" class="form-control" required>
                <option value="CASH" <?= $payment['metode_pembayaran']=='CASH'?'selected':'' ?>>Cash</option>
                <option value="TRANSFER" <?= $payment['metode_pembayaran']=='TRANSFER'?'selected':'' ?>>Transfer Bank</option>
                <option value="QRIS" <?= $payment['metode_pembayaran']=='QRIS'?'selected':'' ?>>QRIS</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Status *</label>
            <select name="status_payment" class="form-control" required>
                <option value="Pending" <?= $payment['status_payment']=='Pending'?'selected':'' ?>>Pending</option>
                <option value="DP" <?= $payment['status_payment']=='DP'?'selected':'' ?>>DP</option>
                <option value="Lunas" <?= $payment['status_payment']=='Lunas'?'selected':'' ?>>Lunas</option>
            </select>
        </div>
        
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>