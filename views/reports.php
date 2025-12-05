<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../models/ReportModel.php';
$model = new ReportModel();

if (isset($_POST['trigger_refresh'])) {
    $model->refreshMaterializedView();
    $msg = "Data Analisis Berhasil Di-refresh dari Database!";
}

$pageMenu = isset($_GET['page_menu']) ? (int)$_GET['page_menu'] : 1;
if ($pageMenu < 1) $pageMenu = 1;
$filterKategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';

$chartData = $model->getTopMenuChart();
$tableMenuData = $model->getMenuPerformance($pageMenu, 5, $filterKategori); 
$listMenu = $tableMenuData['data'];
$pagingMenu = $tableMenuData['pagination'];
$listKategori = $model->getAllKategori(); 

$labels = []; $dataVals = [];
foreach($chartData as $row) {
    $labels[] = $row['nama_menu'];
    $dataVals[] = $row['total_terjual'];
}

$laporanShift = $model->getLaporanShift();
$laporanServer = $model->getLaporanServer();

$pageRes = isset($_GET['page_res']) ? (int)$_GET['page_res'] : 1;
if ($pageRes < 1) $pageRes = 1;
$resData = $model->getRiwayatReservasi($pageRes, 10);
$listReservasi = $resData['data'];
$pagingRes = $resData['pagination'];

$pageDetail = isset($_GET['page_detail']) ? (int)$_GET['page_detail'] : 1;
if ($pageDetail < 1) $pageDetail = 1;

$sortCol = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'tanggal_pesanan';
$sortDir = isset($_GET['order']) ? $_GET['order'] : 'DESC';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';

$detailResult = $model->getLaporanPenjualan($pageDetail, 10, $filterStatus, $sortCol, $sortDir);
$laporanDetail = $detailResult['data'];
$pagingDetail = $detailResult['pagination'];

function buildUrl($key, $val) {
    $params = $_GET;
    $params[$key] = $val;
    return '?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Laporan Restoran Lengkap</title>
    <?php include "header.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card-report { border: none; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: .3s; }
        .card-report:hover { transform: translateY(-5px); }
        .icon-box { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 1.5rem; }
        .bg-orange-soft { background-color: #fff3cd; color: #FEA116; }
        .bg-blue-soft { background-color: #cff4fc; color: #0dcaf0; }
        .bg-green-soft { background-color: #d1e7dd; color: #198754; }
        .page-link { color: #FEA116; }
        .page-item.active .page-link { background-color: #FEA116; border-color: #FEA116; color: white; }
        .sort-link { text-decoration: none; color: #333; margin-left: 5px; }
        .sort-link:hover { color: #FEA116; }
        .sort-active { color: #FEA116; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container-fluid bg-white p-0">
        <?php include "navbar.php"; ?>
        
        <div class="container-fluid py-5 bg-dark hero-header mb-5">
            <div class="container text-center my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3">Laporan & Analisis</h1>
                <nav aria-label="breadcrumb"><ol class="breadcrumb justify-content-center text-uppercase"><li class="breadcrumb-item text-white">Home</li><li class="breadcrumb-item text-white active">Reports</li></ol></nav>
            </div>
        </div>

        <div class="container-fluid py-3">
            <div class="container">
                
                <ul class="nav nav-pills justify-content-center mb-5" id="reportTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active px-4 py-2" data-bs-toggle="pill" data-bs-target="#tab-menu">
                            <i class="fa fa-utensils me-2"></i>Analisis Menu
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link px-4 py-2" data-bs-toggle="pill" data-bs-target="#tab-shift">
                            <i class="fa fa-clock me-2"></i>Laporan Shift
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link px-4 py-2" data-bs-toggle="pill" data-bs-target="#tab-server">
                            <i class="fa fa-user-tie me-2"></i>Performa Server
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link px-4 py-2" data-bs-toggle="pill" data-bs-target="#tab-reservasi">
                            <i class="fa fa-calendar-alt me-2"></i>Riwayat Reservasi
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    
                    <div class="tab-pane fade show active" id="tab-menu">
                        <div class="d-flex justify-content-between mb-3 align-items-center">
                            <form method="GET" class="d-flex">
                                <select name="kategori" class="form-select w-auto me-2" onchange="this.form.submit()">
                                    <option value="">-- Semua Kategori --</option>
                                    <?php foreach($listKategori as $kat): ?>
                                        <option value="<?= $kat['nama_kategori'] ?>" <?= ($filterKategori == $kat['nama_kategori']) ? 'selected' : '' ?>>
                                            <?= $kat['nama_kategori'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <form method="POST">
                                <button type="submit" name="trigger_refresh" class="btn btn-warning fw-bold text-dark btn-sm">
                                    <i class="fa fa-sync-alt me-2"></i> Refresh Data
                                </button>
                            </form>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="card card-report h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary mb-4">Top 10 Menu Terlaris (Global)</h5>
                                        <canvas id="menuChart" style="max-height: 350px;"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card card-report h-100">
                                    <div class="card-header bg-primary text-white d-flex justify-content-between">
                                        <span class="fw-bold">Tabel Performa Menu</span>
                                        <small>Total: <?= $pagingMenu['total_rows'] ?> Item</small>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0 small">
                                                <thead class="table-light"><tr><th>Menu</th><th>Kategori</th><th class="text-center">Jml</th><th class="text-end">Omzet</th></tr></thead>
                                                <tbody>
                                                    <?php if(empty($listMenu)): ?>
                                                        <tr><td colspan="4" class="text-center py-3">Tidak ada data.</td></tr>
                                                    <?php else: ?>
                                                        <?php foreach($listMenu as $m): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($m['nama_menu']) ?></td>
                                                            <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($m['nama_kategori']) ?></span></td>
                                                            <td class="fw-bold text-center"><?= $m['total_terjual'] ?></td>
                                                            <td class="text-end">Rp <?= number_format($m['total_pendapatan']) ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0">
                                        <?php if($pagingMenu['total_pages'] > 1): ?>
                                        <nav>
                                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                                <li class="page-item <?= ($pagingMenu['current_page'] <= 1) ? 'disabled' : '' ?>">
                                                    <a class="page-link" href="<?= buildUrl('page_menu', $pagingMenu['current_page'] - 1) ?>">Prev</a>
                                                </li>
                                                <?php for($i=1; $i<=$pagingMenu['total_pages']; $i++): ?>
                                                <li class="page-item <?= ($i == $pagingMenu['current_page']) ? 'active' : '' ?>">
                                                    <a class="page-link" href="<?= buildUrl('page_menu', $i) ?>"><?= $i ?></a>
                                                </li>
                                                <?php endfor; ?>
                                                <li class="page-item <?= ($pagingMenu['current_page'] >= $pagingMenu['total_pages']) ? 'disabled' : '' ?>">
                                                    <a class="page-link" href="<?= buildUrl('page_menu', $pagingMenu['current_page'] + 1) ?>">Next</a>
                                                </li>
                                            </ul>
                                        </nav>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-shift">
                        <div class="row justify-content-center">
                            <?php foreach($laporanShift as $s): 
                                $icon = ($s['nama_shift'] == 'Shift Pagi') ? 'fa-sun' : 'fa-moon';
                                $bg = ($s['nama_shift'] == 'Shift Pagi') ? 'bg-orange-soft' : 'bg-blue-soft';
                            ?>
                            <div class="col-md-5">
                                <div class="card card-report p-4 text-center mb-3">
                                    <div class="icon-box <?= $bg ?> mx-auto mb-3"><i class="fa <?= $icon ?>"></i></div>
                                    <h3><?= htmlspecialchars($s['nama_shift']) ?></h3>
                                    <div class="row g-3 mt-2">
                                        <div class="col-6 border-end">
                                            <small class="text-muted">Transaksi</small>
                                            <h4 class="fw-bold"><?= $s['jumlah_transaksi'] ?></h4>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Pendapatan</small>
                                            <h4 class="text-success fw-bold">Rp <?= number_format($s['total_pendapatan']/1000) ?>k</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-server">
                        <div class="card card-report">
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-3">Produktivitas Pegawai</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-dark">
                                            <tr><th>Server</th><th>Transaksi</th><th>Total Omzet</th><th>Status</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($laporanServer as $srv): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon-box bg-green-soft me-3" style="width:40px;height:40px;font-size:1rem;"><i class="fa fa-user"></i></div>
                                                        <span class="fw-bold"><?= htmlspecialchars($srv['nama_server'] ?: 'System') ?></span>
                                                    </div>
                                                </td>
                                                <td><?= $srv['jumlah_transaksi'] ?></td>
                                                <td class="text-success fw-bold">Rp <?= number_format($srv['total_pendapatan']) ?></td>
                                                <td>
                                                    <?php if($srv['jumlah_transaksi'] > 5): ?>
                                                        <span class="badge bg-success">Excellent</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Good</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="tab-reservasi">
                        <div class="card card-report">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fa fa-calendar-check me-2"></i>Data Reservasi</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Jam</th>
                                                <th>Pelanggan</th>
                                                <th>Meja</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($listReservasi)): ?>
                                                <tr><td colspan="5" class="text-center py-3">Belum ada data reservasi.</td></tr>
                                            <?php else: ?>
                                                <?php foreach($listReservasi as $r): ?>
                                                <tr>
                                                    <td><?= date('d M Y', strtotime($r['tanggal_reservasi'])) ?></td>
                                                    <td><?= date('H:i', strtotime($r['jam_reservasi'])) ?></td>
                                                    <td>
                                                        <div class="fw-bold"><?= htmlspecialchars($r['nama_pelanggan']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($r['no_telepon']) ?></small>
                                                    </td>
                                                    <td><span class="badge bg-dark">Meja <?= $r['nomor_meja'] ?></span></td>
                                                    <td>
                                                        <?php 
                                                            $badgeClass = 'bg-secondary';
                                                            if($r['status'] == 'confirmed') $badgeClass = 'bg-success';
                                                            if($r['status'] == 'pending') $badgeClass = 'bg-warning text-dark';
                                                            if($r['status'] == 'cancelled') $badgeClass = 'bg-danger';
                                                        ?>
                                                        <span class="badge <?= $badgeClass ?>"><?= strtoupper($r['status']) ?></span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <?php if($pagingRes['total_pages'] > 1): ?>
                                <nav>
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item <?= ($pagingRes['current_page'] <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="<?= buildUrl('page_res', $pagingRes['current_page'] - 1) ?>">Prev</a>
                                        </li>
                                        <?php for($i=1; $i<=$pagingRes['total_pages']; $i++): ?>
                                        <li class="page-item <?= ($i == $pagingRes['current_page']) ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= buildUrl('page_res', $i) ?>"><?= $i ?></a>
                                        </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?= ($pagingRes['current_page'] >= $pagingRes['total_pages']) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="<?= buildUrl('page_res', $pagingRes['current_page'] + 1) ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div> 
                
                <div class="card border-0 shadow-lg mt-5">
                    <div class="card-header bg-dark text-white">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h5 class="mb-0 text-white"><i class="fa fa-list me-2"></i>Rincian Transaksi</h5>
                            </div>
                            
                            <div class="col-md-8">
                                <form method="GET" class="d-flex justify-content-end gap-2">
                                    <select name="status" class="form-select form-select-sm w-auto">
                                        <option value="">- Semua Status -</option>
                                        <option value="selesai" <?= $filterStatus=='selesai'?'selected':'' ?>>Selesai</option>
                                        <option value="pending" <?= $filterStatus=='pending'?'selected':'' ?>>Pending</option>
                                    </select>
                                    
                                    <select name="sort_by" class="form-select form-select-sm w-auto">
                                        <option value="tanggal_pesanan" <?= $sortCol=='tanggal_pesanan'?'selected':'' ?>>Urut Tgl</option>
                                        <option value="subtotal" <?= $sortCol=='subtotal'?'selected':'' ?>>Urut Harga</option>
                                    </select>
                                    
                                    <select name="order" class="form-select form-select-sm w-auto">
                                        <option value="DESC" <?= $sortDir=='DESC'?'selected':'' ?>>Terbaru / Tinggi (Desc)</option>
                                        <option value="ASC" <?= $sortDir=='ASC'?'selected':'' ?>>Terlama / Rendah (Asc)</option>
                                    </select>

                                    <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Server</th>
                                        <th>Shift</th>
                                        <th>Kode</th>
                                        <th>Menu</th>
                                        <th>Status</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($laporanDetail)): ?>
                                        <tr><td colspan="7" class="text-center">Data tidak ditemukan.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($laporanDetail as $l): ?>
                                        <tr>
                                            <td><?= date('d/m H:i', strtotime($l['tanggal_pesanan'])) ?></td>
                                            <td><?= htmlspecialchars($l['nama_server']) ?></td>
                                            <td><span class="badge bg-light text-dark border"><?= $l['nama_shift'] ?></span></td>
                                            <td><small><?= $l['kode_pesanan'] ?></small></td>
                                            <td><?= $l['nama_menu'] ?> (x<?= $l['jumlah'] ?>)</td>
                                            <td>
                                                <?php if($l['status_order'] == 'selesai'): ?>
                                                    <span class="badge bg-success">Selesai</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark"><?= $l['status_order'] ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end fw-bold">Rp <?= number_format($l['subtotal']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Hal <?= $pagingDetail['current_page'] ?> dari <?= $pagingDetail['total_pages'] ?></small>
                            <?php if($pagingDetail['total_pages'] > 1): ?>
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item <?= ($pagingDetail['current_page'] <= 1) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= buildUrl('page_detail', $pagingDetail['current_page'] - 1) ?>">Prev</a>
                                    </li>
                                    <?php 
                                    $start = max(1, $pagingDetail['current_page'] - 2);
                                    $end = min($pagingDetail['total_pages'], $pagingDetail['current_page'] + 2);
                                    for($i=$start; $i<=$end; $i++): 
                                    ?>
                                    <li class="page-item <?= ($i == $pagingDetail['current_page']) ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= buildUrl('page_detail', $i) ?>"><?= $i ?></a>
                                    </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= ($pagingDetail['current_page'] >= $pagingDetail['total_pages']) ? 'disabled' : '' ?>">
                                        <a class="page-link" href="<?= buildUrl('page_detail', $pagingDetail['current_page'] + 1) ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/main.js"></script>

    <script>
        const ctx = document.getElementById('menuChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Porsi Terjual',
                    data: <?= json_encode($dataVals) ?>,
                    backgroundColor: '#FEA116',
                    borderColor: '#FEA116',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>
</html>