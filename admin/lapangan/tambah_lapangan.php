<?php
require_once '../auth.php';

$page = 'lapangan';
$page_title = 'Tambah Lapangan';
$base_url = '..';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_lapangan']);
    $jenis = trim($_POST['jenis_lapangan']);
    $fasilitas = trim($_POST['fasilitas']);
    
    $sql = "INSERT INTO lapangan (nama_lapangan, jenis_lapangan, fasilitas, is_active) VALUES (?, ?, ?, 1)";
    
    if (q($sql, [$nama, $jenis, $fasilitas])) {
        header('Location: index.php?success=Lapangan berhasil ditambahkan');
        exit;
    }
    
    header('Location: index.php?error=Gagal menambahkan lapangan');
    exit;
}

require_once '../includes/header.php';
?>

<div class="card">
    <h3 style="margin-bottom: 20px;">Form Tambah Lapangan</h3>
    <form method="POST" style="max-width: 600px;">
        <div class="form-group">
            <label>Nama Lapangan *</label>
            <input type="text" name="nama_lapangan" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Jenis Lapangan *</label>
            <input type="text" name="jenis_lapangan" class="form-control" placeholder="Contoh: Futsal Vinyl" required>
        </div>
        
        <div class="form-group">
            <label>Fasilitas *</label>
            <textarea name="fasilitas" class="form-control" rows="3" placeholder="Contoh: Lighting, Toilet, Kantin" required></textarea>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>