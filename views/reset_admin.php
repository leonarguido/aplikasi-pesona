<?php
require '../config/koneksi.php';

$username = "superadmin";
$password_baru = "password123";
// Kita hash passwordnya sekarang
$password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

// 1. HAPUS user lama jika ada (supaya bersih)
$hapus = mysqli_query($koneksi, "DELETE FROM users WHERE username = '$username'");

// 2. Buat user BARU
$query = "INSERT INTO users (username, password, full_name, role) 
          VALUES ('$username', '$password_hash', 'Super Administrator', 'super_admin')";

if(mysqli_query($koneksi, $query)){
    echo "<h1>SUKSES!</h1>";
    echo "Akun superadmin berhasil di-reset.<br>";
    echo "Username: <b>superadmin</b><br>";
    echo "Password: <b>password123</b><br>";
    echo "<br>Hash di database: " . $password_hash; // Menampilkan hash untuk pengecekan
    echo "<br><br><a href='login.php'>Klik disini untuk Login Ulang</a>";
} else {
    echo "Gagal: " . mysqli_error($koneksi);
}
?>