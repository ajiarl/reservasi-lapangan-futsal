<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $user = fetchOne("SELECT * FROM users WHERE email = ? AND role = 'admin'", [$email]);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['user_id'];
        $_SESSION['admin_name'] = $user['nama_pelanggan'];
        $_SESSION['last_activity'] = time();
        header('Location: dashboard.php');
        exit;
    }
    
    $error = 'Email atau password salah!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>Admin Panel</h2>
            <p style="text-align:center; color:#666; margin-bottom:30px;">Futsal Reservation</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</body>
</html>