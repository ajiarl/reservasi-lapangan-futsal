<?php
require_once '../auth.php';

$page = 'booking';
$page_title = 'Edit Booking';
$base_url = '..';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php?error=ID tidak valid');
    exit;
}

$booking = fetchOne("SELECT * FROM bookings WHERE booking_id=?", [$id]);
if (!$booking) {
    header('Location: index.php?error=Booking tidak ditemukan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tgl_booking = $_POST['tgl_booking'];
    $status = $_POST['status_booking'];
    
    $sql = "UPDATE bookings SET tgl_booking=?, status_booking=? WHERE booking_id=?";
    
    if (q($sql, [$tgl_booking, $status, $id])) {
        //Jika booking Selesai maka payment Lunas
        if ($status == 'Selesai') {
            q("UPDATE payment 
               SET status_payment='Lunas', 
                   tgl_pembayaran=COALESCE(tgl_pembayaran, NOW()) 
               WHERE Bookings_booking_id=?", [$id]);
        }
        
        header('Location: index.php?success=Booking berhasil diupdate');
        exit;
    }
    
    header('Location: index.php?error=Gagal update booking');
    exit;
}

require_once '../includes/header.php';
?>

<div class="card">
    <h3 style="margin-bottom:20px;">Form Edit Booking</h3>
    <form method="POST" style="max-width:600px;">
        <div class="form-group">
            <label>Booking ID</label>
            <input type="text" class="form-control" value="#<?= $booking['booking_id'] ?>" disabled>
        </div>
        
        <div class="form-group">
            <label>Tanggal Booking *</label>
            <input type="date" name="tgl_booking" class="form-control" value="<?= $booking['tgl_booking'] ?>" required>
        </div>
        
        <div class="form-group">
            <label>Status Booking *</label>
            <select name="status_booking" class="form-control" required>
                <option value="Pending" <?= ($booking['status_booking']=='Pending' || !$booking['status_booking']) ? 'selected' : '' ?>>Pending</option>
                <option value="Berlangsung" <?= $booking['status_booking']=='Berlangsung' ? 'selected' : '' ?>>Berlangsung</option>
                <option value="Selesai" <?= $booking['status_booking']=='Selesai' ? 'selected' : '' ?>>Selesai</option>
                <option value="Batal" <?= $booking['status_booking']=='Batal' ? 'selected' : '' ?>>Batal</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Total Harga</label>
            <input type="text" class="form-control" value="Rp <?= number_format($booking['total_harga'], 0, ',', '.') ?>" disabled>
            <small style="color:#666;">Total harga dihitung otomatis dari detail booking</small>
        </div>
        
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>