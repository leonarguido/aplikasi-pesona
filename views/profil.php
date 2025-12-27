<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['user_id'];

// PROSES UPLOAD TTD
if (isset($_POST['upload_ttd'])) {
    $nama_file = $_FILES['foto_ttd']['name'];
    $ukuran    = $_FILES['foto_ttd']['size'];
    $error     = $_FILES['foto_ttd']['error'];
    $tmp_name  = $_FILES['foto_ttd']['tmp_name'];

    // Cek ekstensi
    $valid_ext = ['jpg', 'jpeg', 'png'];
    $file_ext  = explode('.', $nama_file);
    $file_ext  = strtolower(end($file_ext));

    if (!in_array($file_ext, $valid_ext)) {
        echo "<script>alert('Yang diupload bukan gambar!');</script>";
    } elseif ($ukuran > 2000000) { // Max 2MB
        echo "<script>alert('Ukuran gambar terlalu besar!');</script>";
    } else {
        // Buat folder jika belum ada
        if (!file_exists('assets/img/ttd')) {
            mkdir('assets/img/ttd', 0777, true);
        }

        // Generate nama baru agar tidak duplikat
        $nama_baru = "ttd_" . $id_user . "_" . uniqid() . "." . $file_ext;

        move_uploaded_file($tmp_name, 'assets/img/ttd/' . $nama_baru);

        // Update Database
        $query = "UPDATE tb_user SET ttd = '$nama_baru' WHERE id = '$id_user'";
        mysqli_query($koneksi, $query);

        echo "<script>alert('Tanda tangan berhasil diupload!'); window.location='profil.php';</script>";
    }
}

// Ambil Data User
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tb_user WHERE id = '$id_user'"));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require 'layout/header.php'; ?>
</head>

<body id="page-top">
    <?php require 'layout/topbar.php'; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="row">
            <div class="col-md-2">
                <?php require 'layout/sidebar.php'; ?>
            </div>
            <div class="col-md-10 mt-4">
                <div class="container-fluid">

                    <h1 class="h3 mb-4 text-gray-800">Profil Saya</h1>

                    <div class="row">
                        <div class="col-lg-12">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card shadow">
                                        <div class="card-header py-3">
                                            <h6 class="m-0 font-weight-bold text-primary">Data & Tanda Tangan</h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <img class="img-profile rounded-circle mb-3" src="<?= BASE_URL ?>assets/img/undraw_profile.svg" width="100">
                                            <h4><?= $data['nama']; ?></h4>
                                            <p class="text-muted"><?= ucfirst($data['role']); ?></p>

                                            <hr>

                                            <h5>Tanda Tangan Digital</h5>
                                            <div class="mb-3">
                                                <?php if (!empty($data['ttd'])): ?>
                                                    <img src="<?= BASE_URL ?>assets/img/ttd/<?= $data['ttd']; ?>" class="img-thumbnail" width="200">
                                                    <p class="small text-success mt-2"><i class="fas fa-check-circle"></i> TTD Tersedia</p>
                                                <?php else: ?>
                                                    <div class="alert alert-warning">Belum ada tanda tangan. Silakan upload gambar TTD (putih bersih) agar muncul di surat.</div>
                                                <?php endif; ?>
                                            </div>

                                            <form method="POST" enctype="multipart/form-data">
                                                <div class="form-group text-left">
                                                    <label>Upload/Ganti TTD (Format JPG/PNG)</label>
                                                    <input type="file" name="foto_ttd" class="form-control-file" required>
                                                </div>
                                                <button type="submit" name="upload_ttd" class="btn btn-primary btn-block">Simpan Tanda Tangan</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require 'layout/footer.php'; ?>
</body>

</html>