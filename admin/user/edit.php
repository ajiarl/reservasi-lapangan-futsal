<?php
/**
 * Edit User
 * Admin Panel - Edit User
 */

require_once '../auth.php';
require_once '../db.php';

$error = '';
$success = '';

// Get user_id from URL
$user_id = intval($_GET['id'] ?? 0);

if ($user_id == 0) {
    header('Location: index.php?error=ID User tidak valid!');
    exit;
}

// Fetch user data
$user = fetchOne("SELECT * FROM users WHERE user_id = ?", [$user_id]);

if (!$user) {
    header('Location: index.php?error=User tidak ditemukan!');
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $nama_pelanggan = trim($_POST['nama_pelanggan'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $role = trim($_POST['role'] ?? 'pelanggan');
    
    // Validation
    if (empty($nama_pelanggan)) {
        $error = 'Nama wajib diisi!';
    } elseif (empty($email)) {
        $error = 'Email wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (empty($no_hp)) {
        $error = 'No HP wajib diisi!';
    } else {
        // Check if email already exists (except current user)
        $check_email = fetchOne("SELECT user_id FROM users WHERE email = ? AND user_id != ?", [$email, $user_id]);
        
        if ($check_email) {
            $error = 'Email sudah digunakan oleh user lain!';
        } else {
            // Update user data
            if (!empty($password)) {
                // Validate password if changed
                if (strlen($password) < 6) {
                    $error = 'Password minimal 6 karakter!';
                } elseif ($password !== $confirm_password) {
                    $error = 'Password dan Konfirmasi Password tidak cocok!';
                } else {
                    // Update with new password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users 
                            SET nama_pelanggan = ?, email = ?, password = ?, no_hp = ?, alamat = ?, role = ? 
                            WHERE user_id = ?";
                    $update = q($sql, [$nama_pelanggan, $email, $hashed_password, $no_hp, $alamat, $role, $user_id]);
                }
            } else {
                // Update without changing password
                $sql = "UPDATE users 
                        SET nama_pelanggan = ?, email = ?, no_hp = ?, alamat = ?, role = ? 
                        WHERE user_id = ?";
                $update = q($sql, [$nama_pelanggan, $email, $no_hp, $alamat, $role, $user_id]);
            }
            
            if (!isset($update)) {
                // Password validation failed, error already set
            } elseif ($update) {
                header('Location: index.php?success=User berhasil diupdate!');
                exit;
            } else {
                $error = 'Gagal mengupdate user: ' . getError();
            }
        }
    }
} else {
    // Load existing data
    $_POST['nama_pelanggan'] = $user['nama_pelanggan'];
    $_POST['email'] = $user['email'];
    $_POST['no_hp'] = $user['no_hp'];
    $_POST['alamat'] = $user['alamat'];
    $_POST['role'] = $user['role'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
            <p style="font-size: 13px; opacity: 0.8;">Futsal Reservation</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../dashboard.php">Dashboard</a></li>
            <li><a href="../lapangan/index.php">Data Lapangan</a></li>
            <li><a href="../jadwal/index.php">Data Jadwal</a></li>
            <li><a href="../booking/index.php">Data Booking</a></li>
            <li><a href="index.php" class="active">Data User</a></li>
            <li><a href="../payment/index.php">Data Payment</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Edit User</h1>
            <div class="header-right">
                <span>👤 <?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>

        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Form Edit User</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="nama_pelanggan">Nama Lengkap *</label>
                            <input 
                                type="text" 
                                id="nama_pelanggan" 
                                name="nama_pelanggan" 
                                class="form-control" 
                                value="<?php echo htmlspecialchars($_POST['nama_pelanggan']); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control" 
                                value="<?php echo htmlspecialchars($_POST['email']); ?>"
                                required
                            >
                        </div>

                        <div class="alert alert-info">
                            <strong>Info:</strong> Kosongkan password jika tidak ingin mengubahnya.
                        </div>

                        <div class="form-group">
                            <label for="password">Password Baru (Opsional)</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control" 
                                placeholder="Minimal 6 karakter (kosongkan jika tidak diubah)"
                            >
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-control" 
                                placeholder="Ketik ulang password baru"
                            >
                        </div>

                        <div class="form-group">
                            <label for="no_hp">No HP *</label>
                            <input 
                                type="text" 
                                id="no_hp" 
                                name="no_hp" 
                                class="form-control" 
                                value="<?php echo htmlspecialchars($_POST['no_hp']); ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="alamat">Alamat</label>
                            <textarea 
                                id="alamat" 
                                name="alamat" 
                                class="form-control" 
                            ><?php echo htmlspecialchars($_POST['alamat']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="role">Role *</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="pelanggan" <?php echo ($_POST['role'] == 'pelanggan') ? 'selected' : ''; ?>>Pelanggan</option>
                                <option value="admin" <?php echo ($_POST['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>

                        <div class="d-flex gap-10">
                            <button type="submit" name="submit" class="btn btn-primary">
                                Update
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>