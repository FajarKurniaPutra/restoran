<?php
require_once '../models/menuModel.php';
$model = new MenuModel();

// --- FUNGSI BANTUAN UPLOAD ---
function uploadFoto() {
    $namaFile   = $_FILES['foto']['name'];
    $ukuranFile = $_FILES['foto']['size'];
    $error      = $_FILES['foto']['error'];
    $tmpName    = $_FILES['foto']['tmp_name'];

    // Cek apakah ada file yang diupload
    if ($error === 4) {
        return false; 
    }

    // Cek ekstensi file valid
    $ekstensiGambarValid = ['jpg', 'jpeg', 'png'];
    $ekstensiGambar = explode('.', $namaFile);
    $ekstensiGambar = strtolower(end($ekstensiGambar));

    if (!in_array($ekstensiGambar, $ekstensiGambarValid)) {
        echo "<script>alert('Yang anda upload bukan gambar!');</script>";
        return false;
    }

    // Cek ukuran file (Max 2MB)
    if ($ukuranFile > 2000000) {
        echo "<script>alert('Ukuran gambar terlalu besar!');</script>";
        return false;
    }

    // Generate nama file baru agar tidak duplikat
    $namaFileBaru = uniqid();
    $namaFileBaru .= '.';
    $namaFileBaru .= $ekstensiGambar;

    // Pindahkan file ke folder uploads
    // Asumsi: menu.php ada di folder 'pages', jadi naik satu level (..) lalu ke 'uploads'
    move_uploaded_file($tmpName, '../uploads/' . $namaFileBaru);

    return '../uploads/' . $namaFileBaru;
}

// --- LOGIKA CRUD UTAMA ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_menu'];
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $id_kategori = $_POST['id_kategori'];
    $statusmenu = $_POST['statusmenu'];
    $deskripsi = $_POST['deskripsi'];
    
    // Ambil path foto lama (hidden input)
    $foto_lama = $_POST['foto_lama']; 
    $foto_url = $foto_lama; // Default foto adalah foto lama

    // Cek apakah user mengupload gambar baru
    if ($_FILES['foto']['error'] !== 4) {
        $uploadBaru = uploadFoto();
        if ($uploadBaru) {
            $foto_url = $uploadBaru; // Update variabel foto_url
            
            // Hapus file lama fisik jika ada dan bukan file default/kosong
            if ($foto_lama != "" && file_exists($foto_lama)) {
                unlink($foto_lama);
            }
        }
    }

    if ($id == "") {
        // Create
        $model->create($nama, $harga, $id_kategori, $statusmenu, $deskripsi, $foto_url);
    } else {
        // Update
        $model->update($id, $nama, $harga, $id_kategori, $statusmenu, $deskripsi, $foto_url);
    }
    
    header("Location: menu.php");
    exit;
}

// Handle Delete (Hapus Data Database + Hapus File Gambar)
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = $_GET['id'];
    
    // 1. Ambil data dulu berdasarkan ID untuk mendapatkan path gambar
    $dataMenu = $model->readById($id);
    
    // 2. Jika data ditemukan, hapus filenya
    if ($dataMenu) {
        $pathFoto = $dataMenu['foto_url'];
        if ($pathFoto != "" && file_exists($pathFoto)) {
            unlink($pathFoto); // Hapus file dari folder uploads
        }
    }

    // 3. Baru hapus data dari database
    $model->delete($id);
    header("Location: menu.php");
    exit;
}

