<?php
require_once '../auth.php';

$page = 'booking';
$page_title = 'Tambah Booking';
$base_url = '..';

/* =========================================================
   AJAX: Ambil Jadwal berdasarkan Lapangan & Hari
   ========================================================= */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_jadwal') {
    $lapangan_id = intval($_GET['lapangan_id'] ?? 0);
    $hari = $_GET['hari'] ?? '';

    if ($lapangan_id && $hari) {
        $jadwal = fetchAll(
            "SELECT jadwal_id, jam_mulai_slot, jam_selesai_slot, harga_perjam_slot
             FROM jadwallapangan
             WHERE Lapangan_lapangan_id = ?
               AND hari = ?
             ORDER BY jam_mulai_slot",
            [$lapangan_id, $hari]
        );

        header('Content-Type: application/json');
        echo json_encode($jadwal);
    }
    exit;
}

/* =========================================================
   PROSES SUBMIT BOOKING
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id     = intval($_POST['user_id']);
    $tgl_booking = $_POST['tgl_booking'];
    $jadwal_ids  = $_POST['jadwal_id'] ?? [];
    $jam_mulai   = $_POST['jam_mulai'] ?? [];
    $jam_selesai = $_POST['jam_selesai'] ?? [];

    if (!$user_id || !$tgl_booking || empty($jadwal_ids)) {
        header('Location: index.php?error=Data booking tidak lengkap');
        exit;
    }

    global $conn;
    $conn->begin_transaction();

    try {
        //INSERT BOOKINGS
        q(
            "INSERT INTO bookings (users_user_id, tgl_booking, total_harga, status_booking)
             VALUES (?, ?, 0, 'Pending')",
            [$user_id, $tgl_booking]
        );

        $booking_id = $conn->insert_id;

        //INSERT BOOKING DETAIL
        //(total_harga dihitung oleh trigger)
        foreach ($jadwal_ids as $i => $jadwal_id) {
            q(
                "INSERT INTO booking_detail
                 (booking_id, jadwal_id, jam_mulai, jam_selesai, harga)
                 VALUES (?, ?, ?, ?, 
                    (SELECT harga_perjam_slot FROM jadwallapangan WHERE jadwal_id = ?)
                 )",
                [$booking_id, $jadwal_id, $jam_mulai[$i], $jam_selesai[$i], $jadwal_id]
            );
        }

        $conn->commit();
        header('Location: index.php?success=Booking berhasil ditambahkan');
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        header('Location: index.php?error=' . urlencode($e->getMessage()));
        exit;
    }
}

/* =========================================================
   DATA FORM
   ========================================================= */
$users = fetchAll(
    "SELECT user_id, nama_pelanggan, email
     FROM users
     WHERE role = 'customer'
     ORDER BY nama_pelanggan"
);

$lapangan = fetchAll(
    "SELECT lapangan_id, nama_lapangan
     FROM lapangan
     WHERE is_active = 1
     ORDER BY nama_lapangan"
);

require_once '../includes/header.php';
?>

<div class="card">
    <h3 style="margin-bottom:20px;">Form Tambah Booking</h3>

    <form method="POST" style="max-width:800px;">
        <div class="form-group">
            <label>Pilih Pelanggan *</label>
            <select name="user_id" class="form-control" required>
                <option value="">-- Pilih Pelanggan --</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['user_id'] ?>">
                        <?= htmlspecialchars($u['nama_pelanggan']) ?> (<?= htmlspecialchars($u['email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Tanggal Booking *</label>
            <input type="date" name="tgl_booking" class="form-control"
                   min="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label>Slot Waktu</label>
            <button type="button" class="btn btn-primary btn-sm" onclick="addSlot()">Tambah Slot</button>
        </div>

        <div id="slots"></div>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>

<script>
let counter = 0;
const lapangan = <?= json_encode($lapangan) ?>;
const hari = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];

function addSlot() {
    counter++;
    const div = document.createElement('div');
    div.id = 'slot-' + counter;
    div.style.cssText = 'background:#f9f9f9;padding:15px;margin-bottom:15px;border-radius:5px;border:1px solid #ddd;';
    div.innerHTML = `
        <div style="display:flex;justify-content:space-between;margin-bottom:10px;">
            <strong>Slot #${counter}</strong>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeSlot(${counter})">Hapus</button>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
            <select name="lapangan_${counter}" class="form-control" onchange="loadJadwal(${counter})" required>
                <option value="">Pilih Lapangan</option>
                ${lapangan.map(l => `<option value="${l.lapangan_id}">${l.nama_lapangan}</option>`).join('')}
            </select>

            <select name="hari_${counter}" class="form-control" onchange="loadJadwal(${counter})" required>
                <option value="">Pilih Hari</option>
                ${hari.map(h => `<option value="${h}">${h}</option>`).join('')}
            </select>
        </div>

        <select name="jadwal_id[]" id="jadwal_${counter}" class="form-control"
                onchange="updateJam(${counter})" required>
            <option value="">Pilih Jadwal</option>
        </select>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px;">
            <input type="time" name="jam_mulai[]" id="mulai_${counter}" class="form-control" required>
            <input type="time" name="jam_selesai[]" id="selesai_${counter}" class="form-control" required>
        </div>
    `;
    document.getElementById('slots').appendChild(div);
}

function removeSlot(id) {
    document.getElementById('slot-' + id)?.remove();
}

function loadJadwal(id) {
    const lap = document.querySelector(`select[name="lapangan_${id}"]`).value;
    const h   = document.querySelector(`select[name="hari_${id}"]`).value;
    const sel = document.getElementById('jadwal_' + id);
    if (!lap || !h) return;

    fetch(`?ajax=get_jadwal&lapangan_id=${lap}&hari=${h}`)
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">Pilih Jadwal</option>';
            data.forEach(j => {
                sel.innerHTML += `
                    <option value="${j.jadwal_id}"
                            data-mulai="${j.jam_mulai_slot}"
                            data-selesai="${j.jam_selesai_slot}">
                        ${j.jam_mulai_slot} - ${j.jam_selesai_slot}
                        (Rp ${parseInt(j.harga_perjam_slot).toLocaleString('id-ID')})
                    </option>`;
            });
        });
}

function updateJam(id) {
    const sel = document.getElementById('jadwal_' + id);
    const opt = sel.options[sel.selectedIndex];
    if (opt.value) {
        document.getElementById('mulai_' + id).value   = opt.dataset.mulai;
        document.getElementById('selesai_' + id).value = opt.dataset.selesai;
    }
}

window.onload = () => addSlot();
</script>

<?php require_once '../includes/footer.php'; ?>
