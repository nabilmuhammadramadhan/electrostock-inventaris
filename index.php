<?php
// Memulai sesi untuk fitur login
session_start();

// Cek apakah pengguna sudah login, jika belum arahkan kembali ke login.php
if(!isset($_SESSION['login'])){
    header("Location: login.php");
    exit;
}

// 1. Memanggil file koneksi (hanya dipanggil satu kali)
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
    
    <!-- Library Chart.js dan Font Google Poppins -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* CSS Dasar */
        body {
            font-family: 'Poppins', sans-serif;
            background: #6a7aaf;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        /* Layout Utama (Sidebar + Konten) */
        .layout {
            display: flex;
            gap: 20px;
        }

        /* Desain Sidebar */
        .sidebar {
            width: 250px;
            background: #0f172a;
            min-height: calc(100vh - 40px); /* Menyesuaikan dengan padding body */
            border-radius: 20px;
            padding: 25px;
            color: white;
            box-sizing: border-box;
        }
        .sidebar h2 {
            margin-top: 0;
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: .3s;
        }
        .sidebar a:hover {
            background: #2563eb;
        }
        .sidebar hr {
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin: 20px 0;
        }

        /* Desain Profil User */
        .profile {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #2563eb;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            cursor: pointer;
            margin: 0 auto 30px auto;
            box-shadow: 0 0 15px rgba(37, 99, 235, 0.5);
            position: relative;
        }
        .profile-info {
            display: none;
            position: absolute;
            left: 70px;
            top: 0;
            width: 220px;
            background: rgba(0, 147, 184, 0.95);
            border: 1px solid rgba(150, 18, 18, 0.1);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,.5);
            z-index: 100;
            text-align: center;
            color: white;
        }
        .profile-info h3 {
            margin: 0;
            font-size: 16px;
        }
        .profile-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #d1d5db;
        }
        .profile-info small {
            font-size: 12px;
            color: #94a3b8;
        }

        /* Desain Area Konten */
        .main-content {
            flex: 1;
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(37,99,235,.3);
        }

        /* Desain Grid Chart */
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
        .card h3 { 
            margin-top: 0; 
            font-size: 16px; 
            color: #555; 
            text-align: center; 
        }
        .chart-box { 
            position: relative; 
            height: 300px; 
            width: 100%; 
        }
        
        /* Media Query untuk Responsivitas */
        @media (max-width: 900px) {
            .grid-container { grid-template-columns: 1fr; }
            .layout { flex-direction: column; }
            .sidebar { width: 100%; min-height: auto; margin-bottom: 20px; }
        }
    </style>
</head>
<body>

<div class="layout">

    <!-- BAGIAN KIRI: SIDEBAR -->
    <div class="sidebar">
        <h2>⚡ ElectroStock</h2>

        <!-- Avatar Profil -->
        <div class="profile" onclick="toggleProfile()">
            👤
            <!-- Dropdown Info Profil (Data diambil dari SESSION login.php) -->
            <div class="profile-info" id="profileInfo">
                <h3><?php echo $_SESSION['nama']; ?></h3>
                <p><?php echo $_SESSION['jabatan']; ?></p>
                <small><?php echo $_SESSION['email']; ?></small>
            </div>
        </div>

        <!-- Menu Navigasi -->
        <a href="index.php">📊 Dashboard</a>
        <a href="barang.php">📦 Data Barang</a>
        <a href="transaksi.php">✍️ Catat Transaksi</a>
        <a href="riwayat.php">📜 Riwayat</a>
        <a href="stok_kritis.php">⚠️ Stok Kritis</a>
        <hr>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <!-- BAGIAN KANAN: KONTEN UTAMA -->
    <div class="main-content">
        <div class="header">
            <h2 style="margin: 0;">ElectroStock - Dashboard Visual</h2>
            <p style="margin: 5px 0 0 0; font-size: 14px;">Ringkasan Data Inventaris Gudang</p>
        </div>

        <div class="grid-container">
            <!-- 1. Pie Chart -->
            <div class="card">
                <h3>Proporsi Barang per Kategori</h3>
                <div class="chart-box">
                    <canvas id="kategoriChart"></canvas>
                </div>
            </div>

            <!-- 2. Doughnut Chart -->
            <div class="card">
                <h3>Total Transaksi (Masuk vs Keluar)</h3>
                <div class="chart-box">
                    <canvas id="transaksiChart"></canvas>
                </div>
            </div>

            <!-- 3. Bar Chart Horizontal -->
            <div class="card">
                <h3>Update Stok Saat Ini (Semua Barang)</h3>
                <div class="chart-box">
                    <canvas id="stokChart"></canvas>
                </div>
            </div>

            <!-- 4. Bar Chart Peringatan -->
            <div class="card";">
                <h3>⚠️ Top 5 Stok Paling Sedikit</h3>
                <div class="chart-box">
                    <canvas id="sedikitChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- SCRIPT JAVASCRIPT -->
<script>
    // Fungsi memunculkan/menyembunyikan Profil User
    function toggleProfile() {
        var x = document.getElementById("profileInfo");
        if(x.style.display == "block"){
            x.style.display = "none";
        } else {
            x.style.display = "block";
        }
    }

    // Variabel Data dari PHP untuk Chart.js
    const labelKategori = <?php echo json_encode($label_kategori); ?>;
    const dataKategori = <?php echo json_encode($data_kategori); ?>;

    const labelTransaksi = <?php echo json_encode($label_transaksi); ?>;
    const dataTransaksi = <?php echo json_encode($data_transaksi); ?>;

    const labelStok = <?php echo json_encode($label_stok); ?>;
    const dataStok = <?php echo json_encode($data_stok); ?>;

    const labelSedikit = <?php echo json_encode($label_sedikit); ?>;
    const dataSedikit = <?php echo json_encode($data_sedikit); ?>;

    // Fungsi warna dinamis terintegrasi dari index (1).php
    function generateDynamicColors(count) {
        const colors = ['#0984e3', '#00b894', '#fdcb6e', '#d63031', '#6c5ce7', '#e84393', '#00cec9', '#b2bec3'];
        let generatedColors = [];
        for (let i = 0; i < count; i++) {
            if (i < colors.length) {
                generatedColors.push(colors[i]);
            } else {
                const hue = Math.floor(Math.random() * 360);
                generatedColors.push(`hsl(${hue}, 75%, 55%)`);
            }
        }
        return generatedColors;
    }

    // Menggambar Grafik
    new Chart(document.getElementById('kategoriChart'), {
        type: 'pie',
        data: {
            labels: labelKategori,
            datasets: [{
                data: dataKategori,
                backgroundColor: generateDynamicColors(labelKategori.length) // Menggunakan warna dinamis
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