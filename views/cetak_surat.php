<?php
session_start();
require '../config/koneksi.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$id_permintaan = $_GET['id'];

// Ambil Data Lengkap (Permintaan + User Pemohon + User Admin + Barang)
$query = "SELECT p.*, 
          u_pemohon.nama AS nama_pemohon, u_pemohon.ttd AS ttd_pemohon,
          u_admin.nama AS nama_admin, u_admin.ttd AS ttd_admin,
          b.nama_barang, b.satuan, b.kode_barang
          FROM tb_permintaan p
          JOIN tb_user u_pemohon ON p.user_id = u_pemohon.id
          JOIN tb_barang_bergerak b ON p.barang_id = b.id
          LEFT JOIN tb_user u_admin ON p.admin_id = u_admin.id
          WHERE p.id = '$id_permintaan'";

$result = mysqli_query($koneksi, $query);
$data   = mysqli_fetch_assoc($result);

// Validasi: Hanya bisa dicetak jika sudah DISETUJUI
if ($data['status'] != 'disetujui') {
    echo "<script>alert('Surat belum bisa dicetak karena status belum disetujui!'); window.location='index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Bukti Permintaan Barang - <?= $data['kode_barang']; ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; margin: 0; padding: 20px; }
        .container { width: 100%; max-width: 800px; margin: auto; }
        .header { text-align: center; border-bottom: 3px double black; padding-bottom: 10px; margin-bottom: 20px; }
        .header h2, .header h3 { margin: 0; }
        .content { margin-bottom: 30px; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-data th, .table-data td { border: 1px solid black; padding: 8px; text-align: left; }
        .ttd-wrapper { width: 100%; display: table; margin-top: 50px; }
        .ttd-box { display: table-cell; width: 50%; text-align: center; vertical-align: bottom; }
        .img-ttd { width: 150px; height: auto; display: block; margin: 10px auto; }
        
        /* Tombol print hilang saat diprint */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <button onclick="window.print()" class="no-print" style="padding: 10px 20px; margin-bottom: 20px; cursor: pointer;">🖨️ Cetak Surat</button>

    <div class="header">
        <h2>APLIKASI PESONA</h2>
        <h3>BUKTI SERAH TERIMA BARANG (ATK)</h3>
        <small>Jl. Contoh Alamat Kantor No. 123, Kota Denpasar</small>
    </div>

    <div class="content">
        <p>Pada hari ini, <strong><?= date('d F Y', strtotime($data['tanggal_disetujui'])); ?></strong>, telah disetujui permintaan barang dengan rincian sebagai berikut:</p>

        <table style="width: 100%; margin-bottom: 20px;">
            <tr><td width="20%">No. Transaksi</td><td>: #REQ-<?= sprintf("%04d", $data['id']); ?></td></tr>
            <tr><td>Pemohon</td><td>: <strong><?= $data['nama_pemohon']; ?></strong></td></tr>
            <tr><td>Tanggal Ajuan</td><td>: <?= date('d-m-Y', strtotime($data['tanggal_permintaan'])); ?></td></tr>
            <tr><td>Keperluan</td><td>: <?= $data['keperluan']; ?></td></tr>
        </table>

        <p>Rincian Barang:</p>
        <table class="table-data">
            <thead>
                <tr style="background-color: #eee;">
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Jumlah Disetujui</th>
                    <th>Satuan</th>
                    <th>Catatan Admin</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= $data['kode_barang']; ?></td>
                    <td><?= $data['nama_barang']; ?></td>
                    <td><?= $data['jumlah']; ?></td>
                    <td><?= $data['satuan']; ?></td>
                    <td><?= !empty($data['catatan']) ? $data['catatan'] : '-'; ?></td>
                </tr>
            </tbody>
        </table>

        <p>Demikian surat bukti permintaan barang ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
    </div>

    <div class="ttd-wrapper">
        <div class="ttd-box">
            <p>Pemohon,</p>
            <?php if(!empty($data['ttd_pemohon']) && file_exists('assets/img/ttd/'.$data['ttd_pemohon'])): ?>
                <img src="<?= BASE_URL ?>assets/img/ttd/<?= $data['ttd_pemohon']; ?>" class="img-ttd">
            <?php else: ?>
                <br><br><br><br>
                <small>(Belum Upload TTD)</small><br>
            <?php endif; ?>
            <strong>( <?= $data['nama_pemohon']; ?> )</strong>
        </div>

        <div class="ttd-box">
            <p>Disetujui Oleh,<br>Admin Gudang</p>
            <?php if(!empty($data['ttd_admin']) && file_exists('assets/img/ttd/'.$data['ttd_admin'])): ?>
                <img src="<?= BASE_URL ?>assets/img/ttd/<?= $data['ttd_admin']; ?>" class="img-ttd">
            <?php else: ?>
                <br><br><br><br>
                <small>(Belum Upload TTD)</small><br>
            <?php endif; ?>
            <strong>( <?= $data['nama_admin']; ?> )</strong>
        </div>
    </div>
</div>

</body>
</html>