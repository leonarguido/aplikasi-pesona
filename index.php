<?php
session_start();
// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Panggil File Layout
require 'layout/header.php';
require 'layout/sidebar.php';
require 'layout/topbar.php';
?>

<h1 class="h3 mb-4 text-gray-800">Dashboard</h1>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Selamat Datang, <?= $_SESSION['full_name']; ?></h6>
            </div>
            <div class="card-body">
                <p>Anda login sebagai: <strong><?= ucfirst($_SESSION['role']); ?></strong></p>
                <p>Silakan pilih menu di samping untuk memulai.</p>
            </div>
        </div>
    </div>
</div>
<?php
require 'layout/footer.php';
?>