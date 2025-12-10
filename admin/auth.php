<?php
/**
 * Authentication Check
 * Include this file at the top of every admin page (except login)
 * Futsal Reservation Admin Panel
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Not logged in, redirect to login page
    header('Location: index.php');
    exit;
}

// Optional: Check if session has expired (e.g., after 2 hours of inactivity)
$session_timeout = 7200; // 2 hours in seconds

if (isset($_SESSION['last_activity'])) {
    $elapsed_time = time() - $_SESSION['last_activity'];
    
    if ($elapsed_time > $session_timeout) {
        // Session expired
        session_unset();
        session_destroy();
        header('Location: index.php?timeout=1');
        exit;
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Make admin info available globally
$admin_id = $_SESSION['admin_id'] ?? 0;
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? '';

/**
 * Logout Function
 * Call this to logout user
 */
function logout() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

/**
 * Check if current page is active (for navigation highlighting)
 * 
 * @param string $page Page name to check
 * @return string 'active' class if current page matches
 */
function isActive($page) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page === $page) ? 'active' : '';
}
?>