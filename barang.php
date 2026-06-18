<?php
// 1. Panggil koneksi database
include 'koneksi.php';

// Menangkap parameter 'aksi' di URL. Jika tidak ada, default-nya adalah kosong (tampil tabel)
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

// ==========================================
// PROSES LOGIKA (SIMPAN, UPDATE, HAPUS BARANG & KATEGORI)
// ==========================================

// Jika tombol "Simpan Data" (Form Tambah Barang) diklik
if (isset($_POST['simpan_tambah'])) {

    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $merek = mysqli_real_escape_string($koneksi, $_POST['merek']);
    $spesifikasi = mysqli_real_escape_string($koneksi, $_POST['spesifikasi']);
    $id_kategori = $_POST['id_kategori'];

    $oleh = $_SESSION['nama'];

    $query = "INSERT INTO barang
             (nama_barang, merek, spesifikasi, id_kategori, ditambahkan_oleh)
             VALUES
             ('$nama', '$merek', '$spesifikasi', '$id_kategori', '$oleh')";

    mysqli_query($koneksi, $query);

    header("Location: barang.php");
    exit;
}

// [BARU] Jika tombol "Simpan Kategori" (Form Tambah Kategori) diklik
if (isset($_POST['simpan_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    
    // Simpan ke database kategori
    $query_kategori = "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')";
    mysqli_query($koneksi, $query_kategori);
    
    // Setelah selesai buat kategori, arahkan kembali ke form tambah barang 
    // agar bisa langsung dipakai
    header("Location: barang.php?aksi=tambah");
    exit;
}

// Jika tombol "Simpan Perubahan" (Form Edit Barang) diklik
if (isset($_POST['simpan_edit'])) {
    $id = $_POST['id_barang'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $merek = mysqli_real_escape_string($koneksi, $_POST['merek']);
    $spesifikasi = mysqli_real_escape_string($koneksi, $_POST['spesifikasi']);
    $id_kategori = $_POST['id_kategori'];

    $query = "UPDATE barang SET nama_barang='$nama', merek='$merek', spesifikasi='$spesifikasi', id_kategori='$id_kategori' WHERE id_barang='$id'";
    mysqli_query($koneksi, $query);
    header("Location: barang.php");
    exit;
}

// Proses hapus barang
if ($aksi == 'hapus') {
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang='$id'");
    header("Location: barang.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Barang - ElectroStock</title>
    <style>
        /* CSS Dasar */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        
        /* Tombol */
        .btn { padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold; border: none; cursor: pointer; color: white; }
        .btn-primary { background-color: #0984e3; }
        .btn-secondary { background-color: #636e72; }
        .btn-success { background-color: #00b894; }
        .btn-warning { background-color: #fdcb6e; color: #2d3436; }
        .btn-danger { background-color: #d63031; }
        .btn-action { padding: 5px 10px; font-size: 12px; margin-right: 3px;}
        
        /* Form & Tabel */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; }
        
        /* Table Detail */
        .table-detail { width: 100%; border: none; }
        .table-detail td { border: none; padding: 8px 0; border-bottom: 1px dashed #eee; }
    </style>
</head>
<body>

<div class="container">

    <?php 
    // ==========================================
    // ANTARMUKA HALAMAN TAMBAH BARANG
    // ==========================================
    if ($aksi == 'tambah') { 
    ?>
        <div class="header">
            <h2>Tambah Data Barang</h2>
            <a href="barang.php" class="btn btn-secondary">⬅️ Kembali</a>
        </div>
        <form action="barang.php" method="POST">
            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" class="form-control" required placeholder="Contoh: Laptop Thinkpad T480">
            </div>
            <div class="form-group">
                <label>Merek</label>
                <input type="text" name="merek" class="form-control" required placeholder="Contoh: Lenovo">
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="id_kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php
                    $q_kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
                    while($k = mysqli_fetch_assoc($q_kategori)) {
                        echo "<option value='".$k['id_kategori']."'>".$k['nama_kategori']."</option>";
                    }
                    ?>
                </select>
                <small style="display:block; margin-top:6px; color:#555;">
                    Kategori tidak ada di daftar? <a href="barang.php?aksi=tambah_kategori" style="color:#0984e3; font-weight:bold;">➕ Tambah Kategori Baru</a>
                </small>
            </div>
            <div class="form-group">
                <label>Spesifikasi</label>
                <textarea name="spesifikasi" class="form-control" rows="4" placeholder="Tuliskan spesifikasi produk (RAM, Prosesor, Warna, dll)"></textarea>
            </div>
            <button type="submit" name="simpan_tambah" class="btn btn-primary">💾 Simpan Data Barang</button>
        </form>

    <?php 
    // ==========================================
    // ANTARMUKA HALAMAN TAMBAH KATEGORI BARU
    // ==========================================
    } else if ($aksi == 'tambah_kategori') { 
    ?>
        <div class="header">
            <h2>Tambah Kategori Baru</h2>
            <a href="javascript:history.back()" class="btn btn-secondary">⬅️ Kembali</a>
        </div>
        <form action="barang.php" method="POST">
            <div class="form-group">
                <label>Nama Kategori</label>
                <input type="text" name="nama_kategori" class="form-control" required placeholder="Contoh: Aksesoris Komputer, Handphone, dll">
            </div>
            <button type="submit" name="simpan_kategori" class="btn btn-success">💾 Simpan Kategori</button>
        </form>

    <?php 
    // ==========================================
    // ANTARMUKA HALAMAN EDIT BARANG
    // ==========================================
    } else if ($aksi == 'edit') { 
        $id = $_GET['id'];
        $query_edit = mysqli_query($koneksi, "SELECT * FROM barang WHERE id_barang='$id'");
        $data_edit = mysqli_fetch_assoc($query_edit);
    ?>
        <div class="header">
            <h2>Edit Data Barang</h2>
            <a href="barang.php" class="btn btn-secondary">⬅️ Kembali</a>
        </div>
        <form action="barang.php" method="POST">
            <input type="hidden" name="id_barang" value="<?php echo $data_edit['id_barang']; ?>">
            
            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" class="form-control" value="<?php echo $data_edit['nama_barang']; ?>" required>
            </div>
            <div class="form-group">
                <label>Merek</label>
                <input type="text" name="merek" class="form-control" value="<?php echo $data_edit['merek']; ?>" required>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="id_kategori" class="form-control" required>
                    <?php
                    $q_kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
                    while($k = mysqli_fetch_assoc($q_kategori)) {
                        // Cek apakah id_kategori sama dengan kategori barang yang sedang diedit
                        $selected = ($k['id_kategori'] == $data_edit['id_kategori']) ? "selected" : "";
                        echo "<option value='".$k['id_kategori']."' $selected>".$k['nama_kategori']."</option>";
                    }
                    ?>
                </select>
                <small style="display:block; margin-top:6px; color:#555;">
                    Kategori tidak ada? <a href="barang.php?aksi=tambah_kategori" style="color:#0984e3; font-weight:bold;">➕ Tambah Kategori Baru</a>
                </small>
            </div>
            <div class="form-group">
                <label>Spesifikasi</label>
                <textarea name="spesifikasi" class="form-control" rows="4"><?php echo $data_edit['spesifikasi']; ?></textarea>
            </div>
            <button type="submit" name="simpan_edit" class="btn btn-warning">💾 Simpan Perubahan</button>
        </form>

    <?php 
    // ==========================================
    // ANTARMUKA DETAIL BARANG
    // ==========================================
    } else if ($aksi == 'detail') { 
        $id = $_GET['id'];
        $query_detail = mysqli_query($koneksi, "
            SELECT barang.*, kategori.nama_kategori 
            FROM barang 
            JOIN kategori ON barang.id_kategori = kategori.id_kategori 
            WHERE barang.id_barang='$id'
        ");
        $data_detail = mysqli_fetch_assoc($query_detail);
    ?>
        <div class="header">
            <h2>Detail Informasi Barang</h2>
            <a href="barang.php" class="btn btn-secondary">⬅️ Kembali</a>
        </div>
        <table class="table-detail">
            <tr>
                <td width="25%"><strong>Nama Barang</strong></td>
                <td width="5%">:</td>
                <td><?php echo $data_detail['nama_barang']; ?></td>
            </tr>
            <tr>
                <td><strong>Merek</strong></td>
                <td>:</td>
                <td><?php echo $data_detail['merek']; ?></td>
            </tr>
            <tr>
                <td><strong>Kategori</strong></td>
                <td>:</td>
                <td><?php echo $data_detail['nama_kategori']; ?></td>
            </tr>
            <tr>
                <td><strong>Stok Saat Ini</strong></td>
                <td>:</td>
                <td><strong style="color: #00b894; font-size: 18px;"><?php echo $data_detail['stok']; ?> Unit</strong></td>
            </tr>
            <tr>
                <td style="vertical-align: top;"><strong>Spesifikasi</strong></td>
                <td style="vertical-align: top;">:</td>
                <td><?php echo nl2br($data_detail['spesifikasi']); ?></td>
            </tr>
        </table>

    <?php 
    // ==========================================
    // ANTARMUKA UTAMA (DAFTAR BARANG)
    // ==========================================
    } else { 
    ?>
        <div class="header">
            <div>
                <h2 style="margin: 0 0 5px 0;">Daftar Inventaris Barang</h2>
                <a href="index.php" class="btn btn-secondary" style="font-size: 13px; padding: 5px 10px;">⬅️ Kembali ke Dashboard</a>
            </div>
            <div>
                <a href="barang.php?aksi=tambah_kategori" class="btn btn-success">➕ Tambah Kategori</a>
                <a href="barang.php?aksi=tambah" class="btn btn-primary" style="margin-left: 5px;">➕ Tambah Barang</a>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Barang</th>
                    <th>Merek</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query_tampil = "
                    SELECT barang.*, kategori.nama_kategori 
                    FROM barang 
                    JOIN kategori ON barang.id_kategori = kategori.id_kategori 
                    ORDER BY barang.id_barang DESC
                ";
                $result = mysqli_query($koneksi, $query_tampil);
                $no = 1;
                while($row = mysqli_fetch_assoc($result)) {
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo $row['nama_barang']; ?></td>
                    <td><?php echo $row['merek']; ?></td>
                    <td><?php echo $row['nama_kategori']; ?></td>
                    <td><strong><?php echo $row['stok']; ?></strong></td>
                    <td>
                        <a href="barang.php?aksi=detail&id=<?php echo $row['id_barang']; ?>" class="btn btn-success btn-action">🔍 Detail</a>
                        <a href="barang.php?aksi=edit&id=<?php echo $row['id_barang']; ?>" class="btn btn-warning btn-action">✏️ Edit</a>
                        <!-- Konfirmasi Javascript saat mau hapus -->
                        <a href="barang.php?aksi=hapus&id=<?php echo $row['id_barang']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?');" class="btn btn-danger btn-action">🗑️ Hapus</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

</div>

</body>
</html>