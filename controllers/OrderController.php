<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../models/OrderModel.php';
require_once '../models/MenuModel.php'; 

class OrderController {
    private $model;

    public function __construct() {
        $this->model = new OrderModel();
    }

    public function handleRequest() {
        $msg = "";
        $msgClass = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if (isset($_POST['action']) && $_POST['action'] === 'create') {
                $items = [];
                if(isset($_POST['menu_id'])){
                    for($i=0; $i<count($_POST['menu_id']); $i++){
                        if($_POST['qty'][$i] > 0) $items[] = ['menu_id' => $_POST['menu_id'][$i], 'jumlah' => $_POST['qty'][$i]];
                    }
                }
                
                if(empty($items)) {
                    $msg = "Gagal: Belum ada menu yang dipilih.";
                    $msgClass = "alert-warning";
                } else {
                    $res = $this->model->createOrderTransaction($_POST['pelanggan_id'], $_POST['meja_id'], $items);
                    if ($res === true) {
                        $msg = "Order Berhasil Dibuat (Transaction Commit)!";
                        $msgClass = "alert-success";
                    } else {
                        $msg = "Gagal: $res";
                        $msgClass = "alert-danger";
                    }
                }
            }

            if (isset($_POST['action']) && $_POST['action'] === 'bayar') {
                $result = $this->model->bayarOrder($_POST['id_pesanan'], $_POST['jumlah_bayar'], $_POST['metode']);
                
                if ($result['status'] === 'success') {
                    $kembalianRp = number_format($result['kembalian'], 0, ',', '.');
                    $msg = "<strong>PEMBAYARAN SUKSES!</strong><br>Kembalian: <span class='fs-4 fw-bold'>Rp $kembalianRp</span>";
                    $msgClass = "alert-success";
                } elseif ($result['status'] === 'warning') {
                    $msg = "<strong>TRANSAKSI DITOLAK:</strong> " . $result['message'];
                    $msgClass = "alert-danger"; 
                } else {
                    $msg = "Error: " . $result['message'];
                    $msgClass = "alert-dark";
                }
            }
        }

        return ['msg' => $msg, 'msgClass' => $msgClass];
    }

    public function getData() {
        return [
            'activeMenus' => $this->model->getActiveMenus(),
            'pelanggan' => $this->model->getPelanggan(),
            'meja' => $this->model->getMejaKosong(),
            'orders' => $this->model->getPendingOrders(),
            'explain' => isset($_GET['analyze']) ? $this->model->getExplainAnalyze($_GET['analyze']) : ""
        ];
    }
}

$controller = new OrderController();
$status = $controller->handleRequest();
$data = $controller->getData();

extract($status);
extract($data);
?>