// --- PENGAMBILAN DATA UNTUK TAMPILAN ---
$allMenu = $model->read();

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
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <div class="container-fluid p-0">
            <?php include "navbar.php"; ?>

            <div class="container-fluid py-5 bg-dark hero-header mb-5">
                <div class="container text-center my-5 pt-5 pb-4">
                    <h1 class="display-3 text-white mb-3 animated slideInDown">Food Menu</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center text-uppercase">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Menu Management</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <div class="container-fluid py-5">
            <div class="container">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h5 class="section-title ff-secondary text-center text-primary fw-normal">Food Menu</h5>
                    <h1 class="mb-3">Most Popular Items</h1>
                    
                    <button class="btn btn-success mb-5" onclick="showTambah()">
                        <i class="fa fa-plus"></i> Tambah Menu Baru
                    </button>
                </div>
                
                <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.1s">
                    <ul class="nav nav-pills d-inline-flex justify-content-center border-bottom mb-5">
                        <li class="nav-item">
                            <a class="d-flex align-items-center text-start mx-3 ms-0 pb-3 active" data-bs-toggle="pill" href="#tab-1">
                                <i class="fa fa-coffee fa-2x text-primary"></i>
                                <div class="ps-3">
                                    <small class="text-body">Popular</small>
                                    <h6 class="mt-n1 mb-0">Breakfast</h6>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="d-flex align-items-center text-start mx-3 pb-3" data-bs-toggle="pill" href="#tab-2">
                                <i class="fa fa-hamburger fa-2x text-primary"></i>
                                <div class="ps-3">
                                    <small class="text-body">Special</small>
                                    <h6 class="mt-n1 mb-0">Lunch</h6>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="d-flex align-items-center text-start mx-3 me-0 pb-3" data-bs-toggle="pill" href="#tab-3">
                                <i class="fa fa-utensils fa-2x text-primary"></i>
                                <div class="ps-3">
                                    <small class="text-body">Lovely</small>
                                    <h6 class="mt-n1 mb-0">Dinner</h6>
                                </div>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <?php 
                        function renderMenuTab($items, $tabId, $isActive = false) {
                            $activeClass = $isActive ? 'active' : '';
                            echo '<div id="'.$tabId.'" class="tab-pane fade show p-0 '.$activeClass.'">';
                            echo '<div class="row g-4">';
                            foreach($items as $item): 
                                // Cek apakah gambar ada, jika tidak pakai placeholder
                                $imgSrc = !empty($item['foto_url']) ? htmlspecialchars($item['foto_url']) : '../img/menu-1.jpg';
                            ?>
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center">
                                        <img class="flex-shrink-0 img-fluid rounded" src="<?php echo $imgSrc; ?>" alt="" style="width: 80px; height: 80px; object-fit: cover;">
                                        <div class="w-100 d-flex flex-column text-start ps-4">
                                            <h5 class="d-flex justify-content-between border-bottom pb-2">
                                                <span><?php echo htmlspecialchars($item['nama_menu']); ?></span>
                                                <span class="text-primary">Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></span>
                                            </h5>
                                            <small class="fst-italic"><?php echo htmlspecialchars($item['deskripsi']); ?></small>
                                            
                                            <div class="mt-2">
                                                <button class="btn btn-sm btn-outline-primary me-2" 
                                                    onclick="showEdit(
                                                        '<?php echo $item['id_menu']; ?>',
                                                        '<?php echo htmlspecialchars($item['nama_menu']); ?>',
                                                        '<?php echo $item['harga']; ?>',
                                                        '<?php echo $item['id_kategori']; ?>',
                                                        '<?php echo $item['statusmenu']; ?>',
                                                        '<?php echo htmlspecialchars($item['foto_url']); ?>',
                                                        `<?php echo htmlspecialchars($item['deskripsi']); ?>`
                                                    )">Edit</button>
                                                
                                                <a href="menu.php?action=delete&id=<?php echo $item['id_menu']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Hapus menu ini beserta gambarnya?')">Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                            endforeach;
                            echo '</div></div>';
                        }
                        
                        // Render Tabs
                        renderMenuTab($menuBreakfast, "tab-1", true);
                        renderMenuTab($menuLunch, "tab-2");
                        renderMenuTab($menuDinner, "tab-3");
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'footer.php'; ?>
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

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

    <div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Form Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_menu" id="id_menu">
                    <input type="hidden" name="foto_lama" id="foto_lama">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Menu</label>
                            <input type="text" class="form-control" name="nama" id="nama" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="harga" id="harga" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori (1=Pagi, 2=Siang, 3=Malam)</label>
                            <input type="number" class="form-control" name="id_kategori" id="id_kategori" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="statusmenu" id="statusmenu">
                                <option value="true">Tersedia</option>
                                <option value="false">Habis</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Foto Menu</label>
                            <input type="file" class="form-control" name="foto" id="foto">
                            <div id="preview_text" class="form-text text-primary mt-1"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="deskripsi"></textarea>
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

    <script>
    function showTambah() {
        document.getElementById("modalTitle").innerText = "Tambah Menu Baru";
        document.getElementById("id_menu").value = "";
        document.getElementById("nama").value = "";
        document.getElementById("harga").value = "";
        document.getElementById("id_kategori").value = "";
        document.getElementById("statusmenu").value = "true";
        document.getElementById("deskripsi").value = "";
        
        // Reset foto input
        document.getElementById("foto").value = ""; 
        document.getElementById("foto_lama").value = "";
        document.getElementById("preview_text").innerText = "";
        
        var myModal = new bootstrap.Modal(document.getElementById('formModal'));
        myModal.show();
    }

    function showEdit(id, nama, harga, kategori, status, foto_path, deskripsi) {
        document.getElementById("modalTitle").innerText = "Edit Menu";
        document.getElementById("id_menu").value = id;
        document.getElementById("nama").value = nama;
        document.getElementById("harga").value = harga;
        document.getElementById("id_kategori").value = kategori;
        
        let statusVal = (status === "t" || status === "1" || status === true) ? "true" : "false";
        document.getElementById("statusmenu").value = statusVal;
        
        document.getElementById("deskripsi").value = deskripsi;

        // Set Foto Lama
        document.getElementById("foto_lama").value = foto_path;
        document.getElementById("foto").value = ""; // Reset file input
        
        // Tampilkan info jika ada foto lama
        if(foto_path) {
            document.getElementById("preview_text").innerText = "Gambar saat ini: " + foto_path.split('/').pop();
        } else {
            document.getElementById("preview_text").innerText = "Belum ada gambar.";
        }
        
        var myModal = new bootstrap.Modal(document.getElementById('formModal'));
        myModal.show();
    }
    </script>
</body>
</html>