<?php
require_once '../models/mejaModel.php';
$model = new MejaModel();

// Handle POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'tambah_meja') {
            // Default status saat tambah meja adalah 'Kosong'
            $model->create($_POST['nomor_meja'], $_POST['kapasitas'], 'Kosong');
        } 
        elseif ($action === 'edit_meja') {
            // Update termasuk status meja
            $model->update($_POST['id_meja'], $_POST['nomor_meja'], $_POST['kapasitas'], $_POST['status_meja']);
        }
        elseif ($action === 'reservasi') {
            $nama = $_POST['nama_pelanggan'];
            $no_hp = $_POST['no_hp'];
            $id_meja = $_POST['id_meja'];
            $tanggal = $_POST['tanggal_reservasi'];
            $jam = $_POST['jam_mulai'];
            $durasi = $_POST['durasi'];

            $model->reservasiBaru($nama, $no_hp, $id_meja, $tanggal, $jam, $durasi);
        }
    }
    header("Location: booking.php");
    exit;
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete_meja' && isset($_GET['id'])) {
    $model->delete($_GET['id']);
    header("Location: booking.php");
    exit;
}

// Get Data
$dataMeja = $model->getStatusMejaLengkap();
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
        .card-meja {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-meja:hover { transform: translateY(-5px); }
        
        .status-badge {
            position: absolute;
            top: 10px; right: 10px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Status Colors */
        .status-tersedia { background-color: #198754; } /* Hijau */
        .status-dipakai { background-color: #fd7e14; } /* Oranye (Reservasi) */
        .status-penuh-manual { background-color: #dc3545; } /* Merah (Manual Penuh/Rusak) */
        
        .meja-number {
            font-size: 2rem;
            font-weight: 800;
            color: #333;
        }
        
        .history-list-container {
            max-height: 600px;
            overflow-y: auto;
        }
        .history-list-container::-webkit-scrollbar { width: 6px; }
        .history-list-container::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 3px; }
    </style>
</head>

<body>
    <div class="container-fluid bg-white p-0">
        
        <div class="container-fluid p-0">
            <?php include "navbar.php"; ?>
            <div class="container-fluid py-5 bg-dark hero-header mb-5">
                <div class="container text-center my-5 pt-5 pb-4">
                    <h1 class="display-3 text-white mb-3 animated slideInDown">Manajemen Meja</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center text-uppercase">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Booking Management</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="container-fluid py-3 wow fadeInUp" data-wow-delay="0.1s">
            <div class="container">
                
                <!-- Action Buttons -->
                <div class="row mb-4">
                    <div class="col-12 d-flex justify-content-between align-items-center bg-light p-3 rounded">
                        <h4 class="mb-0 text-primary">Control Panel</h4>
                        <div>
                            <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalReservasi">
                                <i class="fa fa-calendar-plus me-2"></i>Buat Reservasi Baru
                            </button>
                            <button class="btn btn-primary" onclick="showTambahMeja()">
                                <i class="fa fa-plus me-2"></i>Tambah Meja
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    
                    <!-- LEFT SIDE: History -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0 text-white"><i class="fa fa-history me-2"></i>History Reservasi</h5>
                            </div>
                            <div class="card-body">
                                <form method="GET" class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <input type="date" class="form-control form-control-sm" name="filter_date" value="<?php echo $filterDate; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select form-select-sm" name="sort_order">
                                            <option value="DESC" <?php echo $sortOrder == 'DESC' ? 'selected' : ''; ?>>Terbaru</option>
                                            <option value="ASC" <?php echo $sortOrder == 'ASC' ? 'selected' : ''; ?>>Terlama</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fa fa-search"></i></button>
                                    </div>
                                </form>

                                <div class="history-list-container">
                                    <?php if (empty($dataReservasi)): ?>
                                        <div class="text-center text-muted py-4"><small>Belum ada reservasi.</small></div>
                                    <?php else: ?>
                                        <?php foreach ($dataReservasi as $res): ?>
                                            <div class="border-bottom pb-2 mb-2">
                                                <div class="d-flex justify-content-between">
                                                    <strong><?php echo htmlspecialchars($res['nama_pelanggan']); ?></strong>
                                                    <span class="badge bg-primary">Meja <?php echo isset($res['nomor_meja']) ? $res['nomor_meja'] : '?'; ?></span>
                                                </div>
                                                <small class="text-muted d-block">
                                                    <i class="fa fa-phone me-1"></i> <?php echo isset($res['no_hp']) ? htmlspecialchars($res['no_hp']) : '-'; ?>
                                                </small>
                                                <small class="text-dark">
                                                    <i class="fa fa-clock me-1"></i> 
                                                    <?php echo date('d/m/Y', strtotime($res['tanggal_reservasi'])); ?> | 
                                                    <?php echo date('H:i', strtotime($res['jam_mulai'])); ?> - <?php echo date('H:i', strtotime($res['jam_selesai'])); ?>
                                                </small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT SIDE: List Meja -->
                    <div class="col-lg-8">
                        <div class="row g-3">
                            <?php foreach ($dataMeja as $meja): 
                                // Logic warna card berdasarkan status gabungan
                                $cardBg = ($meja['status_final'] !== 'Kosong') ? 'bg-light' : 'bg-white';
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card card-meja <?php echo $cardBg; ?> position-relative">
                                    <div class="<?php echo $meja['css_class']; ?> status-badge">
                                        <?php echo $meja['status_final']; ?>
                                    </div>
                                    <div class="card-body text-center pt-5">
                                        <h6 class="text-muted text-uppercase">Nomor Meja</h6>
                                        <div class="meja-number"><?php echo $meja['nomor_meja']; ?></div>
                                        <p class="text-muted mb-4">Kapasitas: <?php echo $meja['kapasitas']; ?> Orang</p>
                                        
                                        <div class="d-flex justify-content-center gap-2">
                                            <!-- Tombol Edit mengirim 4 parameter: id, nomor, kapasitas, dan status_manual -->
                                            <button class="btn btn-sm btn-outline-warning" onclick="showEditMeja(
                                                '<?php echo $meja['id_meja']; ?>',
                                                '<?php echo $meja['nomor_meja']; ?>',
                                                '<?php echo $meja['kapasitas']; ?>',
                                                '<?php echo $meja['status_manual']; ?>' 
                                            )">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <a href="booking.php?action=delete_meja&id=<?php echo $meja['id_meja']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Yakin ingin menghapus Meja <?php echo $meja['nomor_meja']; ?>?')">
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

        <!-- MODAL TAMBAH/EDIT MEJA (Digabung/Dynamic) -->
        <div class="modal fade" id="modalMeja" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleMeja">Form Meja</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" id="action_meja" value="tambah_meja">
                            <input type="hidden" name="id_meja" id="id_meja">
                            
                            <div class="mb-3">
                                <label class="form-label">Nomor Meja</label>
                                <input type="number" class="form-control" name="nomor_meja" id="nomor_meja" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kapasitas</label>
                                <input type="number" class="form-control" name="kapasitas" id="kapasitas" required>
                            </div>
                            
                            <!-- Dropdown Status Meja (Hanya muncul/relevan saat Edit, tapi kita tampilkan saja) -->
                            <div class="mb-3" id="div_status_meja">
                                <label class="form-label">Status Meja (Manual)</label>
                                <select class="form-select" name="status_meja" id="status_meja">
                                    <option value="Kosong">Kosong (Available)</option>
                                    <option value="Penuh">Penuh (Maintenance/Walk-in)</option>
                                </select>
                                <small class="text-muted">Status 'Penuh' akan menimpa jadwal reservasi.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL RESERVASI -->
        <div class="modal fade" id="modalReservasi" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white">Form Reservasi (Kasir)</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="reservasi">
                            
                            <h6 class="text-primary mb-3 border-bottom pb-2">1. Data Pelanggan</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Pelanggan</label>
                                    <input type="text" class="form-control" name="nama_pelanggan" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nomor HP</label>
                                    <input type="text" class="form-control" name="no_hp" required>
                                </div>
                            </div>

                            <h6 class="text-primary mb-3 border-bottom pb-2">2. Detail Booking</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Pilih Meja</label>
                                    <select class="form-select" name="id_meja" required>
                                        <option value="">-- Pilih Nomor Meja --</option>
                                        <?php foreach ($dataMeja as $m): ?>
                                            <option value="<?php echo $m['id_meja']; ?>">
                                                Meja <?php echo $m['nomor_meja']; ?> 
                                                (<?php echo $m['status_final']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Reservasi</label>
                                    <input type="date" class="form-control" name="tanggal_reservasi" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jam Mulai</label>
                                    <input type="time" class="form-control" name="jam_mulai" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Durasi (Jam)</label>
                                    <input type="number" class="form-control" name="durasi" value="1" min="1" max="5" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success">Konfirmasi Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include 'footer.php'; ?>
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../lib/wow/wow.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/waypoints/waypoints.min.js"></script>
    <script src="../lib/counterup/counterup.min.js"></script>
    <script src="../lib/tempusdominus/js/moment.min.js"></script>
    <script src="../lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="../lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="../js/main.js"></script>

    <script>
        function showTambahMeja() {
            document.getElementById('modalTitleMeja').innerText = "Tambah Meja Baru";
            document.getElementById('action_meja').value = "tambah_meja";
            document.getElementById('id_meja').value = "";
            document.getElementById('nomor_meja').value = "";
            document.getElementById('kapasitas').value = "";
            
            // Set default status kosong saat tambah
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
            
            // Set dropdown sesuai status di DB
            document.getElementById('status_meja').value = status;
            
            var myModal = new bootstrap.Modal(document.getElementById('modalMeja'));
            myModal.show();
        }
    </script>
</body>
</html>