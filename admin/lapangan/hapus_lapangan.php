<?php
require_once '../auth.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php?error=ID tidak valid');
    exit;
}

// Check if has jadwal
$check = fetchOne("SELECT COUNT(*) as total FROM jadwallapangan WHERE Lapangan_lapangan_id = ?", [$id]);

if ($check['total'] > 0) {
    header('Location: index.php?error=Lapangan memiliki jadwal, tidak bisa dihapus');
    exit;
}

// Delete
if (q("DELETE FROM lapangan WHERE lapangan_id = ?", [$id])) {
    header('Location: index.php?success=Lapangan berhasil dihapus');
} else {
    header('Location: index.php?error=Gagal menghapus lapangan');
}
exit;