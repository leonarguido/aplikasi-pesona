<?php
session_start();
require 'config/koneksi.php';

// Jika sudah login, lempar ke index
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // CEK TABEL BARU: tb_user
    $query = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username = '$username'");

    // Cek apakah username ada
    if (mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);
        
        // VERIFIKASI PASSWORD
        if (password_verify($password, $data['password'])) {
            
            // SET SESSION
            $_SESSION['user_id']   = $data['id'];
            $_SESSION['username']  = $data['username'];
            $_SESSION['full_name'] = $data['nama']; // Kolom 'nama'
            
            // MAPPING ROLE (Database Mentor -> Sistem Kita)
            $role_db = $data['role'];
            
            if ($role_db == 'super admin') {
                $_SESSION['role'] = 'super_admin';
            } elseif ($role_db == 'admin gudang') {
                $_SESSION['role'] = 'admin';
            } elseif ($role_db == 'staff') {
                $_SESSION['role'] = 'user'; 
            } else {
                $_SESSION['role'] = $role_db; // 'pimpinan'
            }

            // Login Sukses
            header("Location: index.php");
            exit;
        }
    }
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Aplikasi Pesona</title>
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Login Logistik</h1>
                            </div>
                            
                            <?php if (isset($error)) : ?>
                                <div class="alert alert-danger text-center" role="alert">
                                    Username atau Password salah!
                                </div>
                            <?php endif; ?>

                            <form class="user" method="POST">
                                <div class="form-group">
                                    <input type="text" name="username" class="form-control form-control-user" placeholder="Masukkan Username..." required>
                                </div>
                                
                                <div class="form-group">
                                    <div class="input-group">
                                        <input type="password" name="password" class="form-control" id="passwordInput" placeholder="Password" style="border-top-left-radius: 10rem; border-bottom-left-radius: 10rem; height: 3.1rem; padding: 1.5rem 1rem;" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-white border-left-0" style="border-top-right-radius: 10rem; border-bottom-right-radius: 10rem; cursor: pointer;" onclick="togglePassword()">
                                                <i class="fas fa-eye text-gray-500" id="toggleIcon"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" name="login" class="btn btn-primary btn-user btn-block">
                                    Masuk
                                </button>
                            </form>
                            
                            <hr>
                            <div class="text-center">
                                <small class="text-muted">Gunakan akun: <b>admin</b> / <b>admin123</b></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function togglePassword() {
        var input = document.getElementById("passwordInput");
        var icon = document.getElementById("toggleIcon");
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash"); // Ganti icon jadi mata dicoret
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye"); // Ganti icon jadi mata biasa
        }
    }
    </script>

    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="assets/js/sb-admin-2.min.js"></script>
</body>
</html>