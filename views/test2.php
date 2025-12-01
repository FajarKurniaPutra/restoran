<?php
require_once '../models/MejaModel.php';
$model = new MejaModel();

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'tambah_meja') {
            $nomor_meja = $_POST['nomor_meja'];
            $kapasitas = $_POST['kapasitas'];
            $model->create($nomor_meja, $kapasitas);
        }
        elseif ($action === 'edit_meja') {
            $id_meja = $_POST['id_meja'];
            $nomor_meja = $_POST['nomor_meja'];
            $kapasitas = $_POST['kapasitas'];
            $model->update($id_meja, $nomor_meja, $kapasitas);
        }
        elseif ($action === 'reservasi') {
            $id_pelanggan = $_POST['id_pelanggan'];
            $id_meja = $_POST['id_meja'];
            $tanggal_reservasi = $_POST['tanggal_reservasi'];
            $jam_mulai = $_POST['jam_mulai'];
            $jam_selesai = $_POST['jam_selesai'];
            $model->reservasiMeja($id_pelanggan, $id_meja, $tanggal_reservasi, $jam_mulai, $jam_selesai);
        }
    }
    header("Location: test2.php");
    exit;
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id_meja = $_GET['id_meja'];
    $model->delete($id_meja);
    header("Location: test2.php");
    exit;
}

