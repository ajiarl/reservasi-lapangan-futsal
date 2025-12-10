<?php
/**
 * List Users
 * Admin Panel - Data User Management
 */

require_once '../auth.php';
require_once '../db.php';

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Handle Delete User
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    
    // Check if user has bookings
    $check = fetchOne("SELECT COUNT(*) as total FROM bookings WHERE users_user_id = ?", [$user_id]);
    
    if ($check['total'] > 0) {
        header('Location: index.php?error=Tidak dapat menghapus user yang memiliki booking!');
        exit;
    }
    
    // Cannot delete self
    if ($user_id == $admin_id) {
        header('Location: index.php?error=Tidak dapat menghapus akun sendiri!');
        exit;
    }
    
    $delete = q("DELETE FROM users WHERE user_id = ?", [$user_id]);
    
    if ($delete) {
        header('Location: index.php?success=User berhasil dihapus!');
    } else {
        header('Location: index.php?error=Gagal menghapus user!');
    }
    exit;
}

// Fetch all users
$users = fetchAll("SELECT * FROM users ORDER BY user_id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - Admin Panel</title>
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
            <h1>Data User</h1>
            <div class="header-right">
                <span>👤 <?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>

        <div class="container">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="card" style="text-align: center;">
                    <div style="font-size: 36px; color: #9D4EDD; margin-bottom: 10px;">👥</div>
                    <h3 style="color: #5A189A; margin-bottom: 5px;">
                        <?php echo fetchOne("SELECT COUNT(*) as total FROM users")['total']; ?>
                    </h3>
                    <p style="color: #666; font-size: 14px;">Total User</p>
                </div>

                <div class="card" style="text-align: center;">
                    <div style="font-size: 36px; color: #28a745; margin-bottom: 10px;">👤</div>
                    <h3 style="color: #5A189A; margin-bottom: 5px;">
                        <?php echo fetchOne("SELECT COUNT(*) as total FROM users WHERE role = 'pelanggan'")['total']; ?>
                    </h3>
                    <p style="color: #666; font-size: 14px;">Pelanggan</p>
                </div>

                <div class="card" style="text-align: center;">
                    <div style="font-size: 36px; color: #ff9800; margin-bottom: 10px;">🔑</div>
                    <h3 style="color: #5A189A; margin-bottom: 5px;">
                        <?php echo fetchOne("SELECT COUNT(*) as total FROM users WHERE role = 'admin'")['total']; ?>
                    </h3>
                    <p style="color: #666; font-size: 14px;">Admin</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-between align-center">
                    <h3>Daftar User</h3>
                    <a href="create.php" class="btn btn-primary">+ Tambah User</a>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>No HP</th>
                                    <th>Alamat</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Belum ada data user</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>#<?php echo $user['user_id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['nama_pelanggan']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['no_hp']); ?></td>
                                            <td><?php echo htmlspecialchars($user['alamat'] ?? '-'); ?></td>
                                            <td>
                                                <?php if ($user['role'] == 'admin'): ?>
                                                    <span class="badge badge-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">Pelanggan</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['user_id'] != $admin_id): ?>
                                                    <a href="edit.php?id=<?php echo $user['user_id']; ?>" 
                                                       class="btn btn-primary btn-small">Edit</a>
                                                    <a href="index.php?delete=<?php echo $user['user_id']; ?>" 
                                                       class="btn btn-danger btn-small" 
                                                       onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</a>
                                                <?php else: ?>
                                                    <span style="color: #999; font-size: 13px;">Akun Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>