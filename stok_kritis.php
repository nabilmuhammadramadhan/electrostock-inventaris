<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

include 'koneksi.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'koneksi.php';
// 1. Panggil koneksi database
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Kritis - ElectroStock</title>
    <style>
        /* CSS Dasar (Seragam dengan halaman lain) */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        
        /* Tombol & Tabel */
        .btn { padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold; border: none; cursor: pointer; color: white; }
        .btn-secondary { background-color: #636e72; }
        .btn-success { background-color: #00b894; }
        .btn-action { padding: 5px 10px; font-size: 12px; margin-right: 2px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f8f9fa; }
        
        /* Highlight khusus untuk stok kritis */
        .stok-bahaya { color: #d63031; font-weight: bold; font-size: 16px; }
        
        /* Pesan Aman */
        .alert-aman { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; border-left: 5px solid #28a745; margin-top: 20px; font-weight: bold; text-align: center; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2 style="margin: 0; color: #d63031;">⚠️ Laporan Stok Menipis</h2>
        <a href="index.php" class="btn btn-secondary">⬅ Kembali ke Dashboard</a>
    </div>
    
    <p>Berikut adalah daftar barang elektronik yang stoknya saat ini berada di angka <strong>5 unit atau kurang</strong>. Segera lakukan restock ulang!</p>

    <?php
    // Query untuk mengambil barang yang stoknya <= 5, diurutkan dari yang paling sedikit
    $query_kritis = "
        SELECT barang.*, kategori.nama_kategori 
        FROM barang 
        JOIN kategori ON barang.id_kategori = kategori.id_kategori 
        WHERE barang.stok <= 5 
        ORDER BY barang.stok ASC
    ";
    $result = mysqli_query($koneksi, $query_kritis);
    
    // Mengecek apakah ada data yang stoknya kritis
    if (mysqli_num_rows($result) > 0) {
    ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Merek</th>
                    <th>Kategori</th>
                    <th>Sisa Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                while($row = mysqli_fetch_assoc($result)) {
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $row['nama_barang']; ?></td>
                    <td><?php echo $row['merek']; ?></td>
                    <td><?php echo $row['nama_kategori']; ?></td>
                    <td class="stok-bahaya"><?php echo $row['stok']; ?> Unit</td>
                    <td>
                        <!-- Tombol Detail mengarah ke file barang.php dengan membawa ID barang -->
                        <a href="barang.php?aksi=detail&id=<?php echo $row['id_barang']; ?>" class="btn btn-success btn-action">🔍 Lihat Detail</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php 
    } else { 
        // Jika tidak ada barang yang stoknya 5 ke bawah
    ?>
        <div class="alert-aman">
            🎉 Mantap! Semua stok barang elektronik saat ini dalam kondisi aman (di atas 5 unit).
        </div>
    <?php } ?>

</div>

</body>
</html>