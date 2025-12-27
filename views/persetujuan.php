<?php
session_start();
require '../config/koneksi.php';

// 1. Cek Akses (Hanya Admin & Super Admin)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['role'] == 'user') {
    echo "<script>alert('Akses Ditolak!'); window.location='index.php';</script>";
    exit;
}

// ==========================================
// LOGIKA PHP: PROSES PERSETUJUAN & PENOLAKAN
// ==========================================

// A. PROSES PERSETUJUAN (Approve)
if (isset($_POST['setuju'])) {
    $id_permintaan   = $_POST['id_permintaan'];
    $id_barang       = $_POST['id_barang'];

    // Ambil data jumlah
    $jumlah_minta_awal = $_POST['jumlah_awal'];     // Jumlah asli permintaan user
    $jumlah_disetujui  = $_POST['jumlah_disetujui']; // Jumlah baru keputusan admin

    $catatan_admin   = mysqli_real_escape_string($koneksi, $_POST['catatan_admin']);
    $admin_id        = $_SESSION['user_id'];
    $tanggal_acc     = date('Y-m-d');

    // --- VALIDASI ---
    // 1. Cek apakah jumlah disetujui lebih besar dari permintaan? (Tidak boleh)
    if ($jumlah_disetujui > $jumlah_minta_awal) {
        echo "<script>alert('Gagal! Jumlah disetujui tidak boleh melebihi permintaan awal ($jumlah_minta_awal).');</script>";
    }
    // 2. Cek apakah jumlah disetujui LEBIH KECIL tapi Catatan KOSONG? (Wajib isi)
    elseif ($jumlah_disetujui < $jumlah_minta_awal && empty($catatan_admin)) {
        echo "<script>alert('Gagal! Karena Anda menyetujui jumlah yang lebih sedikit ($jumlah_disetujui dari $jumlah_minta_awal), Anda WAJIB mengisi kolom Catatan Admin sebagai penjelasan.');</script>";
    } else {
        // Cek Stok Gudang
        $cek_stok = mysqli_query($koneksi, "SELECT stok FROM tb_barang_bergerak WHERE id = '$id_barang'");
        $data_stok = mysqli_fetch_assoc($cek_stok);

        if ($data_stok['stok'] < $jumlah_disetujui) {
            echo "<script>alert('Gagal! Stok barang di gudang tidak mencukupi.');</script>";
        } else {
            // EKSEKUSI DATABASE

            // 1. Update Tabel Permintaan
            // Kita update kolom 'jumlah' dengan angka yang DISETUJUI saja
            $query_update = "UPDATE tb_permintaan SET 
                             status = 'disetujui', 
                             jumlah = '$jumlah_disetujui', 
                             tanggal_disetujui = '$tanggal_acc', 
                             admin_id = '$admin_id',
                             catatan = '$catatan_admin'
                             WHERE id = '$id_permintaan'";

            // 2. Kurangi Stok Barang sesuai jumlah yang DISETUJUI
            $query_kurang_stok = "UPDATE tb_barang_bergerak SET stok = stok - $jumlah_disetujui WHERE id = '$id_barang'";

            if (mysqli_query($koneksi, $query_update) && mysqli_query($koneksi, $query_kurang_stok)) {
                echo "<script>alert('Permintaan berhasil DISETUJUI sebanyak $jumlah_disetujui item.'); window.location='persetujuan.php';</script>";
            } else {
                echo "<script>alert('Error Database: " . mysqli_error($koneksi) . "');</script>";
            }
        }
    }
}

