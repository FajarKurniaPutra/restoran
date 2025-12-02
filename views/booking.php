<?php
session_start(); 
require_once '../models/mejaModel.php';
$model = new MejaModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'tambah_meja') {
            $res = $model->create($_POST['nomor_meja'], $_POST['kapasitas'], 'Kosong');
            $_SESSION['notif'] = $res ? "Meja berhasil DITAMBAHKAN!" : "Gagal menambah meja.";
        } 
        elseif ($action === 'edit_meja') {
            $res = $model->update($_POST['id_meja'], $_POST['nomor_meja'], $_POST['kapasitas'], $_POST['status_meja']);
            $_SESSION['notif'] = $res ? "Meja berhasil DIUPDATE!" : "Gagal update meja.";
        }
        elseif ($action === 'reservasi') {
            $nama = $_POST['nama_pelanggan'];
            $no_hp = $_POST['no_hp'];
            $id_meja = $_POST['id_meja'];
            $tanggal = $_POST['tanggal_reservasi'];
            $jam = $_POST['jam_mulai'];
            $durasi = $_POST['durasi'];

            $hasil = $model->reservasiBaru($nama, $no_hp, $id_meja, $tanggal, $jam, $durasi);
            
            if ($hasil === "BENTROK") {
                $_SESSION['notif'] = "Gagal! Meja tersebut sudah dipesan pada jam yang sama.";
            } elseif ($hasil === "SUKSES") {
                $_SESSION['notif'] = "Reservasi Berhasil dibuat!";
            } else {
                $_SESSION['notif'] = "Terjadi kesalahan sistem.";
            }
        }
        
        header("Location: booking.php");
        exit;
    }
}

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete_meja' && isset($_GET['id'])) {
        $model->delete($_GET['id']);
        $_SESSION['notif'] = "Meja beserta riwayat reservasinya berhasil DIHAPUS!";
        header("Location: booking.php");
        exit;
    }
    if ($_GET['action'] === 'cancel_reservasi' && isset($_GET['id'])) {
        $model->cancelReservasi($_GET['id']);
        $_SESSION['notif'] = "Reservasi berhasil DIBATALKAN.";
        header("Location: booking.php");
        exit;
    }
}

// ... (Pengambilan Data untuk View) ...
$filterKapasitas = isset($_GET['filter_kapasitas']) ? (int)$_GET['filter_kapasitas'] : 0;
$dataMeja = $model->getStatusMejaLengkap($filterKapasitas);

