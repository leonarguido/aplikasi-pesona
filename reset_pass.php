<?php
require 'config/koneksi.php';

// Password yang kita inginkan
$password_baru = 'admin123';

// Enkripsi password sesuai standar server kamu
$password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

// Update database
$username_target = 'admin';
$query = "UPDATE tb_user SET password = '$password_hash' WHERE username = '$username_target'";

if (mysqli_query($koneksi, $query)) {
    echo "<h1>SUKSES!</h1>";
    echo "Password untuk username <b>'$username_target'</b> berhasil di-reset.<br>";
    echo "Password baru: <b>$password_baru</b><br><br>";
    echo "<a href='login.php'>Klik disini untuk Login</a>";
} else {
    echo "Gagal reset password: " . mysqli_error($koneksi);
}
?>