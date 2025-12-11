<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../models/MejaModel.php';

class BookingController {
    private $model;

    public function __construct() {
        $this->model = new MejaModel();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (isset($_POST['action']) && $_POST['action'] === 'save_meja') {
                $id = $_POST['id_meja'];
                if (empty($id)) {
                    $this->model->create($_POST['nomor'], $_POST['kapasitas']);
                    $_SESSION['notif'] = "Meja Ditambahkan!";
                } else {
                    $this->model->update($id, $_POST['nomor'], $_POST['kapasitas'], $_POST['status']);
                    $_SESSION['notif'] = "Meja Diupdate!";
                }
                header("Location: booking.php"); exit;
            }

            if (isset($_POST['action']) && $_POST['action'] === 'add_reservasi') {
                $data = [
                    'meja_id' => $_POST['meja_id'],
                    'nama' => $_POST['nama'],
                    'telp' => $_POST['telp'],
                    'tanggal' => $_POST['tanggal'],
                    'jam' => $_POST['jam']
                ];
                
                $res = $this->model->addReservasi($data);
                
                if ($res === "BENTROK") {
                    $_SESSION['notif'] = "GAGAL: Jam tersebut bentrok dengan reservasi lain!";
                } elseif ($res === "MEJA_TIDAK_AVAILABLE") {
                    $_SESSION['notif'] = "GAGAL: Meja sedang dipakai (Status: Terisi). Mohon kosongkan meja terlebih dahulu.";
                } else {
                    $_SESSION['notif'] = "Reservasi Berhasil Disimpan!";
                }
                header("Location: booking.php"); exit;
            }
        }

        if (isset($_GET['action'])) {
            if ($_GET['action'] == 'delete_meja') {
                $this->model->delete($_GET['id']);
                $_SESSION['notif'] = "Meja dihapus.";
            }
            if ($_GET['action'] == 'cancel_res') {
                $this->model->cancelReservasi($_GET['id']);
                $_SESSION['notif'] = "Reservasi Dibatalkan.";
            }
            header("Location: booking.php"); exit;
        }
    }

    public function getData() {
        $f_tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $f_jam = $_GET['jam'] ?? date('H:i');

        return [
            'f_tanggal' => $f_tanggal,
            'f_jam' => $f_jam,
            'mejaList' => $this->model->getMejaWithStatus($f_tanggal, $f_jam),
            'upcomingList' => $this->model->getUpcomingReservations(),
            'historyList' => $this->model->getHistoryReservasi(),
            'pelangganList' => $this->model->getAllPelanggan()
        ];
    }
}

$controller = new BookingController();
$controller->handleRequest();
$data = $controller->getData();

extract($data);
?>