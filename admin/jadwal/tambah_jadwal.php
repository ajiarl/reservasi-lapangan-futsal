<?php
require_once '../auth.php';

$page = 'jadwal';
$page_title = 'Tambah Jadwal';
$base_url = '..';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lapangan_id = intval($_POST['lapangan_id']);
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai_slot'];
    $jam_selesai = $_POST['jam_selesai_slot'];
    $harga = floatval($_POST['harga_perjam_slot']);
    
    // Check duplicate
    $check = fetchOne("SELECT jadwal_id FROM jadwallapangan WHERE Lapangan_lapangan_id=? AND hari=? AND jam_mulai_slot=? AND jam_selesai_slot=?", 
        [$lapangan_id, $hari, $jam_mulai, $jam_selesai]);
    
    if ($check) {
        header('Location: index.php?error=Jadwal sudah ada');
        exit;
    }
    
    $sql = "INSERT INTO jadwallapangan (Lapangan_lapangan_id, hari, jam_mulai_slot, jam_selesai_slot, harga_perjam_slot) VALUES (?, ?, ?, ?, ?)";
    
    if (q($sql, [$lapangan_id, $hari, $jam_mulai, $jam_selesai, $harga])) {
        header('Location: index.php?success=Jadwal berhasil ditambahkan');
        exit;
    }
    
    header('Location: index.php?error=Gagal menambahkan jadwal');
    exit;
}

$lapangan_list = fetchAll("SELECT lapangan_id, nama_lapangan FROM lapangan WHERE is_active=1 ORDER BY nama_lapangan");

require_once '../includes/header.php';
?>

<div class="card">
    <h3 style="margin-bottom:20px;">Form Tambah Jadwal</h3>
    
    <?php if (empty($lapangan_list)): ?>
        <div class="alert alert-danger">
            Tidak ada lapangan aktif. <a href="../lapangan/tambah_lapangan.php">Tambah lapangan</a> terlebih dahulu.
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Pilih Lapangan *</label>
            <select name="lapangan_id" class="form-control" required>
                <option value="">-- Pilih Lapangan --</option>
                <?php foreach ($lapangan_list as $l): ?>
                    <option value="<?= $l['lapangan_id'] ?>"><?= htmlspecialchars($l['nama_lapangan']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Hari *</label>
            <select name="hari" class="form-control" required>
                <option value="">-- Pilih Hari --</option>
                <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $h): ?>
                    <option value="<?= $h ?>"><?= $h ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Jam Mulai *</label>
            <input type="time" name="jam_mulai_slot" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Jam Selesai *</label>
            <input type="time" name="jam_selesai_slot" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Harga Per Jam (Rp) *</label>
            <input type="number" name="harga_perjam_slot" class="form-control" placeholder="150000" min="0" step="1000" required>
        </div>
        
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>