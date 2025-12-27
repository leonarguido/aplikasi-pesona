<?php
// File: buat_admin.php
require '../config/koneksi.php';

// Data akun Super Admin yang akan dibuat
$username = "superadmin";
$password_asli = "password123"; 
$password_hash = password_hash($password_asli, PASSWORD_DEFAULT); // Enkripsi password
$fullname = "Super Administrator";
$role     = "super_admin";

// Cek dulu apakah user sudah ada biar tidak duplikat
$cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");

if(mysqli_num_rows($cek) > 0){
    echo "<h3>User superadmin sudah ada!</h3>";
    echo "Silakan langsung login.";
} else {
    // Masukkan ke database
    $query = "INSERT INTO users (username, password, full_name, role) 
              VALUES ('$username', '$password_hash', '$fullname', '$role')";
    
    if(mysqli_query($koneksi, $query)){
        echo "<h3>Berhasil membuat akun Super Admin!</h3>";
        echo "Username: <b>superadmin</b><br>";
        echo "Password: <b>password123</b><br>";
        echo "<br><a href='login.php'>Klik disini untuk Login</a>";
    } else {
        echo "Gagal: " . mysqli_error($koneksi);
    }
}
?>