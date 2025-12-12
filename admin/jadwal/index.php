<?php
require_once '../auth.php';

$page = 'jadwal';
$page_title = 'Data Jadwal';
$base_url = '..';

// Delete
if (isset($_GET['delete'])) {
    header('Location: hapus_jadwal.php?id=' . intval($_GET['delete']));
    exit;
}

// Filter
$search = $_GET['search'] ?? '';
$lapangan = $_GET['lapangan'] ?? '';
$hari = $_GET['hari'] ?? '';

$sql = "SELECT j.*, l.nama_lapangan, l.jenis_lapangan
        FROM jadwallapangan j
        JOIN lapangan l ON j.Lapangan_lapangan_id = l.lapangan_id
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (l.nama_lapangan LIKE ? OR l.jenis_lapangan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($lapangan) {
    $sql .= " AND j.Lapangan_lapangan_id=?";
    $params[] = $lapangan;
}

if ($hari) {
    $sql .= " AND j.hari=?";
    $params[] = $hari;
}

$sql .= " ORDER BY l.nama_lapangan, FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), j.jam_mulai_slot";

$jadwal = fetchAll($sql, $params);
$lapangan_list = fetchAll("SELECT lapangan_id, nama_lapangan FROM lapangan WHERE is_active=1 ORDER BY nama_lapangan");

require_once '../includes/header.php';
?>

<div class="card">
    <form method="GET" style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:15px; align-items:end;">
        <div class="form-group" style="margin:0;">
            <label>Cari Lapangan</label>
            <input type="text" name="search" class="form-control" placeholder="Nama atau jenis" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="form-group" style="margin:0;">
            <label>Lapangan</label>
            <select name="lapangan" class="form-control">
                <option value="">Semua</option>
                <?php foreach ($lapangan_list as $l): ?>
                    <option value="<?= $l['lapangan_id'] ?>" <?= $lapangan == $l['lapangan_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($l['nama_lapangan']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label>Hari</label>
            <select name="hari" class="form-control">
                <option value="">Semua</option>
                <?php foreach (['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $h): ?>
                    <option value="<?= $h ?>" <?= $hari == $h ? 'selected' : '' ?>><?= $h ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex; gap:10px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($search || $lapangan || $hari): ?>
                <a href="index.php" class="btn btn-secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="d-flex justify-between">
        <h3>Daftar Jadwal</h3>
        <a href="tambah_jadwal.php" class="btn btn-primary">Tambah</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Lapangan</th>
                <th>Jenis</th>
                <th>Hari</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Harga/Jam</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($jadwal)): ?>
                <tr><td colspan="8" class="text-center">Tidak ada data</td></tr>
            <?php else: ?>
                <?php foreach ($jadwal as $j): ?>
                    <tr>
                        <td><?= $j['jadwal_id'] ?></td>
                        <td><?= htmlspecialchars($j['nama_lapangan']) ?></td>
                        <td><?= htmlspecialchars($j['jenis_lapangan']) ?></td>
                        <td><?= $j['hari'] ?></td>
                        <td><?= date('H:i', strtotime($j['jam_mulai_slot'])) ?></td>
                        <td><?= date('H:i', strtotime($j['jam_selesai_slot'])) ?></td>
                        <td>Rp <?= number_format($j['harga_perjam_slot'], 0, ',', '.') ?></td>
                        <td>
                            <a href="edit_jadwal.php?id=<?= $j['jadwal_id'] ?>" class="btn btn-sm">Edit</a>
                            <a href="hapus_jadwal.php?id=<?= $j['jadwal_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>