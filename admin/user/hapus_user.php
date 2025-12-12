<?php
require_once '../auth.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php?error=ID tidak valid');
    exit;
}

// Cek apakah user ini sedang login
if ($id == $admin_id) {
    header('Location: index.php?error=Tidak bisa menghapus akun sendiri');
    exit;
}

// Cek apakah punya booking
$check = fetchOne("SELECT COUNT(*) as total FROM bookings WHERE users_user_id=?", [$id]);

if ($check['total'] > 0) {
    header('Location: index.php?error=User memiliki booking, tidak bisa dihapus');
    exit;
}

// Hapus
if (q("DELETE FROM users WHERE user_id=?", [$id])) {
    header('Location: index.php?success=User berhasil dihapus');
} else {
    header('Location: index.php?error=Gagal menghapus user');
}
exit;