<?php
/**
 * Create Booking Manual
 * Admin Panel - Booking Manual dengan Error Handling Trigger
 */

require_once '../auth.php';
require_once '../db.php';

$error = '';
$success = '';

// Fetch all users (pelanggan) - FIX: Remove WHERE clause if no results
$users = fetchAll("SELECT user_id, nama_pelanggan, email, role FROM users ORDER BY role DESC, nama_pelanggan");

// Fetch all active lapangan
$lapangan_list = fetchAll("SELECT lapangan_id, nama_lapangan FROM lapangan WHERE is_active = 1 ORDER BY nama_lapangan");

// For AJAX: Get jadwal by lapangan and hari
if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_jadwal') {
    $lapangan_id = intval($_GET['lapangan_id'] ?? 0);
    $hari = trim($_GET['hari'] ?? '');
    
    if ($lapangan_id > 0 && !empty($hari)) {
        $jadwal = fetchAll("
            SELECT jadwal_id, jam_mulai_slot, jam_selesai_slot, harga_perjam_slot
            FROM jadwallapangan
            WHERE Lapangan_lapangan_id = ? AND hari = ?
            ORDER BY jam_mulai_slot
        ", [$lapangan_id, $hari]);
        
        header('Content-Type: application/json');
        echo json_encode($jadwal);
    }
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $user_id = intval($_POST['user_id'] ?? 0);
    $tgl_booking = trim($_POST['tgl_booking'] ?? '');
    $jadwal_ids = $_POST['jadwal_id'] ?? [];
    $jam_mulai = $_POST['jam_mulai'] ?? [];
    $jam_selesai = $_POST['jam_selesai'] ?? [];
    
    // Validation
    if ($user_id == 0) {
        $error = 'Pilih pelanggan terlebih dahulu!';
    } elseif (empty($tgl_booking)) {
        $error = 'Tanggal booking wajib diisi!';
    } elseif (empty($jadwal_ids)) {
        $error = 'Pilih minimal 1 slot jadwal!';
    } else {
        // START TRANSACTION
        global $conn;
        $conn->begin_transaction();
        
        try {
            // 1. Insert to bookings table (total_harga will be auto-updated by trigger)
            $sql_booking = "INSERT INTO bookings (users_user_id, tgl_booking, total_harga, status_booking) 
                           VALUES (?, ?, 0, 'pending')";
            
            $insert_booking = q($sql_booking, [$user_id, $tgl_booking]);
            
            if (!$insert_booking) {
                throw new Exception('Gagal insert booking: ' . getError());
            }
            
            // Get booking_id
            $booking_id = lastInsertId();
            
            // 2. Insert to booking_detail (THIS IS WHERE TRIGGER MIGHT FAIL)
            foreach ($jadwal_ids as $index => $jadwal_id) {
                $jadwal_id = intval($jadwal_id);
                $jam_mulai_val = $jam_mulai[$index] ?? '';
                $jam_selesai_val = $jam_selesai[$index] ?? '';
                
                // Get harga from jadwal
                $jadwal_data = fetchOne("SELECT harga_perjam_slot FROM jadwallapangan WHERE jadwal_id = ?", [$jadwal_id]);
                
                if (!$jadwal_data) {
                    throw new Exception("Jadwal ID $jadwal_id tidak ditemukan!");
                }
                
                $harga = $jadwal_data['harga_perjam_slot'];
                
                // Insert booking_detail - THIS CAN FAIL IF TRIGGER DETECTS DOUBLE BOOKING
                $sql_detail = "INSERT INTO booking_detail (booking_id, jadwal_id, jam_mulai, jam_selesai, harga) 
                              VALUES (?, ?, ?, ?, ?)";
                
                $insert_detail = q($sql_detail, [$booking_id, $jadwal_id, $jam_mulai_val, $jam_selesai_val, $harga]);
                
                if (!$insert_detail) {
                    // Check if error is from trigger (duplicate booking)
                    $error_msg = getError();
                    
                    if (stripos($error_msg, 'slot sudah dibooking') !== false || 
                        stripos($error_msg, 'double') !== false ||
                        stripos($error_msg, 'duplicate') !== false) {
                        throw new Exception("Slot waktu sudah dibooking oleh pelanggan lain! Silakan pilih slot lain.");
                    } else {
                        throw new Exception("Gagal insert booking detail: $error_msg");
                    }
                }
            }
            
            // 3. Commit transaction
            $conn->commit();
            
            // Redirect to success
            header('Location: index.php?success=Booking manual berhasil dibuat! (ID: #' . $booking_id . ')');
            exit;
            
        } catch (Exception $e) {
            // ROLLBACK on any error
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Manual - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .slot-item {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }
        .slot-item h4 {
            color: #5A189A;
            margin-bottom: 10px;
        }
    </style>
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
            <li><a href="../jadwal/index.php">Data Jadwal</a></li>
            <li><a href="index.php" class="active">Data Booking</a></li>
            <li><a href="../user/index.php">Data User</a></li>
            <li><a href="../payment/index.php">Data Payment</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Booking Manual (Admin)</h1>
            <div class="header-right">
                <span>👤 <?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>

        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong>Error!</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Form Booking Manual</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="bookingForm">
                        <!-- Select User -->
                        <div class="form-group">
                            <label for="user_id">Pilih Pelanggan *</label>
                            <select id="user_id" name="user_id" class="form-control" required>
                                <option value="">-- Pilih Pelanggan --</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['user_id']; ?>">
                                        <?php 
                                        echo htmlspecialchars($user['nama_pelanggan'] . ' (' . $user['email'] . ')');
                                        if ($user['role'] == 'admin') echo ' - [ADMIN]';
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($users)): ?>
                                <small style="color: #dc3545;">Belum ada user terdaftar. <a href="../user/create.php">Tambah user</a> terlebih dahulu.</small>
                            <?php endif; ?>
                        </div>

                        <!-- Tanggal Booking -->
                        <div class="form-group">
                            <label for="tgl_booking">Tanggal Booking *</label>
                            <input 
                                type="date" 
                                id="tgl_booking" 
                                name="tgl_booking" 
                                class="form-control" 
                                min="<?php echo date('Y-m-d'); ?>"
                                required
                            >
                        </div>

                        <!-- Dynamic Slot Selection -->
                        <div class="form-group">
                            <label>Pilih Slot Waktu *</label>
                            <button type="button" class="btn btn-primary btn-small" onclick="addSlot()">+ Tambah Slot</button>
                        </div>

                        <div id="slotsContainer"></div>

                        <div class="d-flex gap-10 mt-20">
                            <button type="submit" name="submit" class="btn btn-primary">
                                Simpan Booking
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

    <script>
        let slotCounter = 0;
        const lapanganList = <?php echo json_encode($lapangan_list); ?>;
        const hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        function addSlot() {
            slotCounter++;
            const container = document.getElementById('slotsContainer');
            
            const slotDiv = document.createElement('div');
            slotDiv.className = 'slot-item';
            slotDiv.id = 'slot-' + slotCounter;
            
            slotDiv.innerHTML = `
                <div class="d-flex justify-between align-center mb-10">
                    <h4>Slot #${slotCounter}</h4>
                    <button type="button" class="btn btn-danger btn-small" onclick="removeSlot(${slotCounter})">Hapus</button>
                </div>
                
                <div class="form-group">
                    <label>Pilih Lapangan</label>
                    <select name="lapangan_${slotCounter}" class="form-control" onchange="updateHari(${slotCounter})" required>
                        <option value="">-- Pilih Lapangan --</option>
                        ${lapanganList.map(l => `<option value="${l.lapangan_id}">${l.nama_lapangan}</option>`).join('')}
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Pilih Hari</label>
                    <select name="hari_${slotCounter}" class="form-control" onchange="loadJadwal(${slotCounter})" required>
                        <option value="">-- Pilih Hari --</option>
                        ${hariList.map(h => `<option value="${h}">${h}</option>`).join('')}
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Pilih Jadwal Slot</label>
                    <select name="jadwal_id[]" id="jadwal_${slotCounter}" class="form-control" onchange="updateJam(${slotCounter})" required>
                        <option value="">-- Pilih Jadwal --</option>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="form-group">
                        <label>Jam Mulai</label>
                        <input type="time" name="jam_mulai[]" id="jam_mulai_${slotCounter}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Selesai</label>
                        <input type="time" name="jam_selesai[]" id="jam_selesai_${slotCounter}" class="form-control" required>
                    </div>
                </div>
            `;
            
            container.appendChild(slotDiv);
        }

        function removeSlot(id) {
            const slot = document.getElementById('slot-' + id);
            if (slot) slot.remove();
        }

        function updateHari(slotId) {
            const jadwalSelect = document.getElementById('jadwal_' + slotId);
            jadwalSelect.innerHTML = '<option value="">-- Pilih Jadwal --</option>';
        }

        function loadJadwal(slotId) {
            const lapanganSelect = document.querySelector(`select[name="lapangan_${slotId}"]`);
            const hariSelect = document.querySelector(`select[name="hari_${slotId}"]`);
            const jadwalSelect = document.getElementById('jadwal_' + slotId);
            
            const lapanganId = lapanganSelect.value;
            const hari = hariSelect.value;
            
            if (!lapanganId || !hari) return;
            
            fetch(`?ajax=get_jadwal&lapangan_id=${lapanganId}&hari=${hari}`)
                .then(res => res.json())
                .then(data => {
                    jadwalSelect.innerHTML = '<option value="">-- Pilih Jadwal --</option>';
                    data.forEach(j => {
                        jadwalSelect.innerHTML += `<option value="${j.jadwal_id}" data-mulai="${j.jam_mulai_slot}" data-selesai="${j.jam_selesai_slot}">${j.jam_mulai_slot} - ${j.jam_selesai_slot} (Rp ${parseInt(j.harga_perjam_slot).toLocaleString('id-ID')})</option>`;
                    });
                });
        }

        function updateJam(slotId) {
            const jadwalSelect = document.getElementById('jadwal_' + slotId);
            const selectedOption = jadwalSelect.options[jadwalSelect.selectedIndex];
            
            if (selectedOption.value) {
                document.getElementById('jam_mulai_' + slotId).value = selectedOption.dataset.mulai;
                document.getElementById('jam_selesai_' + slotId).value = selectedOption.dataset.selesai;
            }
        }

        // Add first slot on load
        window.onload = function() {
            addSlot();
        };
    </script>
</body>
</html>