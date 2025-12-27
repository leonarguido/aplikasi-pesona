<?php
session_start();
require '../config/koneksi.php';

// 1. Cek Login & Akses
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Hanya Super Admin yang boleh akses halaman ini
if ($_SESSION['role'] != 'super_admin') {
    echo "<script>alert('Akses Ditolak!'); window.location='index.php';</script>";
    exit;
}

// --- LOGIKA CRUD ---

// A. TAMBAH USER
if (isset($_POST['tambah'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role     = $_POST['role'];
    $nip      = $_POST['nip'];
    // Password default user baru: '123456' (bisa diganti user nanti)
    $password = password_hash("123456", PASSWORD_DEFAULT);

    // Cek Username Kembar
    $cek = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah digunakan!');</script>";
    } else {
        $query = "INSERT INTO tb_user (nama, nip, username, password, role) VALUES ('$nama', '$nip', '$username', '$password', '$role')";
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('User berhasil ditambahkan! Password default: 123456'); window.location='data_pengguna.php';</script>";
        } else {
            echo "<script>alert('Gagal: " . mysqli_error($koneksi) . "');</script>";
        }
    }
}

// B. EDIT USER
if (isset($_POST['edit'])) {
    $id       = $_POST['id'];
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $role     = $_POST['role'];
    $nip      = $_POST['nip'];

    // Jika password diisi, update password. Jika kosong, biarkan password lama.
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "UPDATE tb_user SET nama='$nama', nip='$nip', role='$role', password='$password' WHERE id='$id'";
    } else {
        $query = "UPDATE tb_user SET nama='$nama', nip='$nip', role='$role' WHERE id='$id'";
    }

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data pengguna berhasil diupdate!'); window.location='data_pengguna.php';</script>";
    }
}

// C. HAPUS USER
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Cegah hapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        echo "<script>alert('Anda tidak bisa menghapus akun sendiri!'); window.location='data_pengguna.php';</script>";
    } else {
        $query = "DELETE FROM tb_user WHERE id = '$id'";
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('User berhasil dihapus!'); window.location='data_pengguna.php';</script>";
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

                    <h1 class="h3 mb-2 text-gray-800">Kelola Pengguna</h1>
                    <p class="mb-4">Manajemen akun untuk Admin, Pimpinan, dan Staff.</p>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah">
                                        <i class="fas fa-plus"></i> Tambah User Baru
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Lengkap</th>
                                                    <th>NIP</th>
                                                    <th>Username</th>
                                                    <th>Role</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $no = 1;
                                                // Query ke tabel BARU: tb_user
                                                $query = mysqli_query($koneksi, "SELECT * FROM tb_user ORDER BY id DESC");
                                                while ($row = mysqli_fetch_assoc($query)):
                                                ?>
                                                    <tr>
                                                        <td><?= $no++; ?></td>
                                                        <td><?= $row['nama']; ?></td>
                                                        <td><?= $row['nip']; ?></td>
                                                        <td><?= $row['username']; ?></td>
                                                        <td>
                                                            <?php
                                                            // Mempercantik tampilan Role
                                                            if ($row['role'] == 'super admin') echo '<span class="badge badge-danger">Super Admin</span>';
                                                            elseif ($row['role'] == 'admin gudang') echo '<span class="badge badge-primary">Admin Gudang</span>';
                                                            elseif ($row['role'] == 'pimpinan') echo '<span class="badge badge-warning">Pimpinan</span>';
                                                            else echo '<span class="badge badge-secondary">Staff/User</span>';
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalEdit<?= $row['id']; ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <a href="data_pengguna.php?hapus=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus user ini?');">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>

                                                    <div class="modal fade" id="modalEdit<?= $row['id']; ?>">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Edit User</h5>
                                                                    <button class="close" data-dismiss="modal">&times;</button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                                                        <div class="form-group">
                                                                            <label>Nama Lengkap</label>
                                                                            <input type="text" name="nama" class="form-control" value="<?= $row['nama']; ?>" required>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>NIP</label>
                                                                            <input type="number" name="nip" class="form-control" value="<?= $row['nip']; ?>">
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Role</label>
                                                                            <select name="role" class="form-control">
                                                                                <option value="super admin" <?= ($row['role'] == 'super admin') ? 'selected' : ''; ?>>Super Admin</option>
                                                                                <option value="admin gudang" <?= ($row['role'] == 'admin gudang') ? 'selected' : ''; ?>>Admin Gudang</option>
                                                                                <option value="pimpinan" <?= ($row['role'] == 'pimpinan') ? 'selected' : ''; ?>>Pimpinan</option>
                                                                                <option value="staff" <?= ($row['role'] == 'staff') ? 'selected' : ''; ?>>Staff/User</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Reset Password (Opsional)</label>
                                                                            <input type="password" name="password" class="form-control" placeholder="Isi jika ingin ganti password">
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                                        <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
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


    <div class="modal fade" id="modalTambah">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User Baru</h5>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>NIP</label>
                            <input type="number" name="nip" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control">
                                <option value="staff">Staff/User</option>
                                <option value="admin gudang">Admin Gudang</option>
                                <option value="pimpinan">Pimpinan</option>
                                <option value="super admin">Super Admin</option>
                            </select>
                        </div>
                        <small class="text-muted">*Password default user baru adalah: <b>123456</b></small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require 'layout/footer.php'; ?>
</body>

</html>