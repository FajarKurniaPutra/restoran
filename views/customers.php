<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../models/CustomerModel.php';
$model = new CustomerModel();

if (isset($_POST['add_customer'])) {
    $nama = $_POST['nama_pelanggan'];
    $telp = $_POST['no_telepon'];
    if($model->addCustomer($nama, $telp)){
        echo "<script>alert('Pelanggan berhasil ditambahkan!'); window.location='customers.php';</script>";
    }
}

if (isset($_POST['edit_customer'])) {
    $id = $_POST['id_pelanggan'];
    $nama = $_POST['nama_pelanggan'];
    $telp = $_POST['no_telepon'];
    if($model->updateCustomer($id, $nama, $telp)){
        echo "<script>alert('Data pelanggan berhasil diupdate!'); window.location='customers.php';</script>";
    }
}

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    if($model->deleteCustomer($id)){
        echo "<script>alert('Pelanggan berhasil dihapus (Soft Delete)!'); window.location='customers.php';</script>";
    }
}

$search = $_GET['search'] ?? '';
$customers = $model->getAllCustomers($search);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Data Pelanggan</title>
    <?php include "header.php"; ?>
</head>
<body>
    <div class="container-fluid bg-white p-0">
        <?php include "navbar.php"; ?>
        
        <div class="container-fluid py-5 bg-dark hero-header mb-5">
            <div class="container text-center my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3">Master Pelanggan</h1>
                <p class="text-white">Kelola data pembeli restoran Anda</p>
            </div>
        </div>

        <div class="container mb-5">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 text-white"><i class="fa fa-users me-2"></i>Daftar Pelanggan Aktif</h4>
                    <button class="btn btn-light text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fa fa-plus me-1"></i> Tambah Baru
                    </button>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-2 mb-4 justify-content-center">
                        <div class="col-md-6"> <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Cari nama atau no. telepon..." value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-dark" type="submit"><i class="fa fa-search"></i> Cari</button>
                                <?php if(!empty($search)): ?>
                                    <a href="customers.php" class="btn btn-outline-secondary" title="Reset Search"><i class="fa fa-times"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Pelanggan</th>
                                    <th>No. Telepon</th>
                                    <th>Terdaftar Sejak</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($customers)): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">
                                        <?php if(!empty($search)): ?>
                                            Data tidak ditemukan untuk kata kunci: "<strong><?= htmlspecialchars($search) ?></strong>"
                                        <?php else: ?>
                                            Belum ada data pelanggan.
                                        <?php endif; ?>
                                    </td></tr>
                                <?php else: ?>
                                    <?php foreach($customers as $key => $c): ?>
                                    <tr>
                                        <td><?= $key + 1 ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($c['nama_pelanggan']) ?></td>
                                        <td>
                                            <?php if($c['no_telepon']): ?>
                                                <i class="fa fa-phone text-muted me-1"></i> <?= htmlspecialchars($c['no_telepon']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $c['created_at'] ? date('d M Y', strtotime($c['created_at'])) : '-' ?>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-info me-1 btn-edit" 
                                                data-id="<?= $c['id'] ?>"
                                                data-nama="<?= htmlspecialchars($c['nama_pelanggan']) ?>"
                                                data-telp="<?= htmlspecialchars($c['no_telepon']) ?>"
                                                data-bs-toggle="modal" data-bs-target="#editModal">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            
                                            <a href="?delete_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Tambah Pelanggan Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pelanggan" class="form-control" required placeholder="Contoh: Budi Santoso">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" name="no_telepon" class="form-control" placeholder="Contoh: 08123456789">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_customer" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Edit Data Pelanggan</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_pelanggan" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pelanggan" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" name="no_telepon" id="edit_telp" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_customer" class="btn btn-info text-white">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function(){
            var editButtons = document.querySelectorAll('.btn-edit');
            editButtons.forEach(function(button){
                button.addEventListener('click', function(){
                    var id = this.getAttribute('data-id');
                    var nama = this.getAttribute('data-nama');
                    var telp = this.getAttribute('data-telp');

                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_nama').value = nama;
                    document.getElementById('edit_telp').value = telp;
                });
            });
        });
    </script>
</body>
</html>