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
    </style>
</head>
<body>
    <div class="container-fluid bg-white p-0">
        <?php include "navbar.php"; ?>
        
        <div class="container-fluid py-5 bg-dark hero-header mb-5">
            <div class="container text-center my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3">Order System</h1>
                <p class="text-white">Kelola pesanan pelanggan</p>
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
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-order"><i class="fa fa-cash-register me-2"></i>Input Order</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-kitchen"><i class="fa fa-fire me-2"></i>Kitchen / Payment</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-index"><i class="fa fa-database me-2"></i>Indexing Demo</button></li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                
                <div class="tab-pane fade show active" id="tab-order">
                    <div class="row g-4">
                        <div class="col-lg-8 mx-auto">
                            <div class="card border-0 shadow-lg">
                                <div class="card-header bg-primary text-white"><h5 class="mb-0 text-white">Create New Order</h5></div>
                                <div class="card-body p-4">
                                    <form method="POST" id="orderForm">
                                        <input type="hidden" name="action" value="create">
                                        
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <label class="fw-bold mb-1">Cari Pelanggan</label>
                                                <input type="text" class="form-control" list="list_pelanggan" id="input_pelanggan" placeholder="Ketik nama..." autocomplete="off" required>
                                                <datalist id="list_pelanggan">
                                                    <?php foreach($pelanggan as $p): ?>
                                                        <option data-id="<?= $p['id'] ?>" value="<?= htmlspecialchars($p['nama_pelanggan']) ?> (<?= $p['no_telepon'] ?>)">
                                                    <?php endforeach; ?>
                                                </datalist>
                                                <input type="hidden" name="pelanggan_id" id="hidden_pelanggan_id">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="fw-bold mb-1">Cari Meja Kosong</label>
                                                <input type="text" class="form-control" list="list_meja" id="input_meja" placeholder="Ketik nomor meja..." autocomplete="off" required>
                                                <datalist id="list_meja">
                                                    <?php foreach($meja as $m): ?>
                                                        <option data-id="<?= $m['id'] ?>" value="Meja <?= $m['nomor_meja'] ?> (Kap: <?= $m['kapasitas'] ?>)">
                                                    <?php endforeach; ?>
                                                </datalist>
                                                <input type="hidden" name="meja_id" id="hidden_meja_id">
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                            <h6 class="m-0">Pilih Menu</h6>
                                            <div class="search-container" style="width: 250px;">
                                                <input type="text" id="searchMenu" class="form-control form-control-sm" placeholder="Cari menu...">
                                                <i class="fa fa-search search-icon"></i>
                                            </div>
                                        </div>

                                        <div class="row g-3" id="menuContainer" style="max-height: 400px; overflow-y: auto;">
                                            <?php foreach($activeMenus as $m): 
                                                $imgSrc = (!empty($m['gambar']) && file_exists('../uploads/'.$m['gambar'])) ? '../uploads/'.$m['gambar'] : '../img/food-default.jpeg';
                                            ?>
                                            <div class="col-md-6 menu-item" data-name="<?= strtolower($m['nama_menu']) ?>">
                                                <div class="d-flex align-items-center border rounded p-2 bg-light h-100">
                                                    <img src="<?= $imgSrc ?>" class="menu-card-img shadow-sm">
                                                    <div class="ms-3 flex-grow-1">
                                                        <div class="fw-bold text-dark"><?= $m['nama_menu'] ?></div>
                                                        <div class="text-primary small fw-bold">Rp <?= number_format($m['harga']) ?></div>
                                                    </div>
                                                    <div class="text-end">
                                                        <input type="hidden" name="menu_id[]" value="<?= $m['id'] ?>">
                                                        <input type="number" name="qty[]" class="form-control form-control-sm border-primary text-center fw-bold" style="width: 60px;" placeholder="0" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                            <div id="noMenuFound" class="col-12 text-center text-muted py-3" style="display: none;">Menu tidak ditemukan.</div>
                                        </div>

                                        <div class="d-grid mt-4">
                                            <button type="submit" class="btn btn-primary py-3 fw-bold">Submit Order</button>
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
                            <div class="col-12 text-center text-muted py-5"><h4>Tidak ada pesanan pending.</h4></div>
                        <?php endif; ?>

                        <?php foreach($orders as $o): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-dark text-white d-flex justify-content-between">
                                    <span>Meja <?= $o['nomor_meja'] ?></span>
                                    <span class="badge bg-warning text-dark"><?= strtoupper($o['status_order']) ?></span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title text-primary"><?= $o['kode_pesanan'] ?></h5>
                                    <p class="card-text mb-1"><i class="fa fa-user me-2"></i><?= $o['nama_pelanggan'] ?></p>
                                    <h4 class="text-end my-3">Total: Rp <?= number_format($o['total_tagihan']) ?></h4>
                                    
                                    <form method="POST" class="bg-light p-3 rounded">
                                        <input type="hidden" name="action" value="bayar">
                                        <input type="hidden" name="id_pesanan" value="<?= $o['id'] ?>">
                                        
                                        <label class="small text-muted mb-1">Metode & Nominal</label>
                                        <div class="input-group input-group-sm mb-2">
                                            <select name="metode" class="form-select">
                                                <option value="Tunai">Tunai</option>
                                                <option value="QRIS">QRIS</option>
                                                <option value="Debit">Debit</option>
                                            </select>
                                        </div>
                                        
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="jumlah_bayar" class="form-control" placeholder="Bayar..." required min="0">
                                            <button class="btn btn-success fw-bold">BAYAR</button>
                                        </div>
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
                            <h4 class="text-primary mb-3">Database Indexing Performance</h4>
                            <form method="GET" class="d-flex gap-2 mb-3">
                                <input type="text" name="analyze" class="form-control" placeholder="Cari nama menu..." required>
                                <button class="btn btn-dark">Run EXPLAIN ANALYZE</button>
                            </form>
                            <?php if($explain): ?>
                                <div class="bg-dark text-warning p-3 rounded font-monospace" style="font-size: 0.85rem; overflow-x: auto;">
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

        const inputMeja = document.getElementById('input_meja');
        const hiddenMeja = document.getElementById('hidden_meja_id');
        const listMeja = document.getElementById('list_meja');

        inputMeja.addEventListener('input', function() {
            const val = this.value;
            let found = false;
            for (let i = 0; i < listMeja.options.length; i++) {
                if (listMeja.options[i].value === val) {
                    hiddenMeja.value = listMeja.options[i].getAttribute('data-id'); found = true; break;
                }
            }
            if(!found) hiddenMeja.value = "";
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
            if(visibleCount === 0) noMenuFound.style.display = 'block'; else noMenuFound.style.display = 'none';
        });

        document.getElementById('orderForm').addEventListener('submit', function(e){
            if(hiddenPelanggan.value === "") { e.preventDefault(); alert("Pilih Pelanggan dari list!"); inputPelanggan.focus(); return; }
            if(hiddenMeja.value === "") { e.preventDefault(); alert("Pilih Meja dari list!"); inputMeja.focus(); return; }
        });
    </script>
</body>
</html>