<?php
/**
 * Create Jadwal Lapangan
 * Admin Panel - Tambah Slot Waktu Lapangan
 */

require_once '../auth.php';
require_once '../db.php';

$error = '';
$success = '';

// Fetch all active lapangan
$lapangan_list = fetchAll("SELECT lapangan_id, nama_lapangan FROM lapangan WHERE is_active = 1 ORDER BY nama_lapangan");

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $lapangan_id = intval($_POST['lapangan_id'] ?? 0);
    $hari = trim($_POST['hari'] ?? '');
    $jam_mulai_slot = trim($_POST['jam_mulai_slot'] ?? '');
    $jam_selesai_slot = trim($_POST['jam_selesai_slot'] ?? '');
    $harga_perjam_slot = floatval($_POST['harga_perjam_slot'] ?? 0);
    
    // Validation
    if ($lapangan_id == 0) {
        $error = 'Pilih lapangan terlebih dahulu!';
    } elseif (empty($hari)) {
        $error = 'Hari wajib dipilih!';
    } elseif (empty($jam_mulai_slot)) {
        $error = 'Jam Mulai wajib diisi!';
    } elseif (empty($jam_selesai_slot)) {
        $error = 'Jam Selesai wajib diisi!';
    } elseif ($harga_perjam_slot <= 0) {
        $error = 'Harga per jam harus lebih dari 0!';
    } elseif ($jam_mulai_slot >= $jam_selesai_slot) {
        $error = 'Jam Mulai harus lebih kecil dari Jam Selesai!';
    } else {
        // Check duplicate jadwal
        $check = fetchOne(
            "SELECT jadwal_id FROM jadwallapangan 
             WHERE Lapangan_lapangan_id = ? 
             AND hari = ? 
             AND jam_mulai_slot = ? 
             AND jam_selesai_slot = ?",
            [$lapangan_id, $hari, $jam_mulai_slot, $jam_selesai_slot]
        );
        
        if ($check) {
            $error = 'Jadwal ini sudah ada untuk lapangan tersebut!';
        } else {
            // Insert to database
            $sql = "INSERT INTO jadwallapangan (Lapangan_lapangan_id, hari, jam_mulai_slot, jam_selesai_slot, harga_perjam_slot) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $insert = q($sql, [$lapangan_id, $hari, $jam_mulai_slot, $jam_selesai_slot, $harga_perjam_slot]);
            
            if ($insert) {
                header('Location: index.php?success=Jadwal berhasil ditambahkan!');
                exit;
            } else {
                $error = 'Gagal menambahkan jadwal: ' . getError();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Jadwal - Admin Panel</title>
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
            <li><a href="index.php" class="active">Data Jadwal</a></li>
            <li><a href="../booking/index.php">Data Booking</a></li>
            <li><a href="../user/index.php">Data User</a></li>
            <li><a href="../payment/index.php">Data Payment</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Tambah Jadwal Lapangan</h1>
            <div class="header-right">
                <span>👤 <?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>

        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($lapangan_list)): ?>
                <div class="alert alert-warning">
                    Tidak ada lapangan aktif. <a href="../lapangan/create.php">Tambah lapangan</a> terlebih dahulu.
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Form Tambah Jadwal Slot Waktu</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="lapangan_id">Pilih Lapangan *</label>
                            <select id="lapangan_id" name="lapangan_id" class="form-control" required>
                                <option value="">-- Pilih Lapangan --</option>
                                <?php foreach ($lapangan_list as $lap): ?>
                                    <option value="<?php echo $lap['lapangan_id']; ?>"
                                        <?php echo (isset($_POST['lapangan_id']) && $_POST['lapangan_id'] == $lap['lapangan_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($lap['nama_lapangan']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="hari">Hari *</label>
                            <select id="hari" name="hari" class="form-control" required>
                                <option value="">-- Pilih Hari --</option>
                                <?php 
                                $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                foreach ($hari_list as $h): 
                                ?>
                                    <option value="<?php echo $h; ?>"
                                        <?php echo (isset($_POST['hari']) && $_POST['hari'] == $h) ? 'selected' : ''; ?>>
                                        <?php echo $h; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="jam_mulai_slot">Jam Mulai Slot *</label>
                            <input 
                                type="time" 
                                id="jam_mulai_slot" 
                                name="jam_mulai_slot" 
                                class="form-control" 
                                value="<?php echo isset($_POST['jam_mulai_slot']) ? htmlspecialchars($_POST['jam_mulai_slot']) : ''; ?>"
                                required
                            >
                            <small style="color: #666;">Format: HH:MM (contoh: 08:00)</small>
                        </div>

                        <div class="form-group">
                            <label for="jam_selesai_slot">Jam Selesai Slot *</label>
                            <input 
                                type="time" 
                                id="jam_selesai_slot" 
                                name="jam_selesai_slot" 
                                class="form-control" 
                                value="<?php echo isset($_POST['jam_selesai_slot']) ? htmlspecialchars($_POST['jam_selesai_slot']) : ''; ?>"
                                required
                            >
                            <small style="color: #666;">Format: HH:MM (contoh: 10:00)</small>
                        </div>

                        <div class="form-group">
                            <label for="harga_perjam_slot">Harga Per Jam (Rp) *</label>
                            <input 
                                type="number" 
                                id="harga_perjam_slot" 
                                name="harga_perjam_slot" 
                                class="form-control" 
                                placeholder="Contoh: 150000"
                                min="0"
                                step="1000"
                                value="<?php echo isset($_POST['harga_perjam_slot']) ? htmlspecialchars($_POST['harga_perjam_slot']) : ''; ?>"
                                required
                            >
                        </div>

                        <div class="d-flex gap-10">
                            <button type="submit" name="submit" class="btn btn-primary">
                                Simpan
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>