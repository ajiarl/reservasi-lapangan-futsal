<?php
require_once '../auth.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php?error=ID tidak valid');
    exit;
}

// Get payment data
$payment = fetchOne("SELECT * FROM payment WHERE payment_id=?", [$id]);

if (!$payment) {
    header('Location: index.php?error=Payment tidak ditemukan');
    exit;
}

// Hapus
if (q("DELETE FROM payment WHERE payment_id=?", [$id])) {
    header('Location: index.php?success=Payment berhasil dihapus');
} else {
    header('Location: index.php?error=Gagal menghapus payment');
}
exit;