$filterDate = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$dataReservasi = $model->getHistoryReservasi($sortOrder, $filterDate);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Booking & Manajemen Meja</title>
    <?php include "header.php"; ?>
    
    <style>
        .card-meja { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s; margin-bottom: 20px; overflow: hidden; }
        .card-meja:hover { transform: translateY(-5px); }
        .status-badge { position: absolute; top: 10px; right: 10px; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; color: white; text-transform: uppercase; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .status-tersedia { background-color: #198754; } 
        .status-dipakai { background-color: #ffc107; color: #000 !important; } 
        .status-penuh-manual { background-color: #dc3545; }
        .meja-number { font-size: 2rem; font-weight: 800; color: #333; }
        .history-list-container { max-height: 600px; overflow-y: auto; }
        .res-time-badge { background: rgba(255,255,255,0.8); padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; margin-top: 5px; display: inline-block; font-weight: bold; color: #000; }
    </style>
</head>

<body>
    <div class="container-fluid bg-white p-0">
        <?php include "navbar.php"; ?>
        
        <div class="container-fluid py-5 bg-dark hero-header mb-5">
            <div class="container text-center my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3">Manajemen Meja</h1>
            </div>
        </div>

        <div class="container-fluid py-3">
            <div class="container">
                
                <div class="row mb-4">
                    <div class="col-12 bg-light p-3 rounded d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="mb-0 text-primary me-3">Control Panel</h4>
                            <form method="GET" class="d-flex align-items-center">
                                <input type="number" name="filter_kapasitas" class="form-control me-2" placeholder="Min. Kapasitas" value="<?php echo $filterKapasitas > 0 ? $filterKapasitas : ''; ?>" style="width: 150px;">
                                <button type="submit" class="btn btn-secondary"><i class="fa fa-filter"></i> Cari</button>
                            </form>
                        </div>
                        <div>
                            <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalReservasi">
                                <i class="fa fa-calendar-plus me-2"></i>Reservasi Baru
                            </button>
                            <button class="btn btn-primary" onclick="showTambahMeja()"><i class="fa fa-plus me-2"></i>Tambah Meja</button>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-dark text-white"><h5 class="mb-0 text-white"><i class="fa fa-history me-2"></i>Jadwal Aktif</h5></div>
                            <div class="card-body">
                                <form method="GET" class="row g-2 mb-3">
                                    <div class="col-md-6"><input type="date" class="form-control form-control-sm" name="filter_date" value="<?php echo $filterDate; ?>"></div>
                                    <div class="col-md-4"><select class="form-select form-select-sm" name="sort_order">
                                        <option value="DESC" <?php echo $sortOrder == 'DESC' ? 'selected' : ''; ?>>Terbaru</option>
                                        <option value="ASC" <?php echo $sortOrder == 'ASC' ? 'selected' : ''; ?>>Terlama</option></select>
                                    </div>
                                    <div class="col-md-2"><button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa fa-search"></i></button></div>
                                </form>

                                <div class="history-list-container">
                                    <?php if (empty($dataReservasi)): ?>
                                        <div class="text-center text-muted py-4"><small>Tidak ada jadwal reservasi aktif.</small></div>
                                    <?php else: ?>
                                        <?php foreach ($dataReservasi as $res): ?>
                                            <div class="border-bottom pb-2 mb-2 position-relative">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($res['nama_pelanggan']); ?></strong><br>
                                                        <span class="badge bg-primary">Meja <?php echo $res['nomor_meja']; ?></span>
                                                    </div>
                                                    <a href="booking.php?action=cancel_reservasi&id=<?php echo $res['id_reservasi']; ?>" 
                                                        class="btn btn-xs btn-outline-danger btn-sm"
                                                        onclick="return confirm('Apakah anda yakin ingin membatalkan reservasi pelanggan <?php echo htmlspecialchars($res['nama_pelanggan']); ?>?');"
                                                        title="Cancel Reservasi"><i class="fa fa-times"></i></a>
                                                </div>
                                                <small class="text-muted d-block mt-1"><i class="fa fa-phone me-1"></i> <?php echo htmlspecialchars($res['no_hp']); ?></small>
                                                <small class="text-dark fw-bold">
                                                    <i class="fa fa-clock me-1"></i> 
                                                    <?php echo date('d/m/Y', strtotime($res['tanggal_reservasi'])); ?> | <?php echo date('H:i', strtotime($res['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($res['jam_selesai'])); ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="row g-3">
                            <?php if(empty($dataMeja)): ?>
                                <div class="col-12 text-center text-muted p-5"><h4>Tidak ada meja ditemukan.</h4></div>
                            <?php endif; ?>

                            <?php foreach ($dataMeja as $meja): 
                                $cardBg = ($meja['status_final'] === 'Kosong') ? 'bg-white' : 'bg-light';
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card card-meja <?php echo $cardBg; ?> position-relative">
                                    <div class="<?php echo $meja['css_class']; ?> status-badge"><?php echo $meja['status_final']; ?></div>
                                    <div class="card-body text-center pt-5">
                                        <h6 class="text-muted text-uppercase">Nomor Meja</h6>
                                        <div class="meja-number"><?php echo $meja['nomor_meja']; ?></div>
                                        <p class="text-muted mb-2">Kapasitas: <?php echo $meja['kapasitas']; ?> Orang</p>
                                        
                                        <?php if (!empty($meja['info_waktu'])): ?>
                                            <div class="mb-3">
                                                <div class="res-time-badge border border-warning bg-warning">
                                                    <i class="fa fa-clock"></i> <?php echo $meja['info_waktu']; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mb-4"></div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-sm btn-outline-warning" onclick="showEditMeja(
                                                '<?php echo $meja['id_meja']; ?>',
                                                '<?php echo $meja['nomor_meja']; ?>',
                                                '<?php echo $meja['kapasitas']; ?>',
                                                '<?php echo $meja['status_manual']; ?>')">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            
                                            <a href="booking.php?action=delete_meja&id=<?php echo $meja['id_meja']; ?>" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('PERINGATAN KERAS!\n\nAnda akan menghapus Meja No. <?php echo $meja['nomor_meja']; ?>.\n\nJika meja ini memiliki riwayat RESERVASI, data reservasi tersebut JUGA AKAN DIHAPUS agar tidak terjadi error.\n\nApakah Anda yakin ingin melanjutkan penghapusan permanen ini?');">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalMeja" tabindex="-1">
            <div class="modal-dialog"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" id="modalTitleMeja">Form Meja</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST"><div class="modal-body">
                        <input type="hidden" name="action" id="action_meja" value="tambah_meja"><input type="hidden" name="id_meja" id="id_meja">
                        <div class="mb-3"><label>Nomor Meja</label><input type="number" class="form-control" name="nomor_meja" id="nomor_meja" required></div>
                        <div class="mb-3"><label>Kapasitas</label><input type="number" class="form-control" name="kapasitas" id="kapasitas" required></div>
                        <div class="mb-3" id="div_status_meja"><label>Status Manual</label><select class="form-select" name="status_meja" id="status_meja">
                            <option value="Kosong">Kosong (Available)</option>
                            <option value="Penuh">Penuh (Rusak/Maintenance)</option>
                        </select></div>
                </div><div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div></form>
            </div></div>
        </div>

        <div class="modal fade" id="modalReservasi" tabindex="-1">
            <div class="modal-dialog modal-lg"><div class="modal-content">
                    <div class="modal-header bg-primary text-white"><h5 class="modal-title text-white">Buat Reservasi Baru</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                    <form method="POST"><div class="modal-body">
                            <input type="hidden" name="action" value="reservasi">
                            <h6 class="text-primary mb-3 border-bottom pb-2">1. Data Pelanggan</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label>Nama Pelanggan</label>
                                    <input type="text" class="form-control" name="nama_pelanggan" required>
                                </div><div class="col-md-6">
                                    <label>Nomor HP</label>
                                    <input type="text" class="form-control" name="no_hp" required>
                                </div></div>
                            <h6 class="text-primary mb-3 border-bottom pb-2">2. Detail Booking</h6>
                            <div class="row g-3"><div class="col-md-6">
                                <label>Pilih Meja</label>
                                <select class="form-select" name="id_meja" required>
                                    <option value="">-- Pilih Meja --</option>
                                    <?php foreach ($dataMeja as $m): $disabled = ($m['status_final'] !== 'Kosong') ? 'disabled style="color:red;"' : ''; ?>
                                    <option value="<?php echo $m['id_meja']; ?>" 
                                        <?php echo $disabled; ?>>Meja <?php echo $m['nomor_meja']; ?> (<?php echo $m['kapasitas']; ?> org) - <?php echo $m['status_final']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Tanggal</label>
                            <input type="date" class="form-control" name="tanggal_reservasi" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Jam</label>
                            <input type="time" class="form-control" name="jam_mulai" required>
                        </div><div class="col-md-6">
                            <label>Durasi</label>
                            <input type="number" class="form-control" name="durasi" value="1" min="1" max="5" required>
                        </div>
                    </div>
                    </div><div class="modal-footer"><button type="submit" class="btn btn-success">Konfirmasi Booking</button></div></form>
            </div></div>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/main.js"></script>

    <script>
        function showTambahMeja() { 
            document.getElementById('modalTitleMeja').innerText = "Tambah Meja Baru"; 
            document.getElementById('action_meja').value = "tambah_meja"; 
            document.getElementById('id_meja').value = ""; 
            document.getElementById('nomor_meja').value = ""; 
            document.getElementById('kapasitas').value = ""; 
            
            document.getElementById('div_status_meja').style.display = 'none';
            document.getElementById('status_meja').value = "Kosong"; 
            
            var myModal = new bootstrap.Modal(document.getElementById('modalMeja')); 
            myModal.show(); 
        }

        function showEditMeja(id, nomor, kapasitas, status) { 
            document.getElementById('modalTitleMeja').innerText = "Edit Meja"; 
            document.getElementById('action_meja').value = "edit_meja"; 
            document.getElementById('id_meja').value = id; 
            document.getElementById('nomor_meja').value = nomor; 
            document.getElementById('kapasitas').value = kapasitas; 
            
            document.getElementById('div_status_meja').style.display = 'block';
            document.getElementById('status_meja').value = status; 
            
            var myModal = new bootstrap.Modal(document.getElementById('modalMeja')); 
            myModal.show(); 
        }
    </script>

    <?php if (isset($_SESSION['notif'])): ?>
    <script>
        alert("<?php echo $_SESSION['notif']; ?>");
    </script>
    <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

</body>
</html>