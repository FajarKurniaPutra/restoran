<?php
    require_once '../controllers/MenuController.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Menu Manager</title>
    <?php include "header.php"; ?>
    <style>
        .menu-img { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; }
        .page-link { color: #FEA116; border-color: #FEA116; } 
        .page-item.active .page-link { background-color: #FEA116; border-color: #FEA116; color: white; }
        .page-item.disabled .page-link { color: #ccc; border-color: #dee2e6; }
    </style>
</head>
<body>
    <div class="container-fluid bg-white p-0">
        <?php include "navbar.php"; ?>
        
        <div class="container-fluid py-5 bg-dark hero-header mb-5">
            <div class="container text-center my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3">Food Menu</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center text-uppercase">
                        <li class="breadcrumb-item text-white">Home</li>
                        <li class="breadcrumb-item text-white active">Menu</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container-fluid py-5">
            <div class="container">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h5 class="section-title ff-secondary text-center text-primary fw-normal">Food Menu</h5>
                    <h1 class="mb-5">Daftar Hidangan</h1>
                </div>

                <div class="row mb-4 wow fadeInUp">
                    <div class="col-md-6 mb-2">
                        <button class="btn btn-primary px-4 py-2" onclick="showTambah()">
                            <i class="fa fa-plus me-2"></i>Tambah Menu
                        </button>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" class="d-flex">
                            <input type="hidden" name="kategori" value="<?= $activeKategori ?>">
                            <input type="text" name="search" class="form-control me-2" placeholder="Cari nama menu..." value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-dark" type="submit">Cari</button>
                        </form>
                    </div>
                </div>

                <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">
                    <ul class="nav nav-pills d-inline-flex justify-content-center border-bottom mb-5">
                        <li class="nav-item">
                            <a class="d-flex align-items-center text-start mx-3 ms-0 pb-3 <?= $activeKategori == 1 ? 'active' : '' ?>" 
                                href="?kategori=1&search=<?= urlencode($search) ?>">
                                <i class="fa fa-utensils fa-2x me-2"></i><h6 class="mt-n1 mb-0">Main Course</h6>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="d-flex align-items-center text-start mx-3 pb-3 <?= $activeKategori == 2 ? 'active' : '' ?>" 
                                href="?kategori=2&search=<?= urlencode($search) ?>">
                                <i class="fa fa-coffee fa-2x me-2"></i><h6 class="mt-n1 mb-0">Beverage</h6>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="d-flex align-items-center text-start mx-3 me-0 pb-3 <?= $activeKategori == 3 ? 'active' : '' ?>" 
                                href="?kategori=3&search=<?= urlencode($search) ?>">
                                <i class="fa fa-hamburger fa-2x me-2"></i><h6 class="mt-n1 mb-0">Snack</h6>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active p-0">
                            <div class="row g-4">
                                <?php if (empty($allMenu)): ?>
                                    <div class="col-12"><div class="alert alert-light py-4">Tidak ada data di kategori ini.</div></div>
                                <?php else: ?>
                                    <?php foreach ($allMenu as $m): 
                                        $imgSrc = (!empty($m['gambar']) && file_exists('../uploads/' . $m['gambar'])) ? '../uploads/' . $m['gambar'] : '../img/food-default.jpeg';
                                        $isReady = ($m['is_available'] == 't' || $m['is_available'] == 1);
                                        $badge = $isReady 
                                            ? '<span class="badge bg-success position-absolute top-0 start-0 m-2">Ready</span>' 
                                            : '<span class="badge bg-danger position-absolute top-0 start-0 m-2">Sold Out</span>';
                                        $jsonItem = htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <div class="col-lg-6">
                                        <div class="d-flex align-items-center position-relative shadow-sm rounded p-3 bg-light h-100">
                                            <?= $badge ?>
                                            <img class="flex-shrink-0 img-fluid rounded menu-img" src="<?= $imgSrc ?>" alt="">
                                            <div class="w-100 d-flex flex-column text-start ps-4">
                                                <h5 class="d-flex justify-content-between border-bottom pb-2">
                                                    <span><?= htmlspecialchars($m['nama_menu']) ?></span>
                                                    <span class="text-primary">Rp <?= number_format($m['harga'], 0, ',', '.') ?></span>
                                                </h5>
                                                <small class="fst-italic text-muted mb-2"><?= htmlspecialchars($m['deskripsi']) ?></small>
                                                
                                                <div class="mt-auto pt-2">
                                                    <button type="button" class="btn btn-sm btn-outline-warning me-1" onclick="edit(<?= $jsonItem ?>)">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </button>
                                                    <a href="menu.php?action=delete&id=<?= $m['id'] ?>&kategori=<?= $activeKategori ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus menu ini?')">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if($totalPages > 1): ?>
                    <div class="col-12 mt-5">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page-1 ?>&kategori=<?= $activeKategori ?>&search=<?= urlencode($search) ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                
                                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&kategori=<?= $activeKategori ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?page=<?= $page+1 ?>&kategori=<?= $activeKategori ?>&search=<?= urlencode($search) ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <small class="text-muted">Halaman <?= $page ?> dari <?= $totalPages ?> (Total <?= $totalData ?> Menu)</small>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTitle">Form Menu</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="id_menu" id="id_menu">
                            <input type="hidden" name="foto_lama" id="foto_lama">
                            <input type="hidden" name="redirect_kategori" value="<?= $activeKategori ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Menu</label>
                                <input type="text" name="nama" id="nama" class="form-control" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Harga</label>
                                    <input type="number" name="harga" id="harga" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Kategori</label>
                                    <select name="id_kategori" id="id_kategori" class="form-select" required>
                                        <option value="">-- Pilih --</option>
                                        <?php foreach($kategoriList as $k): ?>
                                            <option value="<?= $k['id'] ?>"><?= $k['nama_kategori'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Status Ketersediaan</label>
                                <select name="statusmenu" id="statusmenu" class="form-select">
                                    <option value="true">Tersedia (Ready)</option>
                                    <option value="false">Habis (Sold Out)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Deskripsi</label>
                                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Foto Menu</label>
                                <input type="file" name="foto" id="foto" class="form-control" accept="image/jpg, image/jpeg, image/png, image/gif">
                                <div class="form-text small">Format: JPG, PNG, GIF (Max 2MB). Biarkan kosong jika tidak ingin mengubah foto.</div>
                                
                                <div id="preview-container" class="mt-3 text-center border p-2 bg-light rounded" style="display:none;">
                                    <p class="mb-1 small text-muted">Preview:</p>
                                    <img id="img-preview" src="" style="max-height: 120px; max-width: 100%; border-radius: 5px;">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/main.js"></script>

    <script>
        var modalElement = document.getElementById('formModal');
        var myModal = new bootstrap.Modal(modalElement);

        function showTambah() {
            document.getElementById('modalTitle').innerText = "Tambah Menu Baru";
            
            document.getElementById('id_menu').value = "";
            document.getElementById('nama').value = "";
            document.getElementById('harga').value = "";
            document.getElementById('deskripsi').value = "";
            document.getElementById('id_kategori').value = "<?= $activeKategori ?>";
            document.getElementById('statusmenu').value = "true";
            document.getElementById('foto_lama').value = "";
            document.getElementById('foto').value = "";
            
            document.getElementById('preview-container').style.display = 'block';
            document.getElementById('img-preview').src = "../img/food-default.jpeg";
            
            myModal.show();
        }

        function edit(data) {
            document.getElementById('modalTitle').innerText = "Edit Menu";
            
            document.getElementById('id_menu').value = data.id;
            document.getElementById('nama').value = data.nama_menu;
            document.getElementById('harga').value = data.harga;
            document.getElementById('deskripsi').value = data.deskripsi;
            document.getElementById('id_kategori').value = data.kategori_id;
            
            let statusVal = (data.is_available === 't' || data.is_available === true) ? 'true' : 'false';
            document.getElementById('statusmenu').value = statusVal;
            
            document.getElementById('foto_lama').value = data.gambar;
            document.getElementById('foto').value = "";

            let imgSrc = "../img/food-default.jpeg";
            if (data.gambar) {
                imgSrc = "../uploads/" + data.gambar;
            }
            
            document.getElementById('preview-container').style.display = 'block';
            document.getElementById('img-preview').src = imgSrc;
            
            myModal.show();
        }

        document.getElementById('foto').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-container').style.display = 'block';
                    document.getElementById('img-preview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

    <?php if(isset($_SESSION['notif'])): ?>
    <script>
        alert("<?= $_SESSION['notif'] ?>");
    </script>
    <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

</body>
</html>