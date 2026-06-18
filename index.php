<?php
// 1. Memanggil file koneksi
include 'koneksi.php';

// ==========================================
// PROSES PENGAMBILAN DATA UNTUK DIAGRAM
// ==========================================

// A. Data Kategori (Untuk Pie Chart)
$query_kategori = mysqli_query($koneksi, "SELECT k.nama_kategori, COUNT(b.id_barang) as jumlah FROM kategori k LEFT JOIN barang b ON k.id_kategori = b.id_kategori GROUP BY k.id_kategori");
$label_kategori = []; $data_kategori = [];
while($row = mysqli_fetch_assoc($query_kategori)) {
    $label_kategori[] = $row['nama_kategori'];
    $data_kategori[] = $row['jumlah'];
}

// B. Data Riwayat Transaksi (Untuk Doughnut Chart)
$query_transaksi = mysqli_query($koneksi, "SELECT jenis_transaksi, SUM(jumlah) as total FROM transaksi GROUP BY jenis_transaksi");
$label_transaksi = []; $data_transaksi = [];
while($row = mysqli_fetch_assoc($query_transaksi)) {
    $label_transaksi[] = ucfirst($row['jenis_transaksi']); 
    $data_transaksi[] = $row['total'];
}

// C. Data Stok Semua Barang (Untuk Bar Chart)
$query_stok = mysqli_query($koneksi, "SELECT nama_barang, stok FROM barang");
$label_stok = []; $data_stok = [];
while($row = mysqli_fetch_assoc($query_stok)) {
    $label_stok[] = $row['nama_barang'];
    $data_stok[] = $row['stok'];
}

// D. Data Stok Paling Sedikit - Ambil 5 Terkecil (Untuk Horizontal Bar Chart)
$query_sedikit = mysqli_query($koneksi, "SELECT nama_barang, stok FROM barang ORDER BY stok ASC LIMIT 5");
$label_sedikit = []; $data_sedikit = [];
while($row = mysqli_fetch_assoc($query_sedikit)) {
    $label_sedikit[] = $row['nama_barang'];
    $data_sedikit[] = $row['stok'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Visual - ElectroStock</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0; padding: 20px; color: #333;
        }
        .header {
            background-color: #2c3e50; color: white; padding: 15px 20px;
            border-radius: 8px; margin-bottom: 20px; text-align: center;
        }
        
        /* ========================================= */
        /* CSS BARU UNTUK TOMBOL NAVIGASI            */
        /* ========================================= */
        .nav-container {
            display: flex;
            gap: 15px; /* Memberi jarak antar tombol */
            margin-bottom: 25px;
            justify-content: center; /* Membuat tombol berada di tengah */
            flex-wrap: wrap; /* Agar tombol turun ke bawah jika layar kecil */
        }
        .btn-nav {
            padding: 12px 20px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            border-radius: 6px;
            transition: background-color 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        /* Memberikan warna berbeda untuk setiap tombol */
        .btn-barang { background-color: #0984e3; }
        .btn-barang:hover { background-color: #076bb8; }
        
        .btn-riwayat { background-color: #6c5ce7; }
        .btn-riwayat:hover { background-color: #574bbf; }
        
        .btn-transaksi { background-color: #00b894; }
        .btn-transaksi:hover { background-color: #009678; }
        
        .btn-stok { background-color: #d63031; }
        .btn-stok:hover { background-color: #b52728; }
        /* ========================================= */

        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .card {
            background-color: white; padding: 20px;
            border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        h3 { margin-top: 0; font-size: 16px; color: #555; text-align: center; }
        .chart-box { position: relative; height: 300px; width: 100%; }
    </style>
</head>
<body>

    <div class="header">
        <h2 style="margin: 0;">ElectroStock - Dashboard Visual</h2>
        <p style="margin: 5px 0 0 0; font-size: 14px;">Ringkasan Data Inventaris Gudang</p>
    </div>

    <div class="nav-container">
        <a href="barang.php" class="btn-nav btn-barang">📦 Semua Barang & Kategori</a>
        <a href="riwayat.php" class="btn-nav btn-riwayat">📜 Riwayat Transaksi</a>
        <a href="transaksi.php" class="btn-nav btn-transaksi">✍️ Update Stok</a>
        <a href="stok_kritis.php" class="btn-nav btn-stok">⚠️ Stok Menipis</a>
    </div>

    <div class="grid-container">
        <div class="card">
            <h3>Proporsi Barang per Kategori</h3>
            <div class="chart-box">
                <canvas id="kategoriChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3>Total Transaksi (Masuk vs Keluar)</h3>
            <div class="chart-box">
                <canvas id="transaksiChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3>Update Stok Saat Ini (Semua Barang)</h3>
            <div class="chart-box">
                <canvas id="stokChart"></canvas>
            </div>
        </div>

        <div class="card" style="border-top: 4px solid #d63031;">
            <h3>⚠️ Top 5 Stok Paling Sedikit</h3>
            <div class="chart-box">
                <canvas id="sedikitChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const labelKategori = <?php echo json_encode($label_kategori); ?>;
        const dataKategori = <?php echo json_encode($data_kategori); ?>;

        const labelTransaksi = <?php echo json_encode($label_transaksi); ?>;
        const dataTransaksi = <?php echo json_encode($data_transaksi); ?>;

        const labelStok = <?php echo json_encode($label_stok); ?>;
        const dataStok = <?php echo json_encode($data_stok); ?>;

        const labelSedikit = <?php echo json_encode($label_sedikit); ?>;
        const dataSedikit = <?php echo json_encode($data_sedikit); ?>;

        // Fungsi warna dinamis untuk mencegah warna kategori sama/berulang
        function generateDynamicColors(count) {
            const colors = ['#0984e3', '#00b894', '#fdcb6e', '#d63031', '#6c5ce7', '#e84393', '#00cec9', '#b2bec3'];
            let generatedColors = [];
            for (let i = 0; i < count; i++) {
                // Gunakan warna dasar jika masih tersedia
                if (i < colors.length) {
                    generatedColors.push(colors[i]);
                } else {
                    // Buat warna acak (Hue dinamis) jika kategori lebih dari 8
                    const hue = Math.floor(Math.random() * 360);
                    generatedColors.push(`hsl(${hue}, 75%, 55%)`);
                }
            }
            return generatedColors;
        }

        new Chart(document.getElementById('kategoriChart'), {
            type: 'pie',
            data: {
                labels: labelKategori,
                datasets: [{
                    data: dataKategori,
                    // Panggil fungsi di atas sesuai jumlah data kategori
                    backgroundColor: generateDynamicColors(labelKategori.length)
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });


        new Chart(document.getElementById('transaksiChart'), {
            type: 'doughnut',
            data: {
                labels: labelTransaksi,
                datasets: [{
                    data: dataTransaksi,
                    backgroundColor: ['#00b894', '#d63031'] 
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById('stokChart'), {
            type: 'bar',
            data: {
                labels: labelStok,
                datasets: [{
                    label: 'Jumlah Stok',
                    data: dataStok,
                    backgroundColor: '#0984e3'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById('sedikitChart'), {
            type: 'bar', 
            data: {
                labels: labelSedikit,
                datasets: [{
                    label: 'Sisa Stok',
                    data: dataSedikit,
                    backgroundColor: '#d63031' 
                }]
            },
            options: { 
                indexAxis: 'y', 
                responsive: true, 
                maintainAspectRatio: false 
            }
        });
    </script>
</body>
</html>