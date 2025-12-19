<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) { header("Location: login.php"); exit; }

require_once '../models/OrderModel.php';

class OrderController {
    private $model;
    public function __construct() { $this->model = new OrderModel(); }

    public function handleRequest() {
        $msg = ""; $msgClass = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'create') {
                $items = [];
                if(isset($_POST['menu_id'])){
                    for($i=0; $i<count($_POST['menu_id']); $i++){
                        if($_POST['qty'][$i] > 0) $items[] = ['menu_id' => $_POST['menu_id'][$i], 'jumlah' => $_POST['qty'][$i]];
                    }
                }
                
                if(empty($items)) {
                    $msg = "Gagal: Belum ada menu yang dipilih."; $msgClass = "alert-warning";
                } else {
                    $res = $this->model->createOrderTransaction($_POST['pelanggan_id'], $_POST['meja_id'], $items, $_POST['tipe_order']);
                    if ($res === true) {
                        $msg = "Order Berhasil Dibuat!"; $msgClass = "alert-success";
                    } else {
                        $msg = "Gagal: $res"; $msgClass = "alert-danger";
                    }
                }
            }

            if (isset($_POST['action']) && $_POST['action'] === 'bayar') {
                $result = $this->model->bayarOrder($_POST['id_pesanan'], $_POST['jumlah_bayar'], $_POST['metode']);
                if ($result['status'] === 'success') {
                    $msg = "PEMBAYARAN SUKSES! Kembalian: Rp " . number_format($result['kembalian']);
                    $msgClass = "alert-success";
                } else {
                    $msg = $result['message']; $msgClass = "alert-danger";
                }
            }

            if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
                if ($this->model->cancelOrder($_POST['id_pesanan'])) {
                    $msg = "Order Berhasil Dibatalkan dan Meja Dikosongkan.";
                    $msgClass = "alert-info";
                } else {
                    $msg = "Gagal membatalkan order."; $msgClass = "alert-danger";
                }
            }
        }

        return ['msg' => $msg, 'msgClass' => $msgClass];
    }

    public function getData() {
        $explain = "";
        if (isset($_GET['analyze']) && !empty(trim($_GET['analyze']))) {
            $explain = $this->model->getQueryPlan($_GET['analyze']);
        }

        return [
            'activeMenus' => $this->model->getActiveMenus(),
            'pelanggan' => $this->model->getPelanggan(),
            'meja' => $this->model->getMejaKosong(),
            'orders' => $this->model->getPendingOrders(),
            'explain' => $explain
        ];
    }
}

$controller = new OrderController();
$status = $controller->handleRequest();
$data = $controller->getData();
extract($status); extract($data);
?>