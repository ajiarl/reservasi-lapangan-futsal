<?php
require_once '../auth.php';

$page = 'user';
$page_title = 'Edit User';
$base_url = '..';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php?error=ID tidak valid');
    exit;
}

$user = fetchOne("SELECT * FROM users WHERE user_id=?", [$id]);
if (!$user) {
    header('Location: index.php?error=User tidak ditemukan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_pelanggan']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $role = $_POST['role'];
    $password = trim($_POST['password']);
    
    // Check email (kecuali email sendiri)
    $check = fetchOne("SELECT user_id FROM users WHERE email=? AND user_id!=?", [$email, $id]);
    
    if ($check) {
        header('Location: index.php?error=Email sudah digunakan user lain');
        exit;
    }
    
    // Update dengan atau tanpa password
    if ($password) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET nama_pelanggan=?, email=?, password=?, no_hp=?, alamat=?, role=? WHERE user_id=?";
        $result = q($sql, [$nama, $email, $hashed, $no_hp, $alamat, $role, $id]);
    } else {
        $sql = "UPDATE users SET nama_pelanggan=?, email=?, no_hp=?, alamat=?, role=? WHERE user_id=?";
        $result = q($sql, [$nama, $email, $no_hp, $alamat, $role, $id]);
    }
    
    if ($result) {
        header('Location: index.php?success=User berhasil diupdate');
        exit;
    }
    
    header('Location: index.php?error=Gagal update user');
    exit;
}

require_once '../includes/header.php';
?>

<div class="card">
    <h3 style="margin-bottom:20px;">Form Edit User</h3>
    <form method="POST" style="max-width:600px;">
        <div class="form-group">
            <label>Nama Lengkap *</label>
            <input type="text" name="nama_pelanggan" class="form-control" value="<?= htmlspecialchars($user['nama_pelanggan']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        
        <div class="alert" style="background:#f0f0f0; padding:10px; margin-bottom:15px; border-radius:4px;">
            <small>Kosongkan password jika tidak ingin mengubahnya</small>
        </div>
        
        <div class="form-group">
            <label>Password Baru (opsional)</label>
            <input type="password" name="password" class="form-control" minlength="6" placeholder="Kosongkan jika tidak diubah">
        </div>
        
        <div class="form-group">
            <label>No HP *</label>
            <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($user['no_hp']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($user['alamat']) ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Role *</label>
            <select name="role" class="form-control" required>
                <option value="pelanggan" <?= $user['role']=='pelanggan' ? 'selected' : '' ?>>Pelanggan</option>
                <option value="admin" <?= $user['role']=='admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn">Update</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>