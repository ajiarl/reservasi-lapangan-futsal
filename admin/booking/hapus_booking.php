<?php
require_once '../auth.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php?error=ID tidak valid');
    exit;
}

global $conn;
$conn->begin_transaction();

try {
    // pertama hapus booking_detail (foreign key)
    q("DELETE FROM booking_detail WHERE booking_id=?", [$id]);
    
    // hapus payment jika ada
    q("DELETE FROM payment WHERE Bookings_booking_id=?", [$id]);
    
    // hapus booking
    q("DELETE FROM bookings WHERE booking_id=?", [$id]);
    
    $conn->commit();
    header('Location: index.php?success=Booking berhasil dihapus');
    
} catch (Exception $e) {
    $conn->rollback();
    header('Location: index.php?error=Gagal menghapus booking');
}
exit;