<?php
session_start();
require 'config/koneksi.php';

// Cek Login & Role
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['role'] == 'user' || $_SESSION['role'] == 'pimpinan') {
    echo "<script>alert('Anda tidak memiliki akses!'); window.location='index.php';</script>";
    exit;
}

// --- LOGIKA PHP (CRUD & IMPORT) ---

// A. LOGIKA IMPORT CSV (EXCEL)
if (isset($_POST['import_excel'])) {
    // Cek apakah file diupload
    if (isset($_FILES['file_excel']['name']) && $_FILES['file_excel']['name'] != "") {
        
        $filename = $_FILES['file_excel']['tmp_name'];
        $ext = pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION);

        // Validasi Ekstensi harus CSV
        if ($ext != 'csv') {
            echo "<script>alert('Format file harus .CSV (Comma Separated Values)!');</script>";
        } else {
            $file = fopen($filename, "r");
            $count = 0; // Hitung data sukses
            
            // Lewati baris pertama (Header Judul) agar tidak ikut ter-input
            fgetcsv($file); 

            while (($data = fgetcsv($file, 10000, ",")) !== FALSE) {
                // Mapping Data dari Excel/CSV ke Variabel
                // Kolom 0: Kode, 1: Nama, 2: Satuan, 3: Stok, 4: Ket
                $kode   = mysqli_real_escape_string($koneksi, $data[0]);
                $nama   = mysqli_real_escape_string($koneksi, $data[1]);
                $satuan = mysqli_real_escape_string($koneksi, $data[2]);
                $stok   = (int) $data[3];
                $desc   = mysqli_real_escape_string($koneksi, $data[4]);

                // Cek Duplikat Kode Barang
                $cek = mysqli_query($koneksi, "SELECT kode_barang FROM tb_barang_bergerak WHERE kode_barang = '$kode'");
                
                if (mysqli_num_rows($cek) == 0 && !empty($kode)) {
                    // Jika kode belum ada, Insert Baru
                    $query = "INSERT INTO tb_barang_bergerak (kode_barang, nama_barang, satuan, stok, keterangan) 
                              VALUES ('$kode', '$nama', '$satuan', '$stok', '$desc')";
                    mysqli_query($koneksi, $query);
                    $count++;
                }
            }
            fclose($file);
            echo "<script>alert('Berhasil mengimpor $count data barang!'); window.location='data_barang.php';</script>";
        }
    } else {
        echo "<script>alert('Pilih file terlebih dahulu!');</script>";
    }
}

// B. Tambah Barang Manual
if (isset($_POST['tambah'])) {
    $kode   = $_POST['kode_barang'];
    $nama   = $_POST['nama_barang'];
    $satuan = $_POST['satuan'];
    $desc   = $_POST['keterangan']; 
    $stok   = $_POST['stok'];

    $cek = mysqli_query($koneksi, "SELECT * FROM tb_barang_bergerak WHERE kode_barang = '$kode'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Kode Barang sudah ada!');</script>";
    } else {
        $query = "INSERT INTO tb_barang_bergerak (kode_barang, nama_barang, satuan, keterangan, stok) 
                  VALUES ('$kode', '$nama', '$satuan', '$desc', '$stok')";
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Barang berhasil ditambahkan!'); window.location='data_barang.php';</script>";
        } else {
            echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}

// C. Edit Barang
if (isset($_POST['edit'])) {
    $id     = $_POST['id'];
    $nama   = $_POST['nama_barang'];
    $satuan = $_POST['satuan'];
    $desc   = $_POST['keterangan'];
    $stok   = $_POST['stok'];
    
    $query = "UPDATE tb_barang_bergerak SET nama_barang='$nama', satuan='$satuan', keterangan='$desc', stok='$stok' WHERE id='$id'";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data berhasil diupdate!'); window.location='data_barang.php';</script>";
    }
}

// D. Hapus Barang
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $query = "DELETE FROM tb_barang_bergerak WHERE id = '$id'";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Barang berhasil dihapus!'); window.location='data_barang.php';</script>";
    }
}
?>

