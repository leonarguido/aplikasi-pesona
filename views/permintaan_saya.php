<?php
session_start();
require '../config/koneksi.php';

// 1. Cek Login (Hanya User/Staff)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil ID User yang sedang login
$id_user = $_SESSION['user_id'];
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

                    <h1 class="h3 mb-2 text-gray-800">Riwayat Permintaan Saya</h1>
                    <p class="mb-4">Berikut adalah daftar status permintaan barang yang pernah Anda ajukan.</p>

                    <div class="row">
                        <div class="col-lg-12">


                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Log Permintaan</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="5%">No</th>
                                                    <th>Tanggal Ajuan</th>
                                                    <th>Nama Barang</th>
                                                    <th>Jumlah</th>
                                                    <th>Keperluan</th>
                                                    <th class="text-center">Status & Aksi</th>
                                                    <th>Catatan Admin</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $no = 1;
                                                // Query Join ke tb_barang agar dapat nama barangnya
                                                // Filter WHERE user_id = '$id_user' agar hanya melihat data sendiri
                                                $query = "SELECT p.*, b.nama_barang, b.satuan 
                                  FROM tb_permintaan p 
                                  JOIN tb_barang_bergerak b ON p.barang_id = b.id 
                                  WHERE p.user_id = '$id_user' 
                                  ORDER BY p.tanggal_permintaan DESC";

                                                $result = mysqli_query($koneksi, $query);

                                                while ($row = mysqli_fetch_assoc($result)):
                                                ?>
                                                    <tr>
                                                        <td><?= $no++; ?></td>
                                                        <td><?= date('d-m-Y', strtotime($row['tanggal_permintaan'])); ?></td>
                                                        <td class="font-weight-bold"><?= $row['nama_barang']; ?></td>
                                                        <td><?= $row['jumlah']; ?> <?= $row['satuan']; ?></td>
                                                        <td><small><?= $row['keperluan']; ?></small></td>

                                                        <td class="text-center">
                                                            <?php
                                                            if ($row['status'] == 'menunggu') {
                                                                echo '<span class="badge badge-warning">Menunggu Konfirmasi</span>';
                                                            } elseif ($row['status'] == 'disetujui') {
                                                                // TAMPILAN JIKA DISETUJUI (Badge + Tombol Cetak)
                                                                echo '<span class="badge badge-success"><i class="fas fa-check"></i> Disetujui</span>';
                                                                echo '<br><small class="text-muted">' . date('d-m-Y', strtotime($row['tanggal_disetujui'])) . '</small>';

                                                                echo '<div class="mt-2">';
                                                                echo '<a href="cetak_surat.php?id=' . $row['id'] . '" target="_blank" class="btn btn-primary btn-sm shadow-sm">';
                                                                echo '<i class="fas fa-print fa-sm text-white-50"></i> Cetak Surat';
                                                                echo '</a>';
                                                                echo '</div>';
                                                            } elseif ($row['status'] == 'ditolak') {
                                                                echo '<span class="badge badge-danger"><i class="fas fa-times"></i> Ditolak</span>';
                                                            }
                                                            ?>
                                                        </td>

                                                        <td>
                                                            <?php if (!empty($row['catatan'])): ?>
                                                                <div class="alert alert-danger py-1 px-2 m-0" style="font-size: 0.85rem;">
                                                                    <strong>Info:</strong> <?= $row['catatan']; ?>
                                                                </div>
                                                            <?php else: ?>
                                                                <span class="text-muted text-center d-block">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
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