<?php
    require_once '../controllers/BookingController.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manajemen Meja</title>
    <?php include "header.php"; ?>
    <style>
        .card-meja { transition: transform 0.2s; border: none; cursor: pointer; }
        .card-meja:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .status-bar { height: 6px; width: 100%; }
        .bg-vacant { background-color: #198754; } 
        .bg-occupied { background-color: #dc3545; } 
        .bg-reserved { background-color: #ffc107; } 
        .meja-num { font-size: 2.5rem; font-weight: 800; color: #333; }
        .scrollable-list { max-height: 400px; overflow-y: auto; }
        /* Disabled card style */
        .card-disabled { opacity: 0.7; background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container-fluid bg-white p-0">
        <?php include "navbar.php"; ?>
        
        <div class="container-fluid py-5 bg-dark hero-header mb-5">
            <div class="container text-center my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3">Table & Booking</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb justify-content-center text-uppercase"><li class="breadcrumb-item text-white">Home</li><li class="breadcrumb-item text-white active">Booking</li></ol></nav>
            </div>
        </div>

        <div class="container-fluid py-3">
            <div class="container">
                
                <div class="card shadow-sm mb-4">
                    <div class="card-body bg-light">
                        <div class="row align-items-end g-2">
                            <div class="col-md-5">
                                <label class="small fw-bold text-muted">Cek Ketersediaan:</label>
                                <form method="GET" class="d-flex gap-2">
                                    <input type="date" name="tanggal" class="form-control" value="<?= $f_tanggal ?>">
                                    <input type="time" name="jam" class="form-control" value="<?= $f_jam ?>">
                                    <button class="btn btn-primary"><i class="fa fa-search"></i></button>
                                </form>
                            </div>
                            <div class="col-md-7 text-md-end mt-3 mt-md-0">
                                <div class="text-muted small mb-2 d-inline-block me-3">
                                    <i class="fa fa-circle text-success"></i> Available
                                    <i class="fa fa-circle text-warning ms-2"></i> Booked
                                    <i class="fa fa-circle text-danger ms-2"></i> Dipakai
                                </div>
                                <button class="btn btn-outline-secondary me-1" onclick="modalHistory()">History</button>
                                <button class="btn btn-outline-dark me-1" onclick="modalMeja()">+ Meja</button>
                                <button class="btn btn-primary" onclick="modalRes()">+ Booking</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="text-primary m-0"><i class="fa fa-th-large me-2"></i>Status Meja</h4>
                            <span class="badge bg-secondary">Per: <?= date('d M, H:i', strtotime("$f_tanggal $f_jam")) ?></span>
                        </div>

                        <div class="row g-3">
                            <?php foreach($mejaList as $m): 
                                $isDisabled = false;
                                $cardClass = "";
                                
                                if ($m['status'] == 'terisi') {
                                    $col='bg-occupied'; $txt='DIPAKAI'; $tCol='text-danger'; 
                                    $isDisabled = true; $cardClass = "card-disabled";
                                } elseif (!empty($m['reserved_by'])) {
                                    $col='bg-reserved'; $txt='BOOKED: '.explode(' ',$m['reserved_by'])[0]; $tCol='text-warning'; 
                                    $isDisabled = true;
                                } else {
                                    $col='bg-vacant'; $txt='AVAILABLE'; $tCol='text-success'; 
                                }
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card card-meja shadow-sm h-100 <?= $cardClass ?>">
                                    <div class="status-bar <?= $col ?>"></div>
                                    <div class="card-body text-center position-relative">
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <a href="#" class="text-muted small" onclick='editMeja(<?= json_encode($m) ?>)'><i class="fa fa-cog"></i></a>
                                        </div>
                                        
                                        <small class="fw-bold <?= $tCol ?>"><?= $txt ?></small>
                                        <div class="meja-num my-2"><?= $m['nomor_meja'] ?></div>
                                        <div class="text-muted small mb-3"><i class="fa fa-users"></i> Max <?= $m['kapasitas'] ?></div>
                                        
                                        <?php if(!$isDisabled): ?>
                                            <button class="btn btn-sm btn-outline-primary w-100" onclick="bookSpecificMeja('<?= $m['id'] ?>')">Book This</button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-light text-muted w-100" disabled>Tidak Tersedia</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow border-0 h-100">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-white"><i class="fa fa-clock me-2"></i>Jadwal Aktif</h5>
                            </div>
                            <div class="list-group list-group-flush scrollable-list">
                                <?php if(empty($upcomingList)): ?>
                                    <div class="p-4 text-center text-muted">
                                        <i class="fa fa-mug-hot fa-3x mb-3 text-light"></i><br>
                                        Tidak ada reservasi aktif saat ini.
                                    </div>
                                <?php else: foreach($upcomingList as $r): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($r['nama_pelanggan']) ?></h6>
                                                <small class="text-muted"><?= $r['no_telepon'] ?></small>
                                            </div>
                                            <span class="badge bg-primary align-self-start">Meja <?= $r['nomor_meja'] ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2 align-items-center bg-light p-2 rounded">
                                            <small class="fw-bold text-dark">
                                                <i class="fa fa-calendar-alt me-1"></i>
                                                <?= date('d M - H:i', strtotime($r['tanggal_reservasi'].' '.$r['jam_reservasi'])) ?>
                                            </small>
                                            <a href="?action=cancel_res&id=<?= $r['id'] ?>" onclick="return confirm('Yakin ingin membatalkan reservasi <?= htmlspecialchars($r['nama_pelanggan']) ?>?')" class="btn btn-sm btn-danger py-0" style="font-size: 0.75rem;">Batal</a>
                                        </div>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalRes" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" class="modal-content">
                    <div class="modal-header bg-primary text-white"><h5 class="modal-title">Buat Booking</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_reservasi">
                        
                        <div class="mb-3"><label class="fw-bold">Pilih Meja</label>
                            <select name="meja_id" id="res_meja_id" class="form-select" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach($mejaList as $m): 
                                    if($m['status'] == 'kosong'): ?>
                                    <option value="<?= $m['id'] ?>">Meja <?= $m['nomor_meja'] ?> (Max <?= $m['kapasitas'] ?>)</option>
                                <?php endif; endforeach; ?>
                            </select>
                            <small class="text-muted">* Meja berstatus 'Terisi' tidak muncul disini</small>
                        </div>
                        
                        <div class="mb-3 position-relative">
                            <label class="fw-bold">Nama Pelanggan</label>
                            <input type="text" name="nama" id="search_nama" class="form-control" list="list_pelanggan" placeholder="Ketik nama..." required autocomplete="off">
                            <datalist id="list_pelanggan">
                                <?php foreach($pelangganList as $p): ?>
                                    <option value="<?= htmlspecialchars($p['nama_pelanggan']) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>

                        <div class="mb-3"><label class="fw-bold">No. Telepon</label><input type="text" name="telp" id="res_telp" class="form-control" required></div>
                        <div class="row">
                            <div class="col-6 mb-3"><label>Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= $f_tanggal ?>" required></div>
                            <div class="col-6 mb-3"><label>Jam</label><input type="time" name="jam" class="form-control" value="<?= $f_jam ?>" required></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-success w-100">Simpan Reservasi</button></div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalHistory" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-secondary text-white"><h5 class="modal-title">Riwayat Reservasi</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover small">
                                <thead class="table-dark">
                                    <tr><th>Tanggal</th><th>Jam</th><th>Pelanggan</th><th>Meja</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($historyList as $h): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($h['tanggal_reservasi'])) ?></td>
                                        <td><?= date('H:i', strtotime($h['jam_reservasi'])) ?></td>
                                        <td><?= htmlspecialchars($h['nama_pelanggan']) ?><br><span class="text-muted"><?= $h['no_telepon'] ?></span></td>
                                        <td><?= $h['nomor_meja'] ?></td>
                                        <td>
                                            <?php if($h['status']=='aktif'): ?><span class="badge bg-success">Aktif</span>
                                            <?php elseif($h['status']=='batal'): ?><span class="badge bg-danger">Batal</span>
                                            <?php else: ?><span class="badge bg-secondary"><?= $h['status'] ?></span><?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalMeja" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" class="modal-content">
                    <div class="modal-header bg-dark text-white"><h5 class="modal-title" id="tMeja">Tambah Meja</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="save_meja">
                        <input type="hidden" name="id_meja" id="id_meja">
                        <div class="mb-3"><label>Nomor</label><input type="text" name="nomor" id="nomor" class="form-control" required></div>
                        <div class="mb-3"><label>Kapasitas</label><input type="number" name="kapasitas" id="kapasitas" class="form-control" required></div>
                        <div class="mb-3">
                            <label>Status (Walk-In)</label>
                            <select name="status" id="status" class="form-select">
                                <option value="kosong">Kosong (Available)</option>
                                <option value="terisi">Terisi (Dipakai)</option>
                            </select>
                            <small class="text-muted">Jika 'Terisi', meja tidak bisa dibooking online.</small>
                        </div>
                        <div id="btnDel" class="d-none"><a href="#" id="linkDel" class="text-danger small" onclick="return confirm('Hapus?')">Hapus meja</a></div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/main.js"></script>
    
    <script>
        setInterval(function(){
            if(!document.querySelector('.modal.show')) {
                window.location.reload();
            }
        }, 60000); 

        var mMeja = new bootstrap.Modal(document.getElementById('modalMeja'));
        var mRes = new bootstrap.Modal(document.getElementById('modalRes'));
        var mHist = new bootstrap.Modal(document.getElementById('modalHistory'));
        
        const pelangganData = {};
        <?php foreach($pelangganList as $p): ?>
            pelangganData["<?= $p['nama_pelanggan'] ?>"] = "<?= $p['no_telepon'] ?>";
        <?php endforeach; ?>

        document.getElementById('search_nama').addEventListener('input', function() {
            if(pelangganData[this.value]) document.getElementById('res_telp').value = pelangganData[this.value];
        });

        function modalMeja() {
            document.getElementById('tMeja').innerText = "Tambah Meja";
            document.getElementById('id_meja').value = ""; document.getElementById('nomor').value = ""; document.getElementById('kapasitas').value = "";
            document.getElementById('btnDel').classList.add('d-none');
            mMeja.show();
        }
        function editMeja(d) {
            document.getElementById('tMeja').innerText = "Edit Meja "+d.nomor_meja;
            document.getElementById('id_meja').value = d.id; document.getElementById('nomor').value = d.nomor_meja; document.getElementById('kapasitas').value = d.kapasitas; document.getElementById('status').value = d.status;
            document.getElementById('linkDel').href = "?action=delete_meja&id="+d.id;
            document.getElementById('btnDel').classList.remove('d-none');
            mMeja.show();
        }
        function modalRes() { document.getElementById('res_meja_id').value=""; document.getElementById('search_nama').value=""; document.getElementById('res_telp').value=""; mRes.show(); }
        
        function bookSpecificMeja(id) { 
            document.getElementById('res_meja_id').value=id; 
            mRes.show(); 
        }
        function modalHistory() { mHist.show(); }
    </script>
    
    <?php if(isset($_SESSION['notif'])): ?>
        <script>alert("<?= $_SESSION['notif'] ?>"); </script>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>
</body>
</html>