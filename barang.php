<?php
// 1. Panggil koneksi database
include 'koneksi.php';

// Menangkap parameter 'aksi' di URL. Jika tidak ada, default-nya adalah kosong (tampil tabel)
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

// ==========================================
// PROSES LOGIKA (SIMPAN, UPDATE, HAPUS)
// ==========================================

// Jika tombol "Simpan Data" (Form Tambah) diklik
if (isset($_POST['simpan_tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $merek = mysqli_real_escape_string($koneksi, $_POST['merek']);
    $spesifikasi = mysqli_real_escape_string($koneksi, $_POST['spesifikasi']);
    $id_kategori = $_POST['id_kategori'];
    
    // Simpan ke database (stok otomatis 0 karena baru dibuat)
    $query = "INSERT INTO barang (nama_barang, merek, spesifikasi, id_kategori, stok) 
              VALUES ('$nama', '$merek', '$spesifikasi', '$id_kategori', 0)";
    mysqli_query($koneksi, $query);
    header("Location: barang.php"); // Kembali ke halaman utama barang
    exit;
}

// Jika tombol "Simpan Perubahan" (Form Edit) diklik
if (isset($_POST['simpan_edit'])) {
    $id = $_POST['id_barang'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $merek = mysqli_real_escape_string($koneksi, $_POST['merek']);
    $spesifikasi = mysqli_real_escape_string($koneksi, $_POST['spesifikasi']);
    $id_kategori = $_POST['id_kategori'];
    
    // Update data (Perhatikan: Stok tidak ikut di-update di sini)
    $query = "UPDATE barang SET 
                nama_barang = '$nama', 
                merek = '$merek', 
                spesifikasi = '$spesifikasi', 
                id_kategori = '$id_kategori' 
              WHERE id_barang = '$id'";
    mysqli_query($koneksi, $query);
    header("Location: barang.php");
    exit;
}

// Proses Hapus Data
if ($aksi == 'hapus') {
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang = '$id'");
    header("Location: barang.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Barang - ElectroStock</title>
    <style>
        /* CSS Dasar */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        
        /* Tombol & Tabel */
        .btn { padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold; border: none; cursor: pointer; color: white; }
        .btn-primary { background-color: #0984e3; }
        .btn-secondary { background-color: #636e72; }
        .btn-success { background-color: #00b894; }
        .btn-warning { background-color: #fdcb6e; color: #2d3436; }
        .btn-danger { background-color: #d63031; }
        .btn-action { padding: 5px 10px; font-size: 12px; margin-right: 2px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f8f9fa; }
        
        /* Form */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        
        /* Detail Card */
        .detail-card { background: #f8f9fa; padding: 20px; border-radius: 5px; border-left: 5px solid #0984e3; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>📦 Manajemen Data Barang</h2>
        <a href="index.php" class="btn btn-secondary">⬅ Kembali ke Dashboard</a>
    </div>

    <?php
    // ==========================================
    // TAMPILAN BERDASARKAN AKSI
    // ==========================================
    
    // 1. TAMPILAN FORM TAMBAH
    if ($aksi == 'tambah') {
    ?>
        <h3>Tambah Barang Baru</h3>
        <form method="POST" action="barang.php">
            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Merek</label>
                <input type="text" name="merek" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="id_kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php
                    // Ambil data kategori untuk dropdown
                    $q_kat = mysqli_query($koneksi, "SELECT * FROM kategori");
                    while($k = mysqli_fetch_assoc($q_kat)) {
                        echo "<option value='".$k['id_kategori']."'>".$k['nama_kategori']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Spesifikasi (Opsional)</label>
                <textarea name="spesifikasi" class="form-control" rows="3"></textarea>
            </div>
            <!-- Stok tidak dimasukkan di sini, otomatis diset 0 di proses PHP -->
            
            <button type="submit" name="simpan_tambah" class="btn btn-success">💾 Simpan Data</button>
            <a href="barang.php" class="btn btn-secondary">Batal</a>
        </form>

    <?php
    // 2. TAMPILAN FORM EDIT
    } elseif ($aksi == 'edit') {
        $id = $_GET['id'];
        // Ambil data barang yang akan diedit
        $q_edit = mysqli_query($koneksi, "SELECT * FROM barang WHERE id_barang = '$id'");
        $data = mysqli_fetch_assoc($q_edit);
    ?>
        <h3>Edit Data Barang</h3>
        <form method="POST" action="barang.php">
            <!-- ID Barang disembunyikan untuk keperluan update -->
            <input type="hidden" name="id_barang" value="<?php echo $data['id_barang']; ?>">
            
            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" class="form-control" value="<?php echo $data['nama_barang']; ?>" required>
            </div>
            <div class="form-group">
                <label>Merek</label>
                <input type="text" name="merek" class="form-control" value="<?php echo $data['merek']; ?>" required>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="id_kategori" class="form-control" required>
                    <?php
                    $q_kat = mysqli_query($koneksi, "SELECT * FROM kategori");
                    while($k = mysqli_fetch_assoc($q_kat)) {
                        // Cek kategori mana yang sedang dipilih
                        $selected = ($k['id_kategori'] == $data['id_kategori']) ? "selected" : "";
                        echo "<option value='".$k['id_kategori']."' $selected>".$k['nama_kategori']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Spesifikasi</label>
                <textarea name="spesifikasi" class="form-control" rows="3"><?php echo $data['spesifikasi']; ?></textarea>
            </div>
            <!-- Sekali lagi, tidak ada input untuk edit stok -->
            
            <button type="submit" name="simpan_edit" class="btn btn-success">💾 Simpan Perubahan</button>
            <a href="barang.php" class="btn btn-secondary">Batal</a>
        </form>

    <?php
    // 3. TAMPILAN DETAIL
    } elseif ($aksi == 'detail') {
        $id = $_GET['id'];
        $q_detail = mysqli_query($koneksi, "
            SELECT barang.*, kategori.nama_kategori 
            FROM barang 
            JOIN kategori ON barang.id_kategori = kategori.id_kategori 
            WHERE id_barang = '$id'
        ");
        $data = mysqli_fetch_assoc($q_detail);
    ?>
        <h3>Detail Spesifikasi Barang</h3>
        <div class="detail-card">
            <p><strong>Nama Barang:</strong> <?php echo $data['nama_barang']; ?></p>
            <p><strong>Merek:</strong> <?php echo $data['merek']; ?></p>
            <p><strong>Kategori:</strong> <span style="background: #0984e3; color: white; padding: 2px 8px; border-radius: 10px; font-size: 12px;"><?php echo $data['nama_kategori']; ?></span></p>
            <p><strong>Sisa Stok:</strong> 
                <span style="font-size: 18px; font-weight: bold; color: <?php echo ($data['stok'] <= 5) ? '#d63031' : '#00b894'; ?>;">
                    <?php echo $data['stok']; ?> Unit
                </span>
            </p>
            <hr style="border: 0; border-top: 1px solid #ccc; margin: 15px 0;">
            <p><strong>Spesifikasi Lengkap:</strong><br> <?php echo nl2br($data['spesifikasi']); ?></p>
        </div>
        <br>
        <a href="barang.php" class="btn btn-primary">Kembali ke Tabel</a>

    <?php
    // 4. TAMPILAN TABEL (DEFAULT)
    } else {
    ?>
        <a href="barang.php?aksi=tambah" class="btn btn-primary">+ Tambah Barang Baru</a>
        
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
                // Query mengambil data barang digabung dengan kategori
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