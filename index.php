<?php
session_start();
// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: views/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require 'views/layout/header.php'; ?>
</head>

<body id="page-top">
    <?php require 'views/layout/topbar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="row">
            <div class="col-md-2">
                <?php require 'views/layout/sidebar.php'; ?>
            </div>
            <div class="col-md-10 mt-4">
                <div class="container-fluid">
    
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
    
                </div>
            </div>
        </div>
    </div>

    <?php require 'views/layout/footer.php'; ?>
</body>

</html>