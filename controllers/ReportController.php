<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../models/ReportModel.php';

class ReportController {
    private $model;

    public function __construct() {
        $this->model = new ReportModel();
    }

    public function handleRequest() {
        $msg = "";
        
        if (isset($_POST['trigger_refresh'])) {
            $this->model->refreshMaterializedView();
            $msg = "Data Analisis Berhasil Di-refresh dari Database!";
        }

        return $msg;
    }

    public function getData() {
        $pageMenu = isset($_GET['page_menu']) ? (int)$_GET['page_menu'] : 1;
        if ($pageMenu < 1) $pageMenu = 1;
        $filterKategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';

        $chartData = $this->model->getTopMenuChart();
        $tableMenuData = $this->model->getMenuPerformance($pageMenu, 5, $filterKategori);
        
        $labels = []; 
        $dataVals = [];
        foreach($chartData as $row) {
            $labels[] = $row['nama_menu'];
            $dataVals[] = $row['total_terjual'];
        }

        $laporanShift = $this->model->getLaporanShift();
        $laporanServer = $this->model->getLaporanServer();

        $pageRes = isset($_GET['page_res']) ? (int)$_GET['page_res'] : 1;
        if ($pageRes < 1) $pageRes = 1;
        $resData = $this->model->getRiwayatReservasi($pageRes, 10);

        $pageDetail = isset($_GET['page_detail']) ? (int)$_GET['page_detail'] : 1;
        if ($pageDetail < 1) $pageDetail = 1;

        $sortCol = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'tanggal_pesanan';
        $sortDir = isset($_GET['order']) ? $_GET['order'] : 'DESC';
        $filterStatus = isset($_GET['status']) ? $_GET['status'] : '';

        $detailResult = $this->model->getLaporanPenjualan($pageDetail, 10, $filterStatus, $sortCol, $sortDir);

        return [
            'pageMenu' => $pageMenu,
            'filterKategori' => $filterKategori,
            'pageRes' => $pageRes,
            'pageDetail' => $pageDetail,
            'sortCol' => $sortCol,
            'sortDir' => $sortDir,
            'filterStatus' => $filterStatus,

            'listMenu' => $tableMenuData['data'],
            'pagingMenu' => $tableMenuData['pagination'],
            'listKategori' => $this->model->getAllKategori(),
            
            'labels' => $labels,    
            'dataVals' => $dataVals, 
            
            'laporanShift' => $laporanShift,
            'laporanServer' => $laporanServer,
            
            'listReservasi' => $resData['data'],
            'pagingRes' => $resData['pagination'],
            
            'laporanDetail' => $detailResult['data'],
            'pagingDetail' => $detailResult['pagination']
        ];
    }
}

$controller = new ReportController();
$msg = $controller->handleRequest(); 
$data = $controller->getData();      

extract($data); 
?>