<?php
/**
 * Admin Login Page
 * Futsal Reservation Admin Panel
 */

session_start();
require_once 'db.php';

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email) || empty($password)) {
        $error = 'Email dan Password wajib diisi!';
    } else {
        // Check user in database
        $sql = "SELECT user_id, nama_pelanggan, email, password, role 
                FROM users 
                WHERE email = ? 
                LIMIT 1";
        
        $user = fetchOne($sql, [$email]);
        
        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check if user is admin
                if ($user['role'] === 'admin') {
                    // Set session
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['user_id'];
                    $_SESSION['admin_name'] = $user['nama_pelanggan'];
                    $_SESSION['admin_email'] = $user['email'];
                    
                    // Redirect to dashboard
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Akses ditolak! Anda bukan admin.';
                }
            } else {
                $error = 'Email atau Password salah!';
            }
        } else {
            $error = 'Email atau Password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel Futsal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>Admin Panel</h2>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">Sistem Reservasi Futsal</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="admin@futsal.com"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="••••••••"
                        required
                    >
                </div>
                
                <button type="submit" name="login" class="btn btn-secondary">
                    Login
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 20px; font-size: 13px; color: #999;">
                © 2024 Futsal Reservation System
            </p>
        </div>
    </div>
</body>
</html>