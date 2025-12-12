<?php
require_once '../auth.php';

$page = 'user';
$page_title = 'Tambah User';
$base_url = '..';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_pelanggan']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $role = $_POST['role'];
    
    // Check email
    $check = fetchOne("SELECT user_id FROM users WHERE email=?", [$email]);
    
    if ($check) {
        header('Location: index.php?error=Email sudah terdaftar');
        exit;
    }
    
    $sql = "INSERT INTO users (nama_pelanggan, email, password, no_hp, alamat, role) VALUES (?,?,?,?,?,?)";
    
    if (q($sql, [$nama, $email, $password, $no_hp, $alamat, $role])) {
        header('Location: index.php?success=User berhasil ditambahkan');
        exit;
    }
    
    header('Location: index.php?error=Gagal menambahkan user');
    exit;
}

require_once '../includes/header.php';
?>

<div class="card">
    <h3 style="margin-bottom:20px;">Form Tambah User</h3>
    <form method="POST" style="max-width:600px;">
        <div class="form-group">
            <label>Nama Lengkap *</label>
            <input type="text" name="nama_pelanggan" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" class="form-control" minlength="6" required>
            <small style="color:#666;">Minimal 6 karakter</small>
        </div>
        
        <div class="form-group">
            <label>No HP *</label>
            <input type="text" name="no_hp" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control" rows="3"></textarea>
        </div>
        
        <div class="form-group">
            <label>Role *</label>
            <select name="role" class="form-control" required>
                <option value="pelanggan">Pelanggan</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>