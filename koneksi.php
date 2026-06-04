<?php
// Pengaturan default database Laragon
$host = "localhost";
$user = "root";
$pass = ""; // Password default di Laragon biasanya dibiarkan kosong
$db   = "db_inventaris"; // Nama database yang kamu buat tadi

// Membuat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Mengecek apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>