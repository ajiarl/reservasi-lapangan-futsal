<?php
/**
 * Create Lapangan
 * Admin Panel - Tambah Data Lapangan
 */

require_once '../auth.php';
require_once '../db.php';

$error = '';
$success = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $nama_lapangan = trim($_POST['nama_lapangan'] ?? '');
    $jenis_lapangan = trim($_POST['jenis_lapangan'] ?? '');
    $fasilitas = trim($_POST['fasilitas'] ?? '');
    
    // Validation
    if (empty($nama_lapangan)) {
        $error = 'Nama Lapangan wajib diisi!';
    } elseif (empty($jenis_lapangan)) {
        $error = 'Jenis Lapangan wajib diisi!';
    } elseif (empty($fasilitas)) {
        $error = 'Fasilitas wajib diisi!';
    } else {
        // Insert to database
        $sql = "INSERT INTO lapangan (nama_lapangan, jenis_lapangan, fasilitas, is_active) 
                VALUES (?, ?, ?, 1)";
        
        $insert = q($sql, [$nama_lapangan, $jenis_lapangan, $fasilitas]);
        
        if ($insert) {
            header('Location: index.php?success=Lapangan berhasil ditambahkan!');
            exit;
        } else {
            $error = 'Gagal menambahkan lapangan: ' . getError();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Lapangan - Admin Panel</title>
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
            <li><a href="index.php" class="active">Data Lapangan</a></li>
            <li><a href="../jadwal/index.php">Data Jadwal</a></li>
            <li><a href="../booking/index.php">Data Booking</a></li>
            <li><a href="../user/index.php">Data User</a></li>
            <li><a href="../payment/index.php">Data Payment</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Tambah Lapangan</h1>
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

            <div class="card">
                <div class="card-header">
                    <h3>Form Tambah Lapangan</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="nama_lapangan">Nama Lapangan *</label>
                            <input 
                                type="text" 
                                id="nama_lapangan" 
                                name="nama_lapangan" 
                                class="form-control" 
                                placeholder="Contoh: Lapangan A"
                                value="<?php echo isset($_POST['nama_lapangan']) ? htmlspecialchars($_POST['nama_lapangan']) : ''; ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="jenis_lapangan">Jenis Lapangan *</label>
                            <input 
                                type="text" 
                                id="jenis_lapangan" 
                                name="jenis_lapangan" 
                                class="form-control" 
                                placeholder="Contoh: Futsal Vinyl, Futsal Rumput Sintetis"
                                value="<?php echo isset($_POST['jenis_lapangan']) ? htmlspecialchars($_POST['jenis_lapangan']) : ''; ?>"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="fasilitas">Fasilitas *</label>
                            <textarea 
                                id="fasilitas" 
                                name="fasilitas" 
                                class="form-control" 
                                placeholder="Contoh: Lighting, Toilet, Kantin, Parkir Luas"
                                required
                            ><?php echo isset($_POST['fasilitas']) ? htmlspecialchars($_POST['fasilitas']) : ''; ?></textarea>
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