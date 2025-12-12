<?php
require_once '../auth.php';

$page = 'lapangan';
$page_title = 'Data Lapangan';
$base_url = '..';

// Handle Delete
if (isset($_GET['delete'])) {
    header('Location: hapus_lapangan.php?id=' . intval($_GET['delete']));
    exit;
}

// Query
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT * FROM lapangan WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (nama_lapangan LIKE ? OR jenis_lapangan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status !== '') {
    $sql .= " AND is_active = ?";
    $params[] = $status;
}

$sql .= " ORDER BY lapangan_id DESC";
$lapangan = fetchAll($sql, $params);

require_once '../includes/header.php';
?>

<!-- Filter -->
<div class="card">
    <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 15px; align-items: end;">
        <div class="form-group" style="margin: 0;">
            <label>Cari Lapangan</label>
            <input type="text" name="search" placeholder="Nama atau jenis lapangan" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="form-group" style="margin: 0;">
            <label>Status</label>
            <select name="status">
                <option value="">Semua Status</option>
                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Aktif</option>
                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn">Filter</button>
            <?php if ($search || $status !== ''): ?>
                <a href="index.php" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Table -->
<div class="card">
    <div class="d-flex justify-between" style="margin-bottom: 15px;">
        <h3>Daftar Lapangan</h3>
        <a href="tambah_lapangan.php" class="btn">+ Tambah</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Jenis</th>
                <th>Fasilitas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($lapangan)): ?>
                <tr><td colspan="6" class="text-center">Tidak ada data</td></tr>
            <?php else: ?>
                <?php foreach ($lapangan as $l): ?>
                    <tr>
                        <td><?= $l['lapangan_id'] ?></td>
                        <td><?= htmlspecialchars($l['nama_lapangan']) ?></td>
                        <td><?= htmlspecialchars($l['jenis_lapangan']) ?></td>
                        <td><?= htmlspecialchars($l['fasilitas']) ?></td>
                        <td>
                            <span class="badge badge-<?= $l['is_active'] ? 'success' : 'danger' ?>">
                                <?= $l['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_lapangan.php?id=<?= $l['lapangan_id'] ?>" class="btn btn-sm">Edit</a>
                            <a href="hapus_lapangan.php?id=<?= $l['lapangan_id'] ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>