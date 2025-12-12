<?php
require_once '../auth.php';

$page = 'lapangan';
$page_title = 'Edit Lapangan';
$base_url = '..';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php?error=ID tidak valid');
    exit;
}

$lapangan = fetchOne("SELECT * FROM lapangan WHERE lapangan_id = ?", [$id]);
if (!$lapangan) {
    header('Location: index.php?error=Lapangan tidak ditemukan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_lapangan']);
    $jenis = trim($_POST['jenis_lapangan']);
    $fasilitas = trim($_POST['fasilitas']);
    $is_active = intval($_POST['is_active']);
    
    $sql = "UPDATE lapangan SET nama_lapangan=?, jenis_lapangan=?, fasilitas=?, is_active=? WHERE lapangan_id=?";
    
    if (q($sql, [$nama, $jenis, $fasilitas, $is_active, $id])) {
        header('Location: index.php?success=Lapangan berhasil diupdate');
        exit;
    }
    
    header('Location: index.php?error=Gagal update lapangan');
    exit;
}

require_once '../includes/header.php';
?>

<div class="card">
    <h3 style="margin-bottom: 20px;">Form Edit Lapangan</h3>
    <form method="POST" style="max-width: 600px;">
        <div class="form-group">
            <label>Nama Lapangan *</label>
            <input type="text" name="nama_lapangan" class="form-control" value="<?= htmlspecialchars($lapangan['nama_lapangan']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Jenis Lapangan *</label>
            <input type="text" name="jenis_lapangan" class="form-control" value="<?= htmlspecialchars($lapangan['jenis_lapangan']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Fasilitas *</label>
            <textarea name="fasilitas" class="form-control" rows="3" required><?= htmlspecialchars($lapangan['fasilitas']) ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Status *</label>
            <select name="is_active" class="form-control" required>
                <option value="1" <?= $lapangan['is_active'] == 1 ? 'selected' : '' ?>>Aktif</option>
                <option value="0" <?= $lapangan['is_active'] == 0 ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn">Update</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>