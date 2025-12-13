<?php
require_once '../auth.php';

$page = 'user';
$page_title = 'Data User';
$base_url = '..';

// Delete
if (isset($_GET['delete'])) {
    header('Location: hapus_user.php?id=' . intval($_GET['delete']));
    exit;
}

// Filter
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (nama_pelanggan LIKE ? OR email LIKE ? OR no_hp LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role) {
    $sql .= " AND role=?";
    $params[] = $role;
}

$sql .= " ORDER BY user_id DESC";
$users = fetchAll($sql, $params);

require_once '../includes/header.php';
?>

<div class="card">
    <form method="GET" style="display:grid; grid-template-columns:1fr 1fr auto; gap:15px; align-items:end;">
        <div class="form-group" style="margin:0;">
            <label>Cari User</label>
            <input type="text" name="search" class="form-control" placeholder="Nama, email, HP" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="form-group" style="margin:0;">
            <label>Role</label>
            <select name="role" class="form-control">
                <option value="">Semua</option>
                <option value="customer" <?= $role=='customer' ? 'selected' : '' ?>>Customer</option>
                <option value="admin" <?= $role=='admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($search || $role): ?>
                <a href="index.php" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="d-flex justify-between" style="margin-bottom: 15px;">
        <h3>Daftar User</h3>
        <a href="tambah_user.php" class="btn btn-primary">Tambah</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
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
                <tr><td colspan="7" class="text-center">Tidak ada data</td></tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>#<?= $u['user_id'] ?></td>
                        <td><?= htmlspecialchars($u['nama_pelanggan']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['no_hp']) ?></td>
                        <td><?= htmlspecialchars($u['alamat'] ?: '-') ?></td>
                        <td>
                            <span class="badge badge-<?= $u['role']=='admin' ? 'danger' : 'success' ?>">
                                <?= $u['role']=='admin' ? 'Admin' : 'Customer' ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($u['user_id'] != $admin_id): ?>
                                <a href="edit_user.php?id=<?= $u['user_id'] ?>" class="btn btn-edit btn-sm">Edit</a>
                                <a href="hapus_user.php?id=<?= $u['user_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                            <?php else: ?>
                                <span style="color:#999; font-size:13px;">Akun Aktif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>