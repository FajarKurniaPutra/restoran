<?php
session_start();

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/koneksi.php';
$db = new Database();

$qMenu = pg_query($db->conn, "SELECT COUNT(*) FROM menu WHERE deleted_at IS NULL");
$totalMenu = pg_fetch_result($qMenu, 0, 0);

$qPelanggan = pg_query($db->conn, "SELECT COUNT(*) FROM pelanggan WHERE deleted_at IS NULL");
$totalPelanggan = pg_fetch_result($qPelanggan, 0, 0);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Dashboard - Restoran Management</title>
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
                <div class="container my-5 py-5">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-6 text-center text-lg-start">
                            <h1 class="display-3 text-white animated slideInLeft">Sistem Manajemen<br>Restoran & Kasir</h1>
                            <p class="text-white animated slideInLeft mb-4 pb-2">
                                Platform terintegrasi untuk mengelola pesanan (POS), reservasi meja, data pelanggan, dan analisis laporan penjualan secara realtime dan akurat.
                            </p>
                            <a href="order.php" class="btn btn-primary py-sm-3 px-sm-5 me-3 animated slideInLeft">
                                <i class="fa fa-cash-register me-2"></i>Buka Kasir
                            </a>
                        </div>
                        <div class="col-lg-6 text-center text-lg-end overflow-hidden">
                            <img class="img-fluid" src="../img/hero.png" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid py-5">
            <div class="container">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h5 class="section-title ff-secondary text-center text-primary fw-normal">Quick Access</h5>
                    <h1 class="mb-5">Modul Utama</h1>
                </div>
                <div class="row g-4">
                    
                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="service-item rounded pt-3 h-100">
                            <div class="p-4 text-center">
                                <i class="fa fa-3x fa-shopping-cart text-primary mb-4"></i>
                                <h5>Kasir & Order</h5>
                                <p>Input pesanan pelanggan, proses pembayaran, dan kirim order ke dapur.</p>
                                <a href="order.php" class="btn btn-sm btn-outline-primary mt-2">Masuk <i class="fa fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.3s">
                        <div class="service-item rounded pt-3 h-100">
                            <div class="p-4 text-center">
                                <i class="fa fa-3x fa-chair text-primary mb-4"></i>
                                <h5>Booking Meja</h5>
                                <p>Kelola ketersediaan meja, reservasi baru, dan status meja aktif.</p>
                                <a href="booking.php" class="btn btn-sm btn-outline-primary mt-2">Atur Meja <i class="fa fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.5s">
                        <div class="service-item rounded pt-3 h-100">
                            <div class="p-4 text-center">
                                <i class="fa fa-3x fa-chart-line text-primary mb-4"></i>
                                <h5>Laporan & Analisis</h5>
                                <p>Pantau menu terlaris, total pendapatan, dan riwayat transaksi harian.</p>
                                <a href="reports.php" class="btn btn-sm btn-outline-primary mt-2">Lihat Data <i class="fa fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 wow fadeInUp" data-wow-delay="0.7s">
                        <div class="service-item rounded pt-3 h-100">
                            <div class="p-4 text-center">
                                <i class="fa fa-3x fa-users text-primary mb-4"></i>
                                <h5>Data Pelanggan</h5>
                                <p>Database pelanggan setia, riwayat kunjungan, dan manajemen kontak.</p>
                                <a href="customers.php" class="btn btn-sm btn-outline-primary mt-2">Kelola <i class="fa fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="container-fluid py-5">
            <div class="container">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-6">
                        <div class="row g-3">
                            <div class="col-6 text-start">
                                <img class="img-fluid rounded w-100 wow zoomIn" data-wow-delay="0.1s" src="../img/about-1.jpg">
                            </div>
                            <div class="col-6 text-start">
                                <img class="img-fluid rounded w-75 wow zoomIn" data-wow-delay="0.3s" src="../img/about-2.jpg" style="margin-top: 25%;">
                            </div>
                            <div class="col-6 text-end">
                                <img class="img-fluid rounded w-75 wow zoomIn" data-wow-delay="0.5s" src="../img/about-3.jpg">
                            </div>
                            <div class="col-6 text-end">
                                <img class="img-fluid rounded w-100 wow zoomIn" data-wow-delay="0.7s" src="../img/about-4.jpg">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h5 class="section-title ff-secondary text-start text-primary fw-normal">System Overview</h5>
                        <h1 class="mb-4">Mengapa Menggunakan <i class="fa fa-utensils text-primary me-2"></i>Sistem Ini?</h1>
                        <p class="mb-4">Aplikasi ini dirancang untuk mempercepat operasional restoran, meminimalisir kesalahan pencatatan pesanan, dan memberikan wawasan bisnis yang akurat.</p>
                        
                        <div class="row g-4 mb-4">
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center border-start border-5 border-primary px-3">
                                    <h1 class="flex-shrink-0 display-5 text-primary mb-0" data-toggle="counter-up"><?= $totalMenu ?></h1>
                                    <div class="ps-4">
                                        <p class="mb-0">Varian</p>
                                        <h6 class="text-uppercase mb-0">Total Menu</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center border-start border-5 border-primary px-3">
                                    <h1 class="flex-shrink-0 display-5 text-primary mb-0" data-toggle="counter-up"><?= $totalPelanggan ?></h1>
                                    <div class="ps-4">
                                        <p class="mb-0">Orang</p>
                                        <h6 class="text-uppercase mb-0">Total Pelanggan</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-primary border-0 text-dark" role="alert">
                            <i class="fa fa-info-circle me-2"></i> Gunakan menu <strong>Navbar</strong> di atas atau <strong>Kartu Akses</strong> untuk mulai bekerja.
                        </div>
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
</body>

</html>