<?php 
require 'layout/header.php';
require 'layout/sidebar.php';
require 'layout/topbar.php'; 
?>

<div class="container-fluid">

    <h1 class="h3 mb-2 text-gray-800">Data Barang Bergerak</h1>
    <p class="mb-4">Kelola stok barang, tambah manual, atau import via Excel.</p>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#tambahModal">
                    <i class="fas fa-plus"></i> Tambah Manual
                </button>
                <button class="btn btn-success btn-sm ml-2" data-toggle="modal" data-target="#importModal">
                    <i class="fas fa-file-excel"></i> Import Excel
                </button>
            </div>
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
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = "SELECT * FROM tb_barang_bergerak ORDER BY nama_barang ASC";
                        $data = mysqli_query($koneksi, $query);
                        
                        while ($row = mysqli_fetch_assoc($data)): 
                        ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><span class="badge badge-secondary"><?= $row['kode_barang']; ?></span></td>
                            <td class="font-weight-bold"><?= $row['nama_barang']; ?></td>
                            <td><?= $row['satuan']; ?></td>
                            <td class="text-center">
                                <?php if($row['stok'] > 0): ?>
                                    <span class="badge badge-success" style="font-size: 1rem;"><?= $row['stok']; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?= $row['keterangan']; ?></small></td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $row['id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="data_barang.php?hapus=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus barang ini?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <div class="modal fade" id="editModal<?= $row['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Barang</h5>
                                        <button class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                            <div class="form-group">
                                                <label>Nama Barang</label>
                                                <input type="text" name="nama_barang" class="form-control" value="<?= $row['nama_barang']; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Satuan</label>
                                                <select name="satuan" class="form-control">
                                                    <option value="Unit" <?= ($row['satuan']=='Unit')?'selected':''; ?>>Unit</option>
                                                    <option value="Pcs" <?= ($row['satuan']=='Pcs')?'selected':''; ?>>Pcs</option>
                                                    <option value="Buah" <?= ($row['satuan']=='Buah')?'selected':''; ?>>Buah</option>
                                                    <option value="Rim" <?= ($row['satuan']=='Rim')?'selected':''; ?>>Rim</option>
                                                    <option value="Box" <?= ($row['satuan']=='Box')?'selected':''; ?>>Box</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Stok</label>
                                                <input type="number" name="stok" class="form-control" value="<?= $row['stok']; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Keterangan</label>
                                                <textarea name="keterangan" class="form-control"><?= $row['keterangan']; ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
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

<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data Barang (Excel/CSV)</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Tutorial / Cara Penggunaan:</strong>
                        <ol class="pl-3 mb-0">
                            <li>Download template Excel terlebih dahulu: <br> 
                                <a href="template_barang.php" class="btn btn-sm btn-success mt-1"><i class="fas fa-download"></i> Download Template</a>
                            </li>
                            <li>Buka file tersebut, isi data barang sesuai kolom.</li>
                            <li><strong>JANGAN</strong> mengubah judul kolom (Baris 1).</li>
                            <li>Simpan file (Save As) dengan format <strong>.CSV (Comma delimited)</strong>.</li>
                            <li>Upload file .csv tersebut di bawah ini.</li>
                        </ol>
                    </div>
                    
                    <div class="form-group">
                        <label>Pilih File CSV</label>
                        <input type="file" name="file_excel" class="form-control-file" required accept=".csv">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="import_excel" class="btn btn-primary">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Barang Baru</h5>
                <button class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Kode Barang (Unik)</label>
                        <input type="text" name="kode_barang" class="form-control" placeholder="Cth: KTS-001" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Barang</label>
                        <input type="text" name="nama_barang" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Satuan</label>
                        <select name="satuan" class="form-control">
                            <option value="Pcs">Pcs</option>
                            <option value="Unit">Unit</option>
                            <option value="Rim">Rim</option>
                            <option value="Box">Box</option>
                            <option value="Buah">Buah</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Stok Awal</label>
                        <input type="number" name="stok" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require 'layout/footer.php'; ?>