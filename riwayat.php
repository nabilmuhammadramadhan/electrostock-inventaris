<?php
// Wajib ada keamanan sesi
session_start();
if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

// 1. Panggil koneksi database
include 'koneksi.php';

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

// Proses Hapus Data Riwayat
if ($aksi == 'hapus') {
    $id_transaksi = $_GET['id'];
    
    $q_transaksi = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id_transaksi = '$id_transaksi'");
    if ($data_transaksi = mysqli_fetch_assoc($q_transaksi)) {
        $id_barang = $data_transaksi['id_barang'];
        $jumlah = $data_transaksi['jumlah'];
        $jenis = $data_transaksi['jenis_transaksi'];
        
        // Kembalikan stok ke kondisi semula
        if ($jenis == 'masuk') {
            mysqli_query($koneksi, "UPDATE barang SET stok = stok - $jumlah WHERE id_barang = '$id_barang'");
        } else if ($jenis == 'keluar') {
            mysqli_query($koneksi, "UPDATE barang SET stok = stok + $jumlah WHERE id_barang = '$id_barang'");
        }
        
        mysqli_query($koneksi, "DELETE FROM transaksi WHERE id_transaksi = '$id_transaksi'");
    }
    
    header("Location: riwayat.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - ElectroStock</title>
    <!-- Menambahkan Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        
        .btn { padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold; border: none; cursor: pointer; color: white; font-family: 'Poppins', sans-serif;}
        .btn-primary { background-color: #0984e3; }
        .btn-secondary { background-color: #636e72; }
        .btn-success { background-color: #00b894; }
        .btn-danger { background-color: #d63031; }
        .btn-action { padding: 5px 10px; font-size: 12px; margin-right: 2px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f8f9fa; }
        
        .badge { padding: 4px 8px; border-radius: 4px; color: white; font-weight: bold; font-size: 12px; text-transform: uppercase; }
        .badge-masuk { background-color: #00b894; }
        .badge-keluar { background-color: #d63031; }
        .badge-user { background-color: #f39c12; }

        .detail-card { background: #f8f9fa; padding: 20px; border-radius: 5px; border-left: 5px solid #6c5ce7; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>📜 Riwayat Transaksi Barang</h2>
        <a href="index.php" class="btn btn-secondary">⬅ Kembali ke Dashboard</a>
    </div>

    <?php
    if ($aksi == 'detail') {
        $id = $_GET['id'];
        
        // [BARU] Melakukan JOIN ketiga ke tabel users untuk mengambil nama karyawan
        $q_detail = mysqli_query($koneksi, "
            SELECT transaksi.*, barang.nama_barang, barang.merek, users.nama as nama_karyawan 
            FROM transaksi 
            JOIN barang ON transaksi.id_barang = barang.id_barang 
            LEFT JOIN users ON transaksi.id_user = users.id
            WHERE transaksi.id_transaksi = '$id'
        ");
        $data = mysqli_fetch_assoc($q_detail);
        
        $badge_class = ($data['jenis_transaksi'] == 'masuk') ? 'badge-masuk' : 'badge-keluar';
        
        // Mengamankan jika data transaksi lama belum memiliki user (misal transaksi sebelum fitur user dibuat)
        $nama_pencatat = !empty($data['nama_karyawan']) ? $data['nama_karyawan'] : 'Data Lama (Sistem)';
    ?>
        <h3>Detail Riwayat Transaksi</h3>
        <div class="detail-card">
            <p><strong>ID Transaksi:</strong> #TRX-<?php echo $data['id_transaksi']; ?></p>
            <p><strong>Waktu Transaksi:</strong> <?php echo date('d F Y, H:i', strtotime($data['tanggal'])); ?></p>
            
            <!-- FITUR BARU: Menampilkan Nama Karyawan di Detail -->
            <p><strong>Dicatat Oleh:</strong> <span class="badge badge-user">👤 <?php echo $nama_pencatat; ?></span></p>
            
            <hr style="border: 0; border-top: 1px solid #ccc; margin: 15px 0;">
            <p><strong>Nama Barang:</strong> <?php echo $data['nama_barang']; ?> (<?php echo $data['merek']; ?>)</p>
            <p><strong>Jenis Transaksi:</strong> <span class="badge <?php echo $badge_class; ?>"><?php echo $data['jenis_transaksi']; ?></span></p>
            <p><strong>Jumlah Barang:</strong> <span style="font-size: 18px; font-weight: bold;"><?php echo $data['jumlah']; ?> Unit</span></p>
            <p><strong>Keterangan / Catatan:</strong><br> 
                <i><?php echo empty($data['keterangan']) ? 'Tidak ada catatan' : nl2br($data['keterangan']); ?></i>
            </p>
        </div>
        <br>
        <a href="riwayat.php" class="btn btn-primary">Kembali ke Tabel Riwayat</a>

    <?php
    } else {
    ?>
        <table>
            <thead>
                <tr>
                    <th>Waktu (Tanggal)</th>
                    <th>Nama Barang</th>
                    <th>Jenis</th>
                    <th>Jumlah</th>
                    <th>Pencatat (User)</th> <!-- Kolom Baru -->
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // [BARU] Melakukan JOIN ke tabel users untuk daftar tabel
                $query_tampil = "
                    SELECT transaksi.*, barang.nama_barang, users.nama as nama_karyawan
                    FROM transaksi 
                    JOIN barang ON transaksi.id_barang = barang.id_barang 
                    LEFT JOIN users ON transaksi.id_user = users.id
                    ORDER BY transaksi.tanggal DESC
                ";
                $result = mysqli_query($koneksi, $query_tampil);
                
                while($row = mysqli_fetch_assoc($result)) {
                    $badge_class = ($row['jenis_transaksi'] == 'masuk') ? 'badge-masuk' : 'badge-keluar';
                    $nama_pencatat_tabel = !empty($row['nama_karyawan']) ? $row['nama_karyawan'] : 'Data Lama (Sistem)';
                ?>
                <tr>
                    <td><?php echo date('d M Y, H:i', strtotime($row['tanggal'])); ?></td>
                    <td><?php echo $row['nama_barang']; ?></td>
                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo $row['jenis_transaksi']; ?></span></td>
                    <td><strong><?php echo $row['jumlah']; ?></strong></td>
                    
                    <!-- Menampilkan Siapa yang Mencatat -->
                    <td style="font-size: 13px; color: #555;">👤 <?php echo $nama_pencatat_tabel; ?></td>
                    
                    <td>
                        <a href="riwayat.php?aksi=detail&id=<?php echo $row['id_transaksi']; ?>" class="btn btn-success btn-action">🔍 Detail</a>
                        <a href="riwayat.php?aksi=hapus&id=<?php echo $row['id_transaksi']; ?>" onclick="return confirm('Yakin hapus riwayat ini? Peringatan: Stok barang akan dikembalikan (revert) ke sebelum transaksi ini terjadi.');" class="btn btn-danger btn-action">🗑️ Hapus</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

</div>

</body>
</html>