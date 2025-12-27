<?php
session_start();
require '../config/koneksi.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Logika Proses Permintaan (Disesuaikan dengan Database Kamu)
if (isset($_POST['minta'])) {
    $user_id        = $_SESSION['user_id']; // Sesuai kolom user_id
    $barang_id      = $_POST['barang_id'];  // Sesuai kolom barang_id
    $jumlah         = $_POST['jumlah'];     // Sesuai kolom jumlah
    $tgl_permintaan = $_POST['tanggal_permintaan'];
    $keperluan      = mysqli_real_escape_string($koneksi, $_POST['keperluan']); // Kolom baru

    // Ambil stok saat ini untuk validasi
    $cek_stok = mysqli_query($koneksi, "SELECT stok FROM tb_barang_bergerak WHERE id = '$barang_id'");
    $data_stok = mysqli_fetch_assoc($cek_stok);

    if ($jumlah > $data_stok['stok']) {
        echo "<script>alert('Gagal! Jumlah permintaan melebihi stok tersedia.');</script>";
    } else {
        // QUERY INSERT SESUAI DATABASE KAMU
        $query = "INSERT INTO tb_permintaan (user_id, barang_id, jumlah, tanggal_permintaan, status, keperluan) 
                  VALUES ('$user_id', '$barang_id', '$jumlah', '$tgl_permintaan', 'menunggu', '$keperluan')";

        if (mysqli_query($koneksi, $query)) {
            // Redirect ke halaman permintaan_saya.php (akan kita buat nanti)
            echo "<script>alert('Permintaan berhasil dikirim! Menunggu persetujuan.'); window.location='permintaan_saya.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}
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

                    <h1 class="h3 mb-2 text-gray-800">Daftar Barang (ATK)</h1>
                    <p class="mb-4">Pilih barang yang dibutuhkan untuk keperluan operasional.</p>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Katalog Barang Tersedia</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th>Kode</th>
                                                    <th>Nama Barang</th>
                                                    <th>Satuan</th>
                                                    <th class="text-center">Stok</th>
                                                    <th>Keterangan</th>
                                                    <th width="10%" class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $no = 1;
                                                $query = "SELECT * FROM tb_barang_bergerak ORDER BY nama_barang ASC";
                                                $result = mysqli_query($koneksi, $query);

                                                while ($row = mysqli_fetch_assoc($result)):
                                                    $stok_tersedia = $row['stok'];
                                                ?>
                                                    <tr>
                                                        <td><?= $no++; ?></td>
                                                        <td><span class="badge badge-secondary"><?= $row['kode_barang']; ?></span></td>
                                                        <td class="font-weight-bold"><?= $row['nama_barang']; ?></td>
                                                        <td><?= $row['satuan']; ?></td>

                                                        <td class="text-center">
                                                            <?php if ($stok_tersedia > 0): ?>
                                                                <span class="badge badge-success"><?= $stok_tersedia; ?></span>
                                                            <?php else: ?>
                                                                <span class="badge badge-danger">Habis</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td><small><?= $row['keterangan']; ?></small></td>

                                                        <td class="text-center">
                                                            <?php if ($stok_tersedia > 0): ?>
                                                                <button class="btn btn-primary btn-sm btn-block" data-toggle="modal" data-target="#modalMinta<?= $row['id']; ?>">
                                                                    Minta
                                                                </button>
                                                            <?php else: ?>
                                                                <button class="btn btn-secondary btn-sm btn-block" disabled>Habis</button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>

                                                    <div class="modal fade" id="modalMinta<?= $row['id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Form Permintaan Barang</h5>
                                                                    <button class="close" data-dismiss="modal">&times;</button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <div class="alert alert-primary py-2">
                                                                            <strong><?= $row['nama_barang']; ?></strong> (Stok: <?= $row['stok']; ?>)
                                                                        </div>

                                                                        <input type="hidden" name="barang_id" value="<?= $row['id']; ?>">

                                                                        <div class="form-group">
                                                                            <label>Jumlah Permintaan</label>
                                                                            <input type="number" name="jumlah" class="form-control" min="1" max="<?= $row['stok']; ?>" required>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label>Tanggal Permintaan</label>
                                                                            <input type="date" name="tanggal_permintaan" class="form-control" value="<?= date('Y-m-d'); ?>" readonly>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label>Keperluan</label>
                                                                            <textarea name="keperluan" class="form-control" placeholder="Contoh: Untuk print laporan..." required></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                                        <button type="submit" name="minta" class="btn btn-primary">Ajukan</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
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