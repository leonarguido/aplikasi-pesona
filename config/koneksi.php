<?php
// config/koneksi.php

$server   = "localhost";
$username = "root";      // Sesuaikan dengan user database kamu
$password = "";          // Sesuaikan dengan password database kamu
$database = "db_pesona_api"; // Sesuaikan dengan nama database yang sudah kamu buat

$koneksi = mysqli_connect($server, $username, $password, $database);

if (!$koneksi) {
    die("Gagal terhubung ke database: " . mysqli_connect_error());
}
?>