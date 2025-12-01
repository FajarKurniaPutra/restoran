<!DOCTYPE html>
<html lang="id">
<head>
    <title><?= $isEdit ? 'Edit Menu' : 'Tambah Menu' ?></title>
    <?php include "header.php"; ?>
</head>
<body>
    <div class="container-fluid bg-white p-0">
        <?php include "navbar.php"; ?>

        <div class="container py-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= $isEdit ? 'Edit Menu' : 'Tambah Menu Baru' ?></h4>
                </div>
                <div class="card-body">
                    
                    <form method="POST" enctype="multipart/form-data" 
                          action="index.php?page=menu&action=<?= $isEdit ? 'update&id='.$menu['id_menu'] : 'store' ?>">
                        
                        <?php if($isEdit): ?>
                            <input type="hidden" name="existing_foto" value="<?= $menu['foto_url'] ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Menu</label>
                                    <input type="text" class="form-control" name="nama_menu" 
                                           value="<?= $isEdit ? htmlspecialchars($menu['nama_menu']) : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Harga</label>
                                    <input type="number" class="form-control" name="harga" 
                                           value="<?= $isEdit ? $menu['harga'] : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <select class="form-select" name="id_kategori">
                                        <option value="">-- Pilih Kategori --</option>
                                        <?php foreach ($kategories as $kat): ?>
                                            <?php 
                                                $selected = ($isEdit && $menu['id_kategori'] == $kat['id_kategori']) ? 'selected' : ''; 
                                            ?>
                                            <option value="<?= $kat['id_kategori'] ?>" <?= $selected ?>>
                                                <?= htmlspecialchars($kat['nama_kategori']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="statusmenu">
                                        <option value="t" <?= ($isEdit && $menu['statusmenu'] == 't') ? 'selected' : '' ?>>Tersedia</option>
                                        <option value="f" <?= ($isEdit && $menu['statusmenu'] == 'f') ? 'selected' : '' ?>>Habis</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Gambar</label>
                                    <input type="file" class="form-control" name="foto">
                                    <?php if($isEdit && !empty($menu['foto_url'])): ?>
                                        <div class="mt-2">
                                            <img src="<?= $menu['foto_url'] ?>" width="100" class="rounded">
                                            <small class="d-block text-muted">Gambar saat ini</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea class="form-control" name="deskripsi" rows="4"><?= $isEdit ? htmlspecialchars($menu['deskripsi']) : '' ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Simpan Data
                            </button>
                            <a href="index.php?page=menu" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <?php include "footer.php"; ?>
    </div>
</body>
</html>