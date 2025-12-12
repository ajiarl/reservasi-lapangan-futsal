<?php
require_once '../auth.php';

$page = 'jadwal';
$page_title = 'Edit Jadwal';
$base_url = '..';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php?error=ID tidak valid');
    exit;
}

$jadwal = fetchOne("SELECT * FROM jadwallapangan WHERE jadwal_id=?", [$id]);
if (!$jadwal) {
    header('Location: index.php?error=Jadwal tidak ditemukan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lapangan_id = intval($_POST['lapangan_id']);
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai_slot'];
    $jam_selesai = $_POST['jam_selesai_slot'];
    $harga = floatval($_POST['harga_perjam_slot']);
    
    // Check duplicate (except current)
    $check = fetchOne("SELECT jadwal_id FROM jadwallapangan WHERE Lapangan_lapangan_id=? AND hari=? AND jam_mulai_slot=? AND jam_selesai_slot=? AND jadwal_id!=?", 
        [$lapangan_id, $hari, $jam_mulai, $jam_selesai, $id]);
    
    if ($check) {
        header('Location: index.php?error=Jadwal sudah ada');
        exit;
    }
    
    $sql = "UPDATE jadwallapangan SET Lapangan_lapangan_id=?, hari=?, jam_mulai_slot=?, jam_selesai_slot=?, harga_perjam_slot=? WHERE jadwal_id=?";
    
    if (q($sql, [$lapangan_id, $hari, $jam_mulai, $jam_selesai, $harga, $id])) {
        header('Location: index.php?success=Jadwal berhasil diupdate');
        exit;
    }
    
    header('Location: index.php?error=Gagal update jadwal');
    exit;
}

$lapangan_list = fetchAll("SELECT lapangan_id, nama_lapangan FROM lapangan WHERE is_active=1 ORDER BY nama_lapangan");

require_once '../includes/header.php';
?>

<div class="card">
    <h3 style="margin-bottom:20px;">Form Edit Jadwal</h3>
    <form method="POST" style="max-width:600px;">
        <div class="form-group">
            <label>Pilih Lapangan *</label>
            <select name="lapangan_id" class="form-control" required>
                <option value="">-- Pilih Lapangan --</option>
                <?php foreach ($lapangan_list as $l): ?>
                    <option value="<?= $l['lapangan_id'] ?>" <?= $jadwal['Lapangan_lapangan_id'] == $l['lapangan_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($l['nama_lapangan']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Hari *</label>
            <select name="hari" class="form-control" required>
                <option value="">-- Pilih Hari --</option>
                <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $h): ?>
                    <option value="<?= $h ?>" <?= $jadwal['hari'] == $h ? 'selected' : '' ?>><?= $h ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label>Jam Mulai *</label>
            <input type="time" name="jam_mulai_slot" class="form-control" value="<?= $jadwal['jam_mulai_slot'] ?>" required>
        </div>
        
        <div class="form-group">
            <label>Jam Selesai *</label>
            <input type="time" name="jam_selesai_slot" class="form-control" value="<?= $jadwal['jam_selesai_slot'] ?>" required>
        </div>
        
        <div class="form-group">
            <label>Harga Per Jam (Rp) *</label>
            <input type="number" name="harga_perjam_slot" class="form-control" value="<?= $jadwal['harga_perjam_slot'] ?>" min="0" step="1000" required>
        </div>
        
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn">Update</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>