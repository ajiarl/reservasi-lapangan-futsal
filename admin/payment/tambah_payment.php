<?php
require_once '../auth.php';

$page = 'payment';
$page_title = 'Tambah Payment';
$base_url = '..';

// Get bookings tanpa payment
$bookings = fetchAll("
    SELECT b.*, u.nama_pelanggan 
    FROM bookings b
    JOIN users u ON b.users_user_id = u.user_id
    WHERE b.booking_id NOT IN (SELECT Bookings_booking_id FROM payment)
    ORDER BY b.booking_id DESC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id']);
    $jumlah = floatval($_POST['jumlah_bayar']);
    $metode = trim($_POST['metode_pembayaran']);
    
    $sql = "INSERT INTO payment (Bookings_booking_id, jumlah_bayar, metode_pembayaran, status_payment, tgl_pembayaran) 
            VALUES (?, ?, ?, 'pending', NULL)";
    
    if (q($sql, [$booking_id, $jumlah, $metode])) {
        header('Location: index.php?success=Payment berhasil ditambahkan');
        exit;
    }
    
    header('Location: index.php?error=Gagal menambahkan payment');
    exit;
}

require_once '../includes/header.php';
?>

<div class="card">
    <h3 style="margin-bottom:20px;">Form Tambah Payment</h3>
    
    <?php if (empty($bookings)): ?>
        <div class="alert" style="background:#f8d7da; color:#721c24; padding:15px; border-radius:4px;">
            Semua booking sudah memiliki payment
        </div>
        <a href="index.php" class="btn btn-secondary">Kembali</a>
    <?php else: ?>
        <form method="POST" style="max-width:600px;">
            <div class="form-group">
                <label>Booking *</label>
                <select name="booking_id" class="form-control" id="booking" required>
                    <option value="">Pilih Booking</option>
                    <?php foreach ($bookings as $b): ?>
                        <option value="<?= $b['booking_id'] ?>" data-total="<?= $b['total_harga'] ?>">
                            #<?= $b['booking_id'] ?> - <?= htmlspecialchars($b['nama_pelanggan']) ?> - <?= date('d/m/Y', strtotime($b['tgl_booking'])) ?> - Rp <?= number_format($b['total_harga'], 0, ',', '.') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Jumlah Bayar *</label>
                <input type="number" name="jumlah_bayar" id="jumlah" class="form-control" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label>Metode Pembayaran *</label>
                <select name="metode_pembayaran" class="form-control" required>
                    <option value="">Pilih Metode</option>
                    <option value="Transfer Bank">Transfer Bank</option>
                    <option value="Cash">Cash</option>
                    <option value="E-Wallet">E-Wallet</option>
                    <option value="QRIS">QRIS</option>
                </select>
            </div>
            
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn">Simpan</button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
        
        <script>
        document.getElementById('booking').addEventListener('change', function() {
            var selected = this.options[this.selectedIndex];
            var total = selected.getAttribute('data-total');
            if (total) {
                document.getElementById('jumlah').value = total;
            }
        });
        </script>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>