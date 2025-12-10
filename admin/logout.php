<?php
/**
 * Logout Handler
 * Admin Panel - Futsal Reservation
 */

session_start();
session_unset();
session_destroy();

header('Location: index.php?logout=success');
exit;
?>