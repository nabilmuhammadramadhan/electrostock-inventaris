<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

include 'koneksi.php';
// 1. Panggil koneksi database
include 'koneksi.php';

$pesan_error = '';

// ==========================================
// PROSES SIMPAN TRANSAKSI & UPDATE STOK
// ==========================================
if (isset($_POST['simpan_transaksi'])) {
    $id_barang = $_POST['id_barang'];
    $jenis_transaksi = $_POST['jenis_transaksi'];
    $jumlah = (int)$_POST['jumlah'];
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // Validasi Keamanan: Cegah stok minus jika barang keluar
    if ($jenis_transaksi == 'keluar') {
        $q_cek_stok = mysqli_query($koneksi, "SELECT stok, nama_barang FROM barang WHERE id_barang = '$id_barang'");
        $data_stok = mysqli_fetch_assoc($q_cek_stok);
        
        if ($jumlah > $data_stok['stok']) {
            $pesan_error = "⚠️ GAGAL: Jumlah barang keluar ($jumlah unit) melebihi stok '{$data_stok['nama_barang']}' yang tersedia ({$data_stok['stok']} unit).";
        }
    }

    // Jika tidak ada error (validasi aman), maka proses ke database
    if (empty($pesan_error)) {
        // Langkah 1: Masukkan data riwayat ke tabel 'transaksi'
        $query_insert = "INSERT INTO transaksi (id_barang, jenis_transaksi, jumlah, keterangan) 
                         VALUES ('$id_barang', '$jenis_transaksi', '$jumlah', '$keterangan')";
        mysqli_query($koneksi, $query_insert);

        // Langkah 2: Update (Tambah/Kurang) stok di tabel 'barang'
        if ($jenis_transaksi == 'masuk') {
            mysqli_query($koneksi, "UPDATE barang SET stok = stok + $jumlah WHERE id_barang = '$id_barang'");
        } else if ($jenis_transaksi == 'keluar') {
            mysqli_query($koneksi, "UPDATE barang SET stok = stok - $jumlah WHERE id_barang = '$id_barang'");
        }

        // Langkah 3: Arahkan pengguna ke halaman riwayat agar bisa langsung melihat hasilnya
        header("Location: riwayat.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Transaksi - ElectroStock</title>
    <style>
        /* CSS Dasar Konsisten */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        
        /* Tombol */
        .btn { padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold; border: none; cursor: pointer; color: white; width: 100%; box-sizing: border-box; text-align: center; }
        .btn-primary { background-color: #0984e3; }
        .btn-primary:hover { background-color: #076bb8; }
        .btn-secondary { background-color: #636e72; width: auto; }
        
        /* Form */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .form-control:focus { outline: none; border-color: #0984e3; }
        
        /* Radio Button Kotak */
        .radio-group { display: flex; gap: 10px; }
        .radio-label { flex: 1; text-align: center; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; padding: 10px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .radio-label:hover { background: #eee; }
        input[type="radio"] { display: none; }
        
        /* Indikator Pilihan Transaksi */
        input[type="radio"]:checked + .radio-label.masuk { background-color: #00b894; color: white; border-color: #00b894; }
        input[type="radio"]:checked + .radio-label.keluar { background-color: #d63031; color: white; border-color: #d63031; }

        /* Pesan Error */
        .alert-danger { background-color: #ffcccc; color: #cc0000; padding: 15px; border-radius: 4px; border-left: 5px solid #cc0000; margin-bottom: 20px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2 style="margin: 0;">✍️ Catat Transaksi Baru</h2>
        <a href="index.php" class="btn btn-secondary" style="padding: 8px 12px; font-size: 14px;">⬅ Kembali</a>
    </div>

    <!-- Tampilkan pesan error jika validasi stok gagal -->
    <?php if (!empty($pesan_error)) { ?>
        <div class="alert-danger">
            <?php echo $pesan_error; ?>
        </div>
    <?php } ?>

    <form method="POST" action="transaksi.php">
        
        <div class="form-group">
            <label>Pilih Barang</label>
            <select name="id_barang" class="form-control" required>
                <option value="">-- Pilih Barang Elektronik --</option>
                <?php
                // Ambil daftar barang dari database untuk dropdown
                $q_barang = mysqli_query($koneksi, "SELECT id_barang, nama_barang, merek, stok FROM barang ORDER BY nama_barang ASC");
                while($b = mysqli_fetch_assoc($q_barang)) {
                    // Menampilkan nama, merek, dan sisa stok agar mempermudah pengguna
                    echo "<option value='".$b['id_barang']."'>".$b['nama_barang']." (".$b['merek'].") - Sisa Stok: ".$b['stok']."</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Jenis Transaksi</label>
            <div class="radio-group">
                <input type="radio" id="masuk" name="jenis_transaksi" value="masuk" required>
                <label for="masuk" class="radio-label masuk">📥 BARANG MASUK</label>

                <input type="radio" id="keluar" name="jenis_transaksi" value="keluar" required>
                <label for="keluar" class="radio-label keluar">📤 BARANG KELUAR</label>
            </div>
        </div>

        <div class="form-group">
            <label>Jumlah Barang (Unit)</label>
            <input type="number" name="jumlah" class="form-control" min="1" placeholder="Contoh: 5" required>
        </div>

        <div class="form-group">
            <label>Keterangan / Catatan (Opsional)</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Restock bulanan dari supplier..."></textarea>
        </div>

        <button type="submit" name="simpan_transaksi" class="btn btn-primary" style="font-size: 16px; padding: 12px;">💾 Simpan & Update Stok</button>
    </form>

</div>

</body>
</html>