// B. PROSES PENOLAKAN (Reject)
if (isset($_POST['tolak'])) {
    $id_permintaan = $_POST['id_permintaan'];
    $catatan       = mysqli_real_escape_string($koneksi, $_POST['catatan']); // Alasan penolakan
    $admin_id      = $_SESSION['user_id'];
    $tanggal_acc   = date('Y-m-d');

    $query = "UPDATE tb_permintaan SET 
              status = 'ditolak', 
              tanggal_disetujui = '$tanggal_acc', 
              admin_id = '$admin_id',
              catatan = '$catatan' 
              WHERE id = '$id_permintaan'";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Permintaan berhasil DITOLAK!'); window.location='persetujuan.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($koneksi) . "');</script>";
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

                    <h1 class="h3 mb-2 text-gray-800">Persetujuan Permintaan</h1>
                    <p class="mb-4">Kelola permintaan barang masuk dari user (Staff).</p>

                    <div class="row">
                        <div class="col-lg-12">


                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Permintaan Menunggu Konfirmasi (Pending)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Tanggal</th>
                                                    <th>Pemohon</th>
                                                    <th>Barang</th>
                                                    <th>Jumlah</th>
                                                    <th>Keperluan</th>
                                                    <th>Stok Gudang</th>
                                                    <th width="15%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $no = 1;
                                                // Query Join: tb_permintaan + tb_user + tb_barang_bergerak
                                                $query = "SELECT p.*, p.id AS id_permintaan, u.nama AS nama_pemohon, b.nama_barang, b.stok AS stok_gudang, b.satuan 
                                  FROM tb_permintaan p 
                                  JOIN tb_user u ON p.user_id = u.id 
                                  JOIN tb_barang_bergerak b ON p.barang_id = b.id 
                                  WHERE p.status = 'menunggu' 
                                  ORDER BY p.tanggal_permintaan ASC";

                                                $result = mysqli_query($koneksi, $query);

                                                // Pesan jika kosong
                                                if (mysqli_num_rows($result) == 0) {
                                                    echo "<tr><td colspan='8' class='text-center text-muted py-4'>Tidak ada permintaan baru saat ini.</td></tr>";
                                                }

                                                while ($row = mysqli_fetch_assoc($result)):
                                                ?>
                                                    <tr>
                                                        <td><?= $no++; ?></td>
                                                        <td><?= date('d-m-Y', strtotime($row['tanggal_permintaan'])); ?></td>
                                                        <td class="font-weight-bold"><?= $row['nama_pemohon']; ?></td>
                                                        <td><?= $row['nama_barang']; ?></td>
                                                        <td class="text-center font-weight-bold text-primary" style="font-size: 1.1rem;"><?= $row['jumlah']; ?> <?= $row['satuan']; ?></td>
                                                        <td><small><?= $row['keperluan']; ?></small></td>

                                                        <td class="text-center">
                                                            <?php if ($row['stok_gudang'] >= $row['jumlah']): ?>
                                                                <span class="badge badge-success">Aman (<?= $row['stok_gudang']; ?>)</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-danger">Kurang (<?= $row['stok_gudang']; ?>)</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td class="text-center">
                                                            <button class="btn btn-success btn-sm btn-circle" data-toggle="modal" data-target="#modalSetuju<?= $row['id_permintaan']; ?>" title="Setujui">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button class="btn btn-danger btn-sm btn-circle" data-toggle="modal" data-target="#modalTolak<?= $row['id_permintaan']; ?>" title="Tolak">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </td>
                                                    </tr>

                                                    <div class="modal fade" id="modalSetuju<?= $row['id_permintaan']; ?>">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-success text-white">
                                                                    <h5 class="modal-title">Konfirmasi Persetujuan</h5>
                                                                    <button class="close text-white" data-dismiss="modal">&times;</button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <p>Anda akan menyetujui permintaan dari <strong><?= $row['nama_pemohon']; ?></strong>.</p>

                                                                        <div class="form-group row mb-2">
                                                                            <label class="col-sm-4 col-form-label">Nama Barang</label>
                                                                            <div class="col-sm-8">
                                                                                <input type="text" class="form-control-plaintext font-weight-bold" value="<?= $row['nama_barang']; ?>" readonly>
                                                                            </div>
                                                                        </div>

                                                                        <hr>

                                                                        <div class="form-group">
                                                                            <label class="font-weight-bold text-gray-800">Jumlah Disetujui</label>
                                                                            <div class="input-group">
                                                                                <input type="number" name="jumlah_disetujui" class="form-control font-weight-bold"
                                                                                    value="<?= $row['jumlah']; ?>"
                                                                                    max="<?= $row['jumlah']; ?>"
                                                                                    min="1" required>
                                                                                <div class="input-group-append">
                                                                                    <span class="input-group-text"><?= $row['satuan']; ?></span>
                                                                                </div>
                                                                            </div>
                                                                            <small class="text-muted mt-2 d-block">
                                                                                Permintaan Awal: <b><?= $row['jumlah']; ?></b>. <br>
                                                                                <span class="text-danger">*Anda boleh mengurangi jumlah ini (Persetujuan Parsial).</span>
                                                                            </small>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <label class="font-weight-bold text-gray-800">Catatan Admin</label>
                                                                            <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Tulis alasan jika jumlah yang disetujui lebih sedikit..."></textarea>
                                                                            <small class="text-muted">*Wajib diisi jika jumlah disetujui < permintaan awal.</small>
                                                                        </div>

                                                                        <input type="hidden" name="id_permintaan" value="<?= $row['id_permintaan']; ?>">
                                                                        <input type="hidden" name="id_barang" value="<?= $row['barang_id']; ?>">
                                                                        <input type="hidden" name="jumlah_awal" value="<?= $row['jumlah']; ?>">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                                        <button type="submit" name="setuju" class="btn btn-success">Proses Persetujuan</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal fade" id="modalTolak<?= $row['id_permintaan']; ?>">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-danger text-white">
                                                                    <h5 class="modal-title">Tolak Permintaan?</h5>
                                                                    <button class="close text-white" data-dismiss="modal">&times;</button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <p>Anda akan menolak permintaan dari <strong><?= $row['nama_pemohon']; ?></strong>.</p>

                                                                        <div class="form-group">
                                                                            <label class="font-weight-bold">Alasan Penolakan (Wajib):</label>
                                                                            <textarea name="catatan" class="form-control" rows="3" required placeholder="Contoh: Stok habis, permintaan tidak relevan..."></textarea>
                                                                        </div>

                                                                        <input type="hidden" name="id_permintaan" value="<?= $row['id_permintaan']; ?>">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                                        <button type="submit" name="tolak" class="btn btn-danger">Tolak Permintaan</button>
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


                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-secondary">Riwayat Persetujuan Terakhir</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered" width="100%" cellspacing="0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Tanggal Proses</th>
                                                    <th>Pemohon</th>
                                                    <th>Barang</th>
                                                    <th>Qty Akhir</th>
                                                    <th>Status</th>
                                                    <th>Admin</th>
                                                    <th>Catatan</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query_hist = "SELECT p.*, u.nama AS nama_pemohon, b.nama_barang, a.nama AS nama_admin, b.satuan
                                       FROM tb_permintaan p 
                                       JOIN tb_user u ON p.user_id = u.id 
                                       JOIN tb_barang_bergerak b ON p.barang_id = b.id
                                       LEFT JOIN tb_user a ON p.admin_id = a.id
                                       WHERE p.status != 'menunggu' 
                                       ORDER BY p.tanggal_disetujui DESC LIMIT 5";
                                                $res_hist = mysqli_query($koneksi, $query_hist);
                                                while ($hist = mysqli_fetch_assoc($res_hist)):
                                                ?>
                                                    <tr>
                                                        <td><?= date('d-m-Y', strtotime($hist['tanggal_disetujui'])); ?></td>
                                                        <td><?= $hist['nama_pemohon']; ?></td>
                                                        <td><?= $hist['nama_barang']; ?></td>
                                                        <td class="font-weight-bold"><?= $hist['jumlah']; ?> <?= $hist['satuan']; ?></td>
                                                        <td>
                                                            <?php if ($hist['status'] == 'disetujui'): ?>
                                                                <span class="badge badge-success">Disetujui</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-danger">Ditolak</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><small><?= $hist['nama_admin']; ?></small></td>
                                                        <td><small class="text-muted"><?= $hist['catatan']; ?></small></td>

                                                        <td class="text-center">
                                                            <?php if ($hist['status'] == 'disetujui'): ?>
                                                                <a href="cetak_surat.php?id=<?= $hist['id']; ?>" target="_blank" class="btn btn-info btn-sm" title="Cetak Surat">
                                                                    <i class="fas fa-print"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
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