// Get data
$dataMeja = $model->getStatusMeja();
$dataPelanggan = $model->getPelanggan();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Meja</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        .status-kosong { background-color: #d4edda; }
        .status-terisi { background-color: #f8d7da; }
        .badge-kosong { background-color: #28a745; }
        .badge-terisi { background-color: #dc3545; }
        .btn-reservasi { margin-left: 5px; }
        .card-meja { 
            border: 2px solid #dee2e6; 
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .card-meja:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Manajemen Meja</h2>
        
        <!-- Tombol Tambah Meja -->
        <button class="btn btn-success mb-4" onclick="showTambahMeja()">Tambah Meja</button>
        
        <!-- Grid Meja -->
        <div class="row">
            <?php foreach ($dataMeja as $row) { 
                $status_class = $row['status_meja'] === 'terisi' ? 'status-terisi' : 'status-kosong';
                $badge_class = $row['status_meja'] === 'terisi' ? 'badge-terisi' : 'badge-kosong';
                $status_text = $row['status_meja'] === 'terisi' ? 'TERISI' : 'KOSONG';
            ?>
            <div class="col-md-4 mb-3">
                <div class="card card-meja <?php echo $status_class; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="card-title">Meja <?php echo $row['nomor_meja']; ?></h5>
                            <span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                        </div>
                        <p class="card-text">
                            <strong>Kapasitas:</strong> <?php echo $row['kapasitas']; ?> orang<br>
                        </p>
                        <div class="btn-group">
                            <button class="btn btn-primary btn-sm" onclick="showEditMeja(
                                '<?php echo $row['nomor_meja']; ?>',
                                '<?php echo $row['kapasitas']; ?>'
                            )">Edit</button>
                            
                            <?php if ($row['status_meja'] === 'kosong') { ?>
                            <button class="btn btn-success btn-sm btn-reservasi" 
                                    onclick="showReservasi('<?php echo $row['id_meja']; ?>', '<?php echo $row['nomor_meja']; ?>')">
                                Reservasi
                            </button>
                            <?php } ?>
                            
                            <a href="test2.php?action=delete&id_meja=<?php echo $row['id_meja']; ?>" 
                               class="btn btn-danger btn-sm btn-reservasi" 
                               onclick="return confirm('Hapus meja <?php echo $row['nomor_meja']; ?>?')">
                                Delete
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- Jika tidak ada meja -->
        <?php if (empty($dataMeja)) { ?>
        <div class="alert alert-warning text-center">
            Tidak ada meja tersedia. <a href="javascript:void(0)" onclick="showTambahMeja()">Tambah meja pertama</a>
        </div>
        <?php } ?>
    </div>

    <!-- Modal Tambah/Edit Meja -->
    <div class="modal fade" id="modalMeja" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalTitleMeja"></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="id_meja" id="id_meja">
                    <input type="hidden" name="action" id="action_meja">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nomor Meja</label>
                            <input type="number" class="form-control" name="nomor_meja" id="nomor_meja" required>
                        </div>
                        <div class="form-group">
                            <label>Kapasitas</label>
                            <input type="number" class="form-control" name="kapasitas" id="kapasitas" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Reservasi -->
    <div class="modal fade" id="modalReservasi" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalTitleReservasi">Reservasi Meja</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="reservasi">
                    <input type="hidden" name="id_meja" id="id_meja_reservasi">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Meja: <span id="info_meja_reservasi"></span></strong>
                        </div>
                        
                        <div class="form-group">
                            <label>Pelanggan</label>
                            <select class="form-control" name="id_pelanggan" id="id_pelanggan" required>
                                <option value="">Pilih Pelanggan</option>
                                <?php foreach ($dataPelanggan as $pelanggan) { ?>
                                    <option value="<?php echo $pelanggan['id_pelanggan']; ?>">
                                        <?php echo $pelanggan['nama_pelanggan']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <small class="form-text text-muted">
                                <a href="javascript:void(0)" onclick="alert('Fitur tambah pelanggan akan diimplementasi terpisah')">+ Tambah Pelanggan Baru</a>
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label>Tanggal Reservasi</label>
                            <input type="date" class="form-control" name="tanggal_reservasi" id="tanggal_reservasi" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jam Mulai</label>
                                    <input type="time" class="form-control" name="jam_mulai" id="jam_mulai" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jam Selesai</label>
                                    <input type="time" class="form-control" name="jam_selesai" id="jam_selesai" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Catatan Khusus (Opsional)</label>
                            <textarea class="form-control" name="catatan" id="catatan" rows="2" placeholder="Contoh: Untuk ulang tahun, minta kursi tambahan, dll."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Konfirmasi Reservasi</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        function showTambahMeja() {
            document.getElementById("modalTitleMeja").innerText = "Tambah Meja";
            document.getElementById("id_meja").value = "";
            document.getElementById("nomor_meja").value = "";
            document.getElementById("kapasitas").value = "";
            document.getElementById("action_meja").value = "tambah_meja";
            $('#modalMeja').modal('show');
        }
        
        function showEditMeja(id, nomor, kapasitas) {
            document.getElementById("modalTitleMeja").innerText = "Edit Meja";
            document.getElementById("id_meja").value = id;
            document.getElementById("nomor_meja").value = nomor;
            document.getElementById("kapasitas").value = kapasitas;
            document.getElementById("action_meja").value = "edit_meja";
            $('#modalMeja').modal('show');
        }
        
        function showReservasi(id_meja, nomor_meja) {
            // Set informasi meja
            document.getElementById("id_meja_reservasi").value = id_meja;
            document.getElementById("info_meja_reservasi").innerText = "Meja " + nomor_meja;
            document.getElementById("modalTitleReservasi").innerText = "Reservasi Meja " + nomor_meja;
            
            // Set tanggal minimal hari ini
            var today = new Date().toISOString().split('T')[0];
            document.getElementById("tanggal_reservasi").value = today;
            document.getElementById("tanggal_reservasi").min = today;
            
            // Set default jam (2 jam ke depan)
            var now = new Date();
            var defaultStart = new Date(now.getTime() + 2 * 60 * 60 * 1000); // 2 jam dari sekarang
            var defaultEnd = new Date(defaultStart.getTime() + 2 * 60 * 60 * 1000); // 2 jam kemudian
            
            document.getElementById("jam_mulai").value = formatTime(defaultStart);
            document.getElementById("jam_selesai").value = formatTime(defaultEnd);
            
            // Reset form lainnya
            document.getElementById("id_pelanggan").value = "";
            document.getElementById("catatan").value = "";
            
            $('#modalReservasi').modal('show');
        }
        
        function formatTime(date) {
            var hours = date.getHours().toString().padStart(2, '0');
            var minutes = date.getMinutes().toString().padStart(2, '0');
            return hours + ':' + minutes;
        }
        
        // Validasi jam selesai harus setelah jam mulai
        document.getElementById("jam_selesai").addEventListener('change', function() {
            var jamMulai = document.getElementById("jam_mulai").value;
            var jamSelesai = this.value;
            
            if (jamMulai && jamSelesai && jamSelesai <= jamMulai) {
                alert("Jam selesai harus setelah jam mulai");
                this.value = "";
                this.focus();
            }
        });
    </script>
</body>
</html>