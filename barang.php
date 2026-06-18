<?php
// 1. Panggil koneksi database
include 'koneksi.php';

// Buat folder 'uploads' secara otomatis jika belum ada untuk menyimpan gambar
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}

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
    
    // Proses Upload Gambar
    $gambar = '';
    if(isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != '') {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = time() . '_' . uniqid() . '.' . $ext; // Penamaan unik agar tidak tertimpa
        move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $gambar);
    }

    // Simpan ke database (stok otomatis 0 karena baru dibuat)
    $query = "INSERT INTO barang (nama_barang, merek, spesifikasi, id_kategori, stok, gambar) 
              VALUES ('$nama', '$merek', '$spesifikasi', '$id_kategori', 0, '$gambar')";
    mysqli_query($koneksi, $query);
    header("Location: barang.php"); 
    exit;
}

// Jika tombol "Update Data" (Form Edit Barang) diklik
if (isset($_POST['simpan_edit'])) {
    $id_barang = $_POST['id_barang'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $merek = mysqli_real_escape_string($koneksi, $_POST['merek']);
    $spesifikasi = mysqli_real_escape_string($koneksi, $_POST['spesifikasi']);
    $id_kategori = $_POST['id_kategori'];
    
    // Cek gambar lama dari database
    $q_gambar = mysqli_query($koneksi, "SELECT gambar FROM barang WHERE id_barang='$id_barang'");
    $data_gambar = mysqli_fetch_assoc($q_gambar);
    $gambar_lama = $data_gambar['gambar'];
    
    // Proses Upload Gambar Baru (Jika user memilih gambar baru)
    $gambar_baru = $gambar_lama; // Default gunakan gambar lama
    if(isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != '') {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar_baru = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $gambar_baru);
        
        // Hapus file gambar lama dari folder uploads agar tidak memenuhi server
        if(!empty($gambar_lama) && file_exists('uploads/'.$gambar_lama)) {
            unlink('uploads/'.$gambar_lama);
        }
    }

    $query = "UPDATE barang SET 
                nama_barang = '$nama', 
                merek = '$merek', 
                spesifikasi = '$spesifikasi', 
                id_kategori = '$id_kategori',
                gambar = '$gambar_baru'
              WHERE id_barang = '$id_barang'";
    mysqli_query($koneksi, $query);
    header("Location: barang.php");
    exit;
}

// Proses Hapus Data Barang
if ($aksi == 'hapus') {
    $id = $_GET['id'];
    
    // Hapus file gambar terlebih dahulu sebelum menghapus data dari database
    $q_gambar = mysqli_query($koneksi, "SELECT gambar FROM barang WHERE id_barang='$id'");
    if($data_gambar = mysqli_fetch_assoc($q_gambar)) {
        if(!empty($data_gambar['gambar']) && file_exists('uploads/'.$data_gambar['gambar'])) {
            unlink('uploads/'.$data_gambar['gambar']); // Menghapus file gambar
        }
    }
    
    mysqli_query($koneksi, "DELETE FROM barang WHERE id_barang='$id'");
    header("Location: barang.php");
    exit;
}

// Jika tombol "Simpan Kategori" diklik
if (isset($_POST['simpan_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')");
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
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
        
        .btn { padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold; border: none; cursor: pointer; color: white; }
        .btn-primary { background-color: #0984e3; }
        .btn-success { background-color: #00b894; }
        .btn-warning { background-color: #fdcb6e; color: #2d3436; }
        .btn-danger { background-color: #d63031; }
        .btn-secondary { background-color: #636e72; }
        .btn-action { padding: 5px 10px; font-size: 0.9em; margin: 2px; }

        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; vertical-align: middle;}
        th { background-color: #f8f9fa; font-weight: 600; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        
        /* Style untuk gambar */
        .img-thumbnail { width: 70px; height: 70px; object-fit: cover; border-radius: 6px; border: 1px solid #ccc; }
        .img-detail { max-width: 300px; width: 100%; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container">

    <?php if ($aksi == 'tambah') { ?>
        <!-- ========================================== -->
        <!-- HALAMAN TAMBAH BARANG -->
        <!-- ========================================== -->
        <div class="header">
            <h2>➕ Tambah Barang Baru</h2>
            <a href="barang.php" class="btn btn-secondary">🔙 Kembali</a>
        </div>
        
        <!-- Wajib menggunakan enctype="multipart/form-data" untuk form upload file -->
        <form action="" method="POST" enctype="multipart/form-data">
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
                    $q_kat = mysqli_query($koneksi, "SELECT * FROM kategori");
                    while($k = mysqli_fetch_assoc($q_kat)) {
                        echo "<option value='".$k['id_kategori']."'>".$k['nama_kategori']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Spesifikasi</label>
                <textarea name="spesifikasi" class="form-control" rows="4"></textarea>
            </div>
            <!-- INPUT GAMBAR -->
            <div class="form-group">
                <label>Gambar Produk</label>
                <input type="file" name="gambar" class="form-control" accept="image/*">
                <small style="color: #666;"><i>Format: JPG, PNG, GIF. Kosongkan jika tidak ada gambar.</i></small>
            </div>
            
            <button type="submit" name="simpan_tambah" class="btn btn-primary">💾 Simpan Data</button>
        </form>

    <?php } else if ($aksi == 'edit') { 
        $id = $_GET['id'];
        $q_edit = mysqli_query($koneksi, "SELECT * FROM barang WHERE id_barang='$id'");
        $d_edit = mysqli_fetch_assoc($q_edit);
    ?>
        <!-- ========================================== -->
        <!-- HALAMAN EDIT BARANG -->
        <!-- ========================================== -->
        <div class="header">
            <h2>✏️ Edit Barang</h2>
            <a href="barang.php" class="btn btn-secondary">🔙 Kembali</a>
        </div>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_barang" value="<?php echo $d_edit['id_barang']; ?>">
            
            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" class="form-control" value="<?php echo $d_edit['nama_barang']; ?>" required>
            </div>
            <div class="form-group">
                <label>Merek</label>
                <input type="text" name="merek" class="form-control" value="<?php echo $d_edit['merek']; ?>" required>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="id_kategori" class="form-control" required>
                    <?php
                    $q_kat = mysqli_query($koneksi, "SELECT * FROM kategori");
                    while($k = mysqli_fetch_assoc($q_kat)) {
                        $selected = ($k['id_kategori'] == $d_edit['id_kategori']) ? 'selected' : '';
                        echo "<option value='".$k['id_kategori']."' $selected>".$k['nama_kategori']."</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Spesifikasi</label>
                <textarea name="spesifikasi" class="form-control" rows="4"><?php echo $d_edit['spesifikasi']; ?></textarea>
            </div>
            
            <!-- PREVIEW GAMBAR LAMA & INPUT GAMBAR BARU -->
            <div class="form-group">
                <label>Gambar Produk Saat Ini</label><br>
                <?php if(!empty($d_edit['gambar']) && file_exists('uploads/'.$d_edit['gambar'])) { ?>
                    <img src="uploads/<?php echo $d_edit['gambar']; ?>" class="img-thumbnail" style="width: 120px; height: 120px; margin-bottom: 10px;">
                <?php } else { ?>
                    <p style="color:#888;"><i>Tidak ada gambar.</i></p>
                <?php } ?>
            </div>
            <div class="form-group">
                <label>Ganti Gambar Baru</label>
                <input type="file" name="gambar" class="form-control" accept="image/*">
                <small style="color: #666;"><i>Abaikan jika tidak ingin mengubah gambar produk.</i></small>
            </div>
            
            <button type="submit" name="simpan_edit" class="btn btn-primary">💾 Update Data</button>
        </form>

    <?php } else if ($aksi == 'detail') { 
        $id = $_GET['id'];
        $q_detail = mysqli_query($koneksi, "SELECT barang.*, kategori.nama_kategori FROM barang JOIN kategori ON barang.id_kategori = kategori.id_kategori WHERE id_barang='$id'");
        $d_detail = mysqli_fetch_assoc($q_detail);
    ?>
        <!-- ========================================== -->
        <!-- HALAMAN DETAIL BARANG -->
        <!-- ========================================== -->
        <div class="header">
            <h2>🔍 Detail Barang: <?php echo $d_detail['nama_barang']; ?></h2>
            <a href="barang.php" class="btn btn-secondary">🔙 Kembali</a>
        </div>
        
        <div style="display:flex; gap: 30px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px; text-align: center;">
                <?php if(!empty($d_detail['gambar']) && file_exists('uploads/'.$d_detail['gambar'])) { ?>
                    <img src="uploads/<?php echo $d_detail['gambar']; ?>" class="img-detail">
                <?php } else { ?>
                    <div class="img-detail" style="height:250px; background:#eee; display:flex; align-items:center; justify-content:center; color:#999; margin: 0 auto;">
                        🚫 Tidak ada gambar
                    </div>
                <?php } ?>
            </div>
            <div style="flex: 2; min-width: 300px;">
                <table style="margin-top:0;">
                    <tr><th width="35%">Nama Barang</th><td><?php echo $d_detail['nama_barang']; ?></td></tr>
                    <tr><th>Merek</th><td><?php echo $d_detail['merek']; ?></td></tr>
                    <tr><th>Kategori</th><td><?php echo $d_detail['nama_kategori']; ?></td></tr>
                    <tr><th>Sisa Stok Saat Ini</th><td><strong style="font-size: 1.2em; color: #0984e3;"><?php echo $d_detail['stok']; ?> Unit</strong></td></tr>
                    <tr><th>Spesifikasi</th><td><?php echo nl2br($d_detail['spesifikasi']); ?></td></tr>
                </table>
            </div>
        </div>

    <?php } else { ?>
        <!-- ========================================== -->
        <!-- HALAMAN UTAMA (TABEL BARANG) -->
        <!-- ========================================== -->
        <div class="header">
            <h2>📦 Kelola Data Barang</h2>
            <div>
                <a href="barang.php?aksi=tambah" class="btn btn-primary">➕ Tambah Barang</a>
                <button onclick="document.getElementById('formKategori').style.display='block'" class="btn btn-success">📁 Tambah Kategori</button>
            </div>
        </div>
        
        <div id="formKategori" style="display:none; background:#f9f9f9; padding:15px; border:1px solid #ddd; margin-bottom:15px; border-radius:4px;">
            <form action="" method="POST">
                <div class="form-group">
                    <label>Nama Kategori Baru</label>
                    <input type="text" name="nama_kategori" class="form-control" required style="width:300px; display:inline-block;">
                    <button type="submit" name="simpan_kategori" class="btn btn-primary">Simpan</button>
                    <button type="button" onclick="document.getElementById('formKategori').style.display='none'" class="btn btn-secondary">Batal</button>
                </div>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 10%; text-align: center;">Gambar</th>
                    <th>Nama Barang</th>
                    <th>Merek</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th style="width: 25%;">Aksi</th>
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
                    <td style="text-align: center;"><?php echo $no++; ?></td>
                    <td style="text-align: center;">
                        <!-- TAMPILKAN THUMBNAIL GAMBAR -->
                        <?php if(!empty($row['gambar']) && file_exists('uploads/'.$row['gambar'])) { ?>
                            <img src="uploads/<?php echo $row['gambar']; ?>" class="img-thumbnail">
                        <?php } else { ?>
                            <div style="width: 70px; height: 70px; background: #eee; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; color: #aaa; border: 1px solid #ccc;">No Image</div>
                        <?php } ?>
                    </td>
                    <td><?php echo $row['nama_barang']; ?></td>
                    <td><?php echo $row['merek']; ?></td>
                    <td><?php echo $row['nama_kategori']; ?></td>
                    <td style="font-size: 1.1em;"><strong><?php echo $row['stok']; ?></strong></td>
                    <td>
                        <a href="barang.php?aksi=detail&id=<?php echo $row['id_barang']; ?>" class="btn btn-success btn-action">🔍 Detail</a>
                        <a href="barang.php?aksi=edit&id=<?php echo $row['id_barang']; ?>" class="btn btn-warning btn-action">✏️ Edit</a>
                        <a href="barang.php?aksi=hapus&id=<?php echo $row['id_barang']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus barang beserta gambarnya?');" class="btn btn-danger btn-action">🗑️ Hapus</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>

</div>

</body>
</html>