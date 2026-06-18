<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

include 'koneksi.php';
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
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>

.profile{
    cursor:pointer;
    margin-bottom:25px;
}

.avatar{
    width:60px;
    height:60px;
    border-radius:50%;
    background:#2563eb;

    display:flex;
    justify-content:center;
    align-items:center;

    font-size:28px;
    color:white;

    margin:auto;

    box-shadow:0 0 15px rgba(37,99,235,.5);
}

.profile-info{
    display:none;
    position:absolute;

    left:70px;
    top:50px;

    width:220px;

    background:rgba(0, 147, 184, 0.95);
    border:1px solid rgba(150, 18, 18, 0.1);

    border-radius:20px;
    padding:20px;

    box-shadow:0 10px 30px rgba(0,0,0,.5);

    z-index:100;
    text-align:center;
}
.profile{
    width:45px;
    height:45px;
    border-radius:50%;
    background:#2563eb;

    display:flex;
    justify-content:center;
    align-items:center;

    cursor:pointer;

    margin:0 auto 20px auto;

    box-shadow:0 0 15px rgba(116, 19, 6, 0.5);
}
.profile h3{
    margin:0;
    font-size:14px;
}
.profile p{
    margin:5px 0;
    font-size:14px;
}

.profile small{
    font-size:12px;
    color:#cbd5e1;
}

.profile p{
    margin:5px 0;
    color:#d1d5db;
}

.profile small{
    color:#94a3b8;
}
        body {
            .layout{
            display:flex;
            gap:20px;
            }

            .sidebar{
                width:250px;
                background:#0f172a;
                min-height:100vh;
                border-radius:20px;
                padding:25px;
                color:white;
                box-sizing:border-box;
            }

            .sidebar h2{
                margin-top:0;
                text-align:center;
                margin-bottom:30px;
            }

            .sidebar a{
                display:block;
                color:white;
                text-decoration:none;
                padding:14px;
                border-radius:12px;
                margin-bottom:10px;
                transition:.3s;
            }

            .sidebar a:hover{
                background:#2563eb;
            }

            .main-content{
                flex:1;
            }
            font-family: 'Poppins', sans-serif;
            background: #6a7aaf;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg,#1e3a8a,#2563eb);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(37,99,235,.3);
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
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,.08);
            transition: .3s;
        }

.card:hover {
    transform: translateY(-5px);
}
        h3 { margin-top: 0; font-size: 16px; color: #555; text-align: center; }
        .chart-box { position: relative; height: 300px; width: 100%; }
    </style>
</head>
<body>

<div class="layout">

    <div class="sidebar">

    <h2>⚡ ElectroStock</h2>

    <div class="profile" onclick="toggleProfile()">

    <div class="avatar">
        👤
    </div>

    <div class="profile-info" id="profileInfo">
        <h3><?php echo $_SESSION['nama']; ?></h3>
        <p><?php echo $_SESSION['jabatan']; ?></p>
        <small><?php echo $_SESSION['email']; ?></small>
    </div>

</div>

    <a href="index.php">📊 Dashboard</a>
    <a href="barang.php">📦 Data Barang</a>
    <a href="transaksi.php">📋 Transaksi</a>
    <a href="riwayat.php">📜 Riwayat</a>
    <a href="stok_kritis.php">⚠️ Stok Kritis</a>

    <hr>

    <a href="logout.php">🚪 Logout</a>
        </div>

        <div class="main-content">

    <div class="header">
        <h2 style="margin: 0;">ElectroStock - Dashboard Visual</h2>
        <p style="margin: 5px 0 0 0; font-size: 14px;">Ringkasan Data Inventaris Gudang</p>
    </div>

    <!-- <div class="nav-container"> 
        <a href="barang.php" class="btn-nav btn-barang">📦 Semua Barang & Kategori</a>
        <a href="riwayat.php" class="btn-nav btn-riwayat">📜 Riwayat Transaksi</a>
        <a href="transaksi.php" class="btn-nav btn-transaksi">✍️ Update Stok</a>
        <a href="stok_kritis.php" class="btn-nav btn-stok">⚠️ Stok Menipis</a>
    </div> -->

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

        new Chart(document.getElementById('kategoriChart'), {
            type: 'pie',
            data: {
                labels: labelKategori,
                datasets: [{
                    data: dataKategori,
                    backgroundColor: ['#0984e3', '#00b894', '#fdcb6e']
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
</div>
<script>

function toggleProfile(){

    var x = document.getElementById("profileInfo");

    if(x.style.display=="block"){
        x.style.display="none";
    }else{
        x.style.display="block";
    }

}

</script>
</body>
</html>