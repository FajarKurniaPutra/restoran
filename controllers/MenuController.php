<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../models/MenuModel.php';

class MenuController {
    private $model;

    public function __construct() {
        $this->model = new MenuModel();
    }

    private function validateInput($data) {
        return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    private function uploadFoto() {
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === 4) {
            return false; 
        }
        
        $namaFile   = $_FILES['foto']['name'];
        $ukuranFile = $_FILES['foto']['size'];
        $error      = $_FILES['foto']['error'];
        $tmpName    = $_FILES['foto']['tmp_name'];

        if ($error !== UPLOAD_ERR_OK) return 'ERROR';

        if ($ukuranFile > 2 * 1024 * 1024) {
            $_SESSION['notif'] = 'Gagal: Ukuran gambar maksimal 2MB!';
            return 'ERROR';
        }

        $ekstensiValid = ['jpg', 'jpeg', 'png', 'gif'];
        $ekstensi = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));
        if (!in_array($ekstensi, $ekstensiValid)) {
            $_SESSION['notif'] = 'Gagal: Ekstensi file tidak diizinkan!';
            return 'ERROR';
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpName);
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
            $_SESSION['notif'] = 'Gagal: File bukan gambar valid!';
            return 'ERROR';
        }

        $namaFileBaru = uniqid() . '_' . time() . '.' . $ekstensi;
        $targetDir = '../uploads/';
        
        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
        
        if (move_uploaded_file($tmpName, $targetDir . $namaFileBaru)) {
            return $namaFileBaru; 
        }
        return 'ERROR';
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_menu'] ?? '';
            $nama = $this->validateInput($_POST['nama']);
            $harga = (float)$_POST['harga'];
            $kategori = $_POST['id_kategori'];
            $status = $_POST['statusmenu'];
            $deskripsi = $this->validateInput($_POST['deskripsi']);
            $redirectCat = $_POST['redirect_kategori'] ?? 1;
            
            $foto_final = $_POST['foto_lama'];
            $uploadResult = $this->uploadFoto();

            if ($uploadResult === 'ERROR') {
                header("Location: menu.php?kategori=" . $redirectCat); 
                exit;
            } elseif ($uploadResult !== false) {
                $foto_final = $uploadResult;
                if (!empty($_POST['foto_lama']) && file_exists('../uploads/' . $_POST['foto_lama'])) {
                    unlink('../uploads/' . $_POST['foto_lama']);
                }
            }

            $data = [
                'nama' => $nama, 'harga' => $harga, 'kategori_id' => $kategori,
                'deskripsi' => $deskripsi, 'gambar' => $foto_final, 'is_available' => $status
            ];

            if (empty($id)) {
                $res = $this->model->create($data);
                $_SESSION['notif'] = $res ? "Menu berhasil DITAMBAHKAN!" : "Gagal menambah menu.";
            } else {
                $res = $this->model->update($id, $data);
                $_SESSION['notif'] = $res ? "Menu berhasil DIUPDATE!" : "Gagal update menu.";
            }
            header("Location: menu.php?kategori=" . $redirectCat);
            exit;
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $oldData = $this->model->getById($_GET['id']);
            if ($oldData && !empty($oldData['gambar']) && file_exists('../uploads/' . $oldData['gambar'])) {
                unlink('../uploads/' . $oldData['gambar']);
            }

            $this->model->delete($_GET['id']);
            $cat = $_GET['kategori'] ?? 1;
            $_SESSION['notif'] = "Menu berhasil DIHAPUS!";
            header("Location: menu.php?kategori=" . $cat);
            exit;
        }
    }

    public function getData() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;

        $limit = 6;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';
        $activeKategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 1;

        $allMenu = $this->model->getAll($limit, $offset, $search, $activeKategori);
        $totalData = $this->model->countAll($search, $activeKategori);
        $totalPages = ceil($totalData / $limit);
        $kategoriList = $this->model->getCategories();

        return compact('page', 'limit', 'offset', 'search', 'activeKategori', 'allMenu', 'totalData', 'totalPages', 'kategoriList');
    }
}

$controller = new MenuController();
$controller->handleRequest();
$data = $controller->getData();
extract($data);
?>