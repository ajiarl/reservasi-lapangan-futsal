<?php
session_start();
require_once 'db.php';

// Check login
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Session timeout (2 hours)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_destroy();
    header('Location: index.php?timeout=1');
    exit;
}

$_SESSION['last_activity'] = time();
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];
?>