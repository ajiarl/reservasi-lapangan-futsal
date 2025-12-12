<?php
require_once '../auth.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php?error=ID tidak valid');
    exit;
}

// Check if has booking_detail
$check = fetchOne("SELECT COUNT(*) as c FROM booking_detail WHERE jadwal_id=?", [$id]);

if ($check['c'] > 0) {
    header('Location: index.php?error=Jadwal sudah dibooking, tidak bisa dihapus');
    exit;
}

// Delete
if (q("DELETE FROM jadwallapangan WHERE jadwal_id=?", [$id])) {
    header('Location: index.php?success=Jadwal berhasil dihapus');
} else {
    header('Location: index.php?error=Gagal menghapus jadwal');
}
exit;