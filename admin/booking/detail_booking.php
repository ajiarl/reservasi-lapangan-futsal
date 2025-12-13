<?php
require_once '../auth.php';

$page = 'booking';
$page_title = 'Detail Booking';
$base_url = '..';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php?error=ID tidak valid');
    exit;
}

// Booking data
$booking = fetchOne("SELECT b.*, u.nama_pelanggan, u.no_hp, u.email, u.alamat
    FROM bookings b
    JOIN users u ON b.users_user_id = u.user_id
    WHERE b.booking_id=?", [$id]);

if (!$booking) {
    header('Location: index.php?error=Booking tidak ditemukan');
    exit;
}

// Booking details (slots)
$details = fetchAll("SELECT bd.*, j.hari, j.jam_mulai_slot, j.jam_selesai_slot, l.nama_lapangan, l.jenis_lapangan
    FROM booking_detail bd
    JOIN jadwallapangan j ON bd.jadwal_id = j.jadwal_id
    JOIN lapangan l ON j.Lapangan_lapangan_id = l.lapangan_id
    WHERE bd.booking_id=?
    ORDER BY j.hari, bd.jam_mulai", [$id]);

// Payment data
$payment = fetchOne("SELECT * FROM payment WHERE Bookings_booking_id=?", [$id]);

$statusBooking = $booking['status_booking'] ?: 'Pending';

$class = match ($statusBooking) {
    'Pending'      => 'warning',
    'Berlangsung'  => 'primary',
    'Selesai'      => 'success',
    'Batal'        => 'danger',
    default        => 'secondary'
};

require_once '../includes/header.php';
?>



<div class="card">
    <h3>Informasi Booking</h3>

    <div class="info-cards">

        <div class="info-card">
            <div class="label">Booking ID</div>
            <div class="value">#<?= $booking['booking_id'] ?></div>
        </div>

        <div class="info-card">
            <div class="label">Nama</div>
            <div class="value"><?= htmlspecialchars($booking['nama_pelanggan']) ?></div>
        </div>

        <div class="info-card">
            <div class="label">No HP</div>
            <div class="value"><?= htmlspecialchars($booking['no_hp']) ?></div>
        </div>

        <div class="info-card">
            <div class="label">Email</div>
            <div class="value"><?= htmlspecialchars($booking['email']) ?></div>
        </div>

        <div class="info-card">
            <div class="label">Alamat</div>
            <div class="value"><?= htmlspecialchars($booking['alamat']) ?></div>
        </div>

        <div class="info-card">
            <div class="label">Tanggal</div>
            <div class="value"><?= date('d F Y', strtotime($booking['tgl_booking'])) ?></div>
        </div>

        <div class="info-card">
            <div class="label">Total</div>
            <div class="value total-harga">
                Rp <?= number_format($booking['total_harga'],0,',','.') ?>
            </div>
        </div>

        <div class="info-card">
            <div class="label">Status</div>
            <div class="value badge badge-<?= $class ?>">
                <?= htmlspecialchars($statusBooking) ?>
            </div>
        </div>


    </div>
</div>




<div class="card">
    <h3>Detail Slot Booking</h3>
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
            <?php if (empty($details)): ?>
                <tr><td colspan="5" class="text-center">Tidak ada detail</td></tr>
            <?php else: ?>
                <?php foreach ($details as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['nama_lapangan']) ?></td>
                        <td><?= htmlspecialchars($d['jenis_lapangan']) ?></td>
                        <td><?= $d['hari'] ?></td>
                        <td><?= date('H:i', strtotime($d['jam_mulai'])) ?> - <?= date('H:i', strtotime($d['jam_selesai'])) ?></td>
                        <td>Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($payment): ?>
    <div class="card">
        <h3>Informasi Payment</h3>
        <p><strong>Payment ID:</strong> #<?= $payment['payment_id'] ?></p>
        <p><strong>Jumlah:</strong> Rp <?= number_format($payment['jumlah_bayar'], 0, ',', '.') ?></p>
        <p><strong>Metode:</strong> <?= htmlspecialchars($payment['metode_pembayaran']) ?></p>
        <p><strong>Status:</strong> 
            <?php
            $statusPay = $payment['status_payment'] ?: 'Pending';

            $paymentClass = match ($statusPay) {
                'Pending' => 'warning',
                'DP' => 'info',
                'Lunas' => 'success',
                default => 'secondary'
            };

            $class = $badge[$payment['status_payment']] ?? 'info';
            ?>
            <span class="badge badge-<?= $paymentClass ?>">
                <?= htmlspecialchars($statusPay) ?>
            </span>

        </p>
        <?php if ($payment['tgl_pembayaran']): ?>
            <p><strong>Tanggal Bayar:</strong> <?= date('d F Y H:i', strtotime($payment['tgl_pembayaran'])) ?></p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="alert alert-warning">Belum ada data payment</div>
<?php endif; ?>

<div style="margin-top:20px;">
    <a href="index.php" class="btn btn-secondary">← Kembali</a>
</div>

<pre>
<?php print_r($booking); ?>
</pre>


<?php require_once '../includes/footer.php'; ?>