<?php
session_start();
require_once '../models/menuModel.php';
$model = new MenuModel();

function uploadFoto() {
    $namaFile   = $_FILES['foto']['name'];
    $ukuranFile = $_FILES['foto']['size'];
    $error      = $_FILES['foto']['error'];
    $tmpName    = $_FILES['foto']['tmp_name'];

    if ($error === 4) { return false; }
    $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));

    if (!in_array($ekstensiGambar, $ekstensiGambarValid)) {
        echo "<script>alert('Yang anda upload bukan gambar!');</script>";
        return false;
    }
    if ($ukuranFile > 2000000) {
        echo "<script>alert('Ukuran gambar terlalu besar!');</script>";
        return false;
    }
    $namaFileBaru = uniqid() . '.' . $ekstensiGambar;
    move_uploaded_file($tmpName, '../uploads/' . $namaFileBaru);
    return '../uploads/' . $namaFileBaru;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_menu'];
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $id_kategori = $_POST['id_kategori'];
    $statusmenu = $_POST['statusmenu'];
    $deskripsi = $_POST['deskripsi'];
    
    $foto_lama = $_POST['foto_lama']; 
    $foto_url = $foto_lama; 

    if ($_FILES['foto']['error'] !== 4) {
        $uploadBaru = uploadFoto();
        if ($uploadBaru) {
            $foto_url = $uploadBaru; 
            
            if ($foto_lama != "" && file_exists($foto_lama)) {
                unlink($foto_lama);
            }
        }
    }

    if ($id == "") {
        $res = $model->create($nama, $harga, $id_kategori, $statusmenu, $deskripsi, $foto_url);
        $_SESSION['notif'] = $res ? "Data berhasil DITAMBAHKAN!" : "Gagal menambah data.";
    } else {
        $res = $model->update($id, $nama, $harga, $id_kategori, $statusmenu, $deskripsi, $foto_url);
        $_SESSION['notif'] = $res ? "Data berhasil DIUPDATE!" : "Gagal update data.";
    }
    
    header("Location: menu.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = $_GET['id'];
    $dataMenu = $model->readById($id);
    
    if ($dataMenu) {
        $pathFoto = $dataMenu['foto_url'];
        if ($pathFoto != "" && file_exists($pathFoto)) { unlink($pathFoto); }
    }

    $res = $model->delete($id); 
    
    $_SESSION['notif'] = $res ? "Data Menu beserta riwayat pesanannya berhasil DIHAPUS!" : "Gagal menghapus data.";
    
    $redirectUrl = "menu.php";
    if(isset($_GET['search'])) {
        $redirectUrl .= "?search=" . urlencode($_GET['search']);
    }
    header("Location: " . $redirectUrl);
    exit;
}

$kategoriList = $model->getKategori();
$allMenu = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    $allMenu = $model->search($searchKeyword);
} else {
    $allMenu = $model->read();
}

$menuBreakfast = array_filter($allMenu, function($var) { return $var['id_kategori'] == 1; });
$menuLunch     = array_filter($allMenu, function($var) { return $var['id_kategori'] == 2; });
$menuDinner    = array_filter($allMenu, function($var) { return $var['id_kategori'] == 3; });
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Menu Manager Page</title>
    <?php include "header.php"; ?>
</head>
<body>
    <div class="container-fluid bg-white p-0">
        <div class="container-fluid p-0">
            <?php include "navbar.php"; ?>
            <div class="container-fluid py-5 bg-dark hero-header mb-5">
                <div class="container text-center my-5 pt-5 pb-4">
                    <h1 class="display-3 text-white mb-3">Food Menu</h1>
                </div>
            </div>
        </div>

        <div class="container-fluid py-5">
            <div class="container">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h5 class="section-title ff-secondary text-center text-primary fw-normal">Food Menu</h5>
                    <h1 class="mb-3">Most Popular Items</h1>
                    <button class="btn btn-success mb-5" onclick="showTambah()"><i class="fa fa-plus"></i> Tambah Menu Baru</button>
                </div>
                
                <div class="row mb-4 wow fadeInUp"><div class="col-md-6 mx-auto">
                    <form method="GET" action="menu.php" class="d-flex">
                        <input type="text" class="form-control me-2" name="search" placeholder="Cari..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </form>
                </div></div>
                
                <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">
                    <ul class="nav nav-pills d-inline-flex justify-content-center border-bottom mb-5">
                            <li class="nav-item"><a class="d-flex align-items-center text-start mx-3 ms-0 pb-3 active" data-bs-toggle="pill" href="#tab-1"><h6 class="mt-n1 mb-0">Breakfast</h6></a></li>
                            <li class="nav-item"><a class="d-flex align-items-center text-start mx-3 pb-3" data-bs-toggle="pill" href="#tab-2"><h6 class="mt-n1 mb-0">Lunch</h6></a></li>
                            <li class="nav-item"><a class="d-flex align-items-center text-start mx-3 me-0 pb-3" data-bs-toggle="pill" href="#tab-3"><h6 class="mt-n1 mb-0">Dinner</h6></a></li>
                    </ul>

                    <div class="tab-content">
                        <?php 
                        function renderMenuTab($items, $tabId, $isActive = false) {
                            $activeClass = $isActive ? 'active' : '';
                            echo '<div id="'.$tabId.'" class="tab-pane fade show p-0 '.$activeClass.'">';
                            if(empty($items)) {
                                echo '<div class="alert alert-info text-center">Tidak ada menu.</div>';
                            } else {
                                echo '<div class="row g-4">';
                                foreach($items as $item): 
                                    $imgSrc = !empty($item['foto_url']) ? htmlspecialchars($item['foto_url']) : '../img/menu-1.jpg';

                                $isAvailable = ($item['statusmenu'] == 't' || $item['statusmenu'] == 'true');
                                $statusBadge = $isAvailable 
                                    ? '<span class="badge bg-success position-absolute top-0 start-0 m-2">Tersedia</span>' 
                                    : '<span class="badge bg-danger position-absolute top-0 start-0 m-2">Habis</span>';

                                $imgClass = $isAvailable ? '' : 'opacity-50';
                                ?>
                                    <div class="col-lg-6">
                                        <div class="d-flex align-items-center position-relative">
                                            <?= $statusBadge ?>
                                            
                                            <img class="flex-shrink-0 img-fluid rounded <?= $imgClass ?>" src="<?= $imgSrc; ?>" alt="" style="width: 80px; height: 80px; object-fit: cover;">
                                            <div class="w-100 d-flex flex-column text-start ps-4">
                                                <h5 class="d-flex justify-content-between border-bottom pb-2">
                                                    <span><?= htmlspecialchars($item['nama_menu']); ?></span>
                                                    <span class="text-primary">Rp <?= number_format($item['harga'], 0, ',', '.'); ?></span>
                                                </h5>
                                                <small class="fst-italic"><?= htmlspecialchars($item['deskripsi']); ?></small>
                                                
                                                <div class="mt-2">
                                                    <button class="btn btn-sm btn-outline-primary me-2" 
                                                        onclick="showEdit(
                                                        '<?= $item['id_menu']; ?>',
                                                        '<?= htmlspecialchars($item['nama_menu'], ENT_QUOTES); ?>',
                                                        '<?= $item['harga']; ?>',
                                                        '<?= $item['id_kategori']; ?>',
                                                        '<?= $item['statusmenu']; ?>',
                                                        '<?= htmlspecialchars($item['foto_url'], ENT_QUOTES); ?>',
                                                        <?= htmlspecialchars(json_encode($item['deskripsi']), ENT_QUOTES); ?>)">Edit
                                                    </button>
                                                    
                                                    <a href="menu.php?action=delete&id=<?= $item['id_menu']; ?>" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('...')">Delete</a> </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                endforeach;
                                echo '</div>';
                            }
                            echo '</div>';
                        }
                        renderMenuTab($menuBreakfast, "tab-1", true);
                        renderMenuTab($menuLunch, "tab-2");
                        renderMenuTab($menuDinner, "tab-3");
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <div class="modal fade" id="formModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalTitle">Form Menu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_menu" id="id_menu"><input type="hidden" name="foto_lama" id="foto_lama">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" class="form-control" name="nama" id="nama" required>
                    </div>
                    <div class="mb-3">
                        <label>Harga</label>
                        <input type="number" class="form-control" name="harga" id="harga" required>
                    </div>
                    <div class="mb-3">
                        <label>Kategori</label>
                        <select class="form-control" name="id_kategori" id="id_kategori" required>
                            <option value="">Pilih</option>
                            <?php foreach($kategoriList as $kategori): ?>
                                <option value="<?php echo $kategori['id_kategori']; ?>">
                                    <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="mb-3" id="div_status_menu">
                        <label>Status</label>
                        <select class="form-control" name="statusmenu" id="statusmenu">
                            <option value="true">Tersedia</option>
                            <option value="false">Habis</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Foto</label>
                        <input type="file" class="form-control" name="foto" id="foto">
                        <div id="preview_image" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi"></textarea>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
            </form>
        </div></div>
    </div>

    <script>
    function showTambah() {
        document.getElementById("modalTitle").innerText = "Tambah Menu Baru";
        document.getElementById("id_menu").value = "";
        document.getElementById("nama").value = "";
        document.getElementById("harga").value = "";
        document.getElementById("id_kategori").value = "";
        
        document.getElementById("div_status_menu").style.display = "none";
        document.getElementById("statusmenu").value = "true"; 
        
        document.getElementById("deskripsi").value = "";
        document.getElementById("foto").value = ""; 
        document.getElementById("preview_image").innerHTML = "";
        
        var myModal = new bootstrap.Modal(document.getElementById('formModal')); 
        myModal.show();
    }

    function showEdit(id, nama, harga, kategori, status, foto_path, deskripsi) {
        document.getElementById("modalTitle").innerText = "Edit Menu";
        document.getElementById("id_menu").value = id;
        document.getElementById("nama").value = nama;
        document.getElementById("harga").value = harga;
        document.getElementById("id_kategori").value = kategori;
        
        document.getElementById("div_status_menu").style.display = "block";
        
        let statusVal = (status === "t" || status === "true" || status === true) ? "true" : "false";
        document.getElementById("statusmenu").value = statusVal;
        
        document.getElementById("deskripsi").value = deskripsi;
        document.getElementById("foto_lama").value = foto_path;
        var myModal = new bootstrap.Modal(document.getElementById('formModal'));
        myModal.show();
    }
    document.getElementById('foto').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview_image').innerHTML = 
                    '<img src="' + e.target.result + '" alt="Preview" style="max-width: 100%; max-height: 150px;">';
            }
            reader.readAsDataURL(file);
        }
    });
    </script>

    <?php if (isset($_SESSION['notif'])): ?>
    <script>
        alert("<?php echo $_SESSION['notif']; ?>");
    </script>
    <?php 
        unset($_SESSION['notif']); 
    ?>
    <?php endif; ?>

</body>
</html>