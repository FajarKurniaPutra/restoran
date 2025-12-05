<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="container position-relative p-0">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark p-3 py-lg-0">
        <a href="homepage.php" class="navbar-brand p-0">
            <h1 class="text-primary ff-secondary m-0 fw-normal"><i class="fa fa-utensils me-3"></i>RestOrun</h1>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="fa fa-bars"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto py-0 pe-4">
                
                <a href="homepage.php" class="nav-item nav-link <?= ($currentPage == 'homepage.php') ? 'active' : '' ?>">Home</a>
                <a href="menu.php" class="nav-item nav-link <?= ($currentPage == 'menu.php') ? 'active' : '' ?>">Menu</a>
                
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle <?= in_array($currentPage, ['booking.php', 'order.php', 'customers.php', 'reports.php']) ? 'active' : '' ?>" data-bs-toggle="dropdown">Manajemen</a>
                    <div class="dropdown-menu m-0">
                        <h6 class="dropdown-header">Operasional</h6>
                        <a href="booking.php" class="dropdown-item <?= ($currentPage == 'booking.php') ? 'active' : '' ?>">Booking Meja</a>
                        <a href="order.php" class="dropdown-item <?= ($currentPage == 'order.php') ? 'active' : '' ?>">Order (Kasir)</a>
                        
                        <div class="dropdown-divider"></div>
                        
                        <h6 class="dropdown-header">Data & Laporan</h6>
                        <a href="customers.php" class="dropdown-item <?= ($currentPage == 'customers.php') ? 'active' : '' ?>">Data Pelanggan</a>
                        <a href="reports.php" class="dropdown-item <?= ($currentPage == 'reports.php') ? 'active' : '' ?>">Laporan Penjualan</a>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center border-start ps-3 ms-3">
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                    <div class="text-end me-3 d-none d-lg-block">
                        <h6 class="text-light m-0 small fw-bold"><?= htmlspecialchars($_SESSION['fullname']) ?></h6>
                        <span class="badge bg-warning text-dark" style="font-size: 0.65rem;"><?= htmlspecialchars($_SESSION['role']) ?></span>
                    </div>
                    <a href="logout.php" class="btn btn-outline-primary btn-sm fw-bold" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                        <i class="fa fa-sign-out-alt me-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary py-2 px-4">Login</a>
                <?php endif; ?>
            </div>

        </div>
    </nav>
</div>