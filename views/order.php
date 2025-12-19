<?php
    require_once '../controllers/OrderController.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Transaction</title>
    <?php include "header.php"; ?>
    <style>
        .menu-card-img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .search-container { position: relative; }
        .search-icon { position: absolute; right: 15px; top: 10px; color: #aaa; }
        .order-card { transition: transform 0.2s; }
        .order-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>
    <div class="container-fluid bg-white p-0">
        <?php include "navbar.php"; ?>
        
        <div class="container-fluid py-5 bg-dark hero-header mb-5">
            <div class="container text-center my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3">Order System</h1>
                <p class="text-white">Kelola pesanan dan pembayaran pelanggan</p>
            </div>
        </div>

        <div class="container mb-5">
            
            <?php if(!empty($msg)): ?>
                <div class="alert <?= $msgClass ?> alert-dismissible fade show" role="alert">
                    <i class="fa fa-info-circle me-2"></i><?= $msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <ul class="nav nav-pills nav-justified mb-4" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-order">
                        <i class="fa fa-cash-register me-2"></i>Input Order
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-kitchen">
                        <i class="fa fa-fire me-2"></i>Kitchen / Payment
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-index">
                        <i class="fa fa-database me-2"></i>Performance Demo
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                
                <div class="tab-pane fade show active" id="tab-order">
                    <div class="row g-4">
                        <div class="col-lg-10 mx-auto">
                            <div class="card border-0 shadow-lg">
                                <div class="card-header bg-primary text-white p-3">
                                    <h5 class="mb-0 text-white"><i class="fa fa-plus-circle me-2"></i>Buat Pesanan Baru</h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST" id="orderForm">
                                        <input type="hidden" name="action" value="create">
                                        
                                        <div class="row mb-4 bg-light p-3 rounded mx-0 border">
                                            <div class="col-md-12">
                                                <label class="fw-bold mb-2">Tipe Pesanan:</label><br>
                                                <div class="form-check form-check-inline me-4">
                                                    <input class="form-check-input" type="radio" name="tipe_order" id="tipe_dinein" value="dinein" checked>
                                                    <label class="form-check-label fw-bold" for="tipe_dinein"><i class="fa fa-chair text-primary me-1"></i> Dine In (Makan di Tempat)</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="tipe_order" id="tipe_takeaway" value="takeaway">
                                                    <label class="form-check-label fw-bold" for="tipe_takeaway"><i class="fa fa-shopping-bag text-warning me-1"></i> Takeaway (Bawa Pulang)</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label class="fw-bold mb-1">Cari Pelanggan</label>
                                                <input type="text" class="form-control" list="list_pelanggan" id="input_pelanggan" placeholder="Ketik nama pelanggan..." required>
                                                <datalist id="list_pelanggan">
                                                    <?php foreach($pelanggan as $p): ?>
                                                        <option data-id="<?= $p['id'] ?>" value="<?= htmlspecialchars($p['nama_pelanggan']) ?> (<?= $p['no_telepon'] ?>)">
                                                    <?php endforeach; ?>
                                                </datalist>
                                                <input type="hidden" name="pelanggan_id" id="hidden_pelanggan_id">
                                            </div>

                                            <div class="col-md-6" id="container_meja">
                                                <label class="fw-bold mb-1">Cari Meja Kosong</label>
                                                <input type="text" class="form-control" list="list_meja" id="input_meja" placeholder="Nomor meja..." autocomplete="off">
                                                <datalist id="list_meja">
                                                    <?php foreach($meja as $m): ?>
                                                        <option data-id="<?= $m['id'] ?>" value="Meja <?= $m['nomor_meja'] ?> (Kap: <?= $m['kapasitas'] ?>)">
                                                    <?php endforeach; ?>
                                                </datalist>
                                                <input type="hidden" name="meja_id" id="hidden_meja_id">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                            <h6 class="m-0 text-dark"><i class="fa fa-utensils me-2"></i>Pilih Daftar Menu</h6>
                                            <div class="search-container" style="width: 250px;">
                                                <input type="text" id="searchMenu" class="form-control form-control-sm" placeholder="Cari nama menu...">
                                                <i class="fa fa-search search-icon"></i>
                                            </div>
                                        </div>

                                        <div class="row g-3" id="menuContainer" style="max-height: 400px; overflow-y: auto;">
                                            <?php foreach($activeMenus as $m): 
                                                $imgSrc = (!empty($m['gambar']) && file_exists('../uploads/'.$m['gambar'])) ? '../uploads/'.$m['gambar'] : '../img/food-default.jpeg';
                                            ?>
                                            <div class="col-md-6 menu-item" data-name="<?= strtolower($m['nama_menu']) ?>">
                                                <div class="d-flex align-items-center border rounded p-2 bg-white h-100 shadow-sm border-start border-4 border-primary">
                                                    <img src="<?= $imgSrc ?>" class="menu-card-img shadow-sm">
                                                    <div class="ms-3 flex-grow-1">
                                                        <div class="fw-bold text-dark mb-0"><?= $m['nama_menu'] ?></div>
                                                        <div class="text-primary small fw-bold">Rp <?= number_format($m['harga']) ?></div>
                                                    </div>
                                                    <div class="text-end">
                                                        <input type="hidden" name="menu_id[]" value="<?= $m['id'] ?>">
                                                        <input type="number" name="qty[]" class="form-control form-control-sm border-primary text-center fw-bold" style="width: 65px;" placeholder="0" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                            <div id="noMenuFound" class="col-12 text-center text-muted py-3" style="display: none;">Menu tidak ditemukan.</div>
                                        </div>

                                        <div class="d-grid mt-4">
                                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow">
                                                <i class="fa fa-paper-plane me-2"></i>SUBMIT ORDER SEKARANG
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-kitchen">
                    <div class="row g-4">
                        <?php if(empty($orders)): ?>
                            <div class="col-12 text-center text-muted py-5">
                                <i class="fa fa-clipboard-list fa-4x mb-3 opacity-25"></i>
                                <h4>Tidak ada pesanan aktif saat ini.</h4>
                            </div>
                        <?php endif; ?>

                        <?php foreach($orders as $o): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100 order-card">
                                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">
                                        <?= ($o['meja_id']) ? '<i class="fa fa-chair me-1"></i> Meja '.$o['nomor_meja'] : '<i class="fa fa-shopping-bag text-warning me-1"></i> Takeaway' ?>
                                    </span>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-warning text-dark"><?= strtoupper($o['status_order']) ?></span>
                                        
                                        <form method="POST" onsubmit="return confirm('Batalkan orderan ini? Meja akan otomatis dikosongkan.')">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="id_pesanan" value="<?= $o['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm py-0"><i class="fa fa-times"></i></button>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title text-primary mb-0"><?= $o['kode_pesanan'] ?></h5>
                                        <small class="text-muted"><?= date('H:i', strtotime($o['created_at'] ?? 'now')) ?></small>
                                    </div>
                                    <p class="card-text mb-1 fw-bold text-dark"><i class="fa fa-user-circle me-2 text-muted"></i><?= $o['nama_pelanggan'] ?></p>
                                    <hr>
                                    <h4 class="text-end text-dark mb-4">Total: <span class="text-primary">Rp <?= number_format($o['total_tagihan']) ?></span></h4>
                                    
                                    <form method="POST" class="bg-light p-3 rounded border">
                                        <input type="hidden" name="action" value="bayar">
                                        <input type="hidden" name="id_pesanan" value="<?= $o['id'] ?>">
                                        
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="small fw-bold text-muted mb-1">Metode Bayar</label>
                                                <select name="metode" class="form-select form-select-sm">
                                                    <option value="Tunai">Tunai</option>
                                                    <option value="QRIS">QRIS</option>
                                                    <option value="Debit">Debit</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="small fw-bold text-muted mb-1">Nominal Bayar</label>
                                                <input type="number" name="jumlah_bayar" class="form-control form-control-sm" placeholder="Rp..." required min="0">
                                            </div>
                                        </div>
                                        <button class="btn btn-success btn-sm w-100 fw-bold shadow-sm">
                                            <i class="fa fa-check-circle me-1"></i> PROSES PEMBAYARAN
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-index">
                    <div class="card border-0 shadow">
                        <div class="card-body">
                            <h4 class="text-primary mb-3"><i class="fa fa-tachometer-alt me-2"></i>Database Performance Analyzer</h4>
                            <p class="text-muted">Gunakan fitur ini untuk melihat bagaimana database melakukan pencarian data menu.</p>
                            <form method="GET" class="d-flex gap-2 mb-3">
                                <input type="text" name="analyze" class="form-control" 
                                    placeholder="Cari nama menu..." 
                                    value="<?= htmlspecialchars($_GET['analyze'] ?? '') ?>" required>
                                <button class="btn btn-dark fw-bold px-4">RUN EXPLAIN ANALYZE</button>
                                <?php if(isset($_GET['analyze'])): ?>
                                    <a href="order.php" class="btn btn-outline-secondary">
                                        <i class="fa fa-sync"></i> Reset
                                    </a>
                                <?php endif; ?>
                            </form>
                            <?php if($explain): ?>
                                <div class="bg-dark text-warning p-3 rounded font-monospace" style="font-size: 0.85rem; overflow-x: auto; border: 3px solid #fea116;">
                                    <div class="mb-2 border-bottom border-secondary pb-1 text-white fw-bold">PostgreSQL Query Execution Plan:</div>
                                    <pre class="m-0"><?= $explain ?></pre>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const inputPelanggan = document.getElementById('input_pelanggan');
        const hiddenPelanggan = document.getElementById('hidden_pelanggan_id');
        const listPelanggan = document.getElementById('list_pelanggan');

        const inputMeja = document.getElementById('input_meja');
        const hiddenMeja = document.getElementById('hidden_me_id');
        const listMeja = document.getElementById('list_meja');
        
        const tipeDineIn = document.getElementById('tipe_dinein');
        const tipeTakeaway = document.getElementById('tipe_takeaway');
        const containerMeja = document.getElementById('container_meja');

        function toggleMejaField() {
            if (tipeTakeaway.checked) {
                containerMeja.style.opacity = '0.4';
                inputMeja.disabled = true;
                inputMeja.value = "";
                document.getElementById('hidden_meja_id').value = "";
                inputMeja.required = false;
            } else {
                containerMeja.style.opacity = '1';
                inputMeja.disabled = false;
                inputMeja.required = true;
            }
        }

        tipeDineIn.addEventListener('change', toggleMejaField);
        tipeTakeaway.addEventListener('change', toggleMejaField);

        inputPelanggan.addEventListener('input', function() {
            const val = this.value;
            let found = false;
            for (let i = 0; i < listPelanggan.options.length; i++) {
                if (listPelanggan.options[i].value === val) {
                    hiddenPelanggan.value = listPelanggan.options[i].getAttribute('data-id'); found = true; break;
                }
            }
            if(!found) hiddenPelanggan.value = "";
        });

        inputMeja.addEventListener('input', function() {
            const val = this.value;
            let found = false;
            for (let i = 0; i < listMeja.options.length; i++) {
                if (listMeja.options[i].value === val) {
                    document.getElementById('hidden_meja_id').value = listMeja.options[i].getAttribute('data-id'); 
                    found = true; break;
                }
            }
            if(!found) document.getElementById('hidden_meja_id').value = "";
        });

        const searchInput = document.getElementById('searchMenu');
        const menuItems = document.querySelectorAll('.menu-item');
        const noMenuFound = document.getElementById('noMenuFound');

        searchInput.addEventListener('keyup', function(e) {
            const term = e.target.value.toLowerCase();
            let visibleCount = 0;
            menuItems.forEach(function(item) {
                const name = item.getAttribute('data-name');
                if(name.includes(term)) { item.style.display = 'block'; visibleCount++; } else { item.style.display = 'none'; }
            });
            noMenuFound.style.display = visibleCount === 0 ? 'block' : 'none';
        });

        document.getElementById('orderForm').addEventListener('submit', function(e){
            if(hiddenPelanggan.value === "") { 
                e.preventDefault(); 
                alert("Harap pilih Pelanggan dari daftar yang tersedia!"); 
                inputPelanggan.focus(); 
                return; 
            }
            
            if(tipeDineIn.checked && document.getElementById('hidden_meja_id').value === "") { 
                e.preventDefault(); 
                alert("Harap pilih Nomor Meja dari daftar untuk pesanan Dine-In!"); 
                inputMeja.focus(); 
                return; 
            }

            let hasItem = false;
            const qtys = document.getElementsByName('qty[]');
            for(let q of qtys) { if(parseInt(q.value) > 0) { hasItem = true; break; } }
            if(!hasItem) {
                e.preventDefault();
                alert("Pilih minimal 1 menu dengan jumlah lebih dari 0!");
            }
        });

        toggleMejaField();
    </script>
</body>
</html>