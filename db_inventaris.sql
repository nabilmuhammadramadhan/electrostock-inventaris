-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 06, 2026 at 05:52 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_inventaris`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int NOT NULL,
  `nama_barang` varchar(150) NOT NULL,
  `merek` varchar(100) DEFAULT NULL,
  `spesifikasi` text,
  `id_kategori` int NOT NULL,
  `stok` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `nama_barang`, `merek`, `spesifikasi`, `id_kategori`, `stok`) VALUES
(1, 'ROG Zephyrus G14', 'Asus', 'AMD Ryzen 7, RAM 16GB, SSD 512GB, RTX 3050', 1, 2),
(2, 'Galaxy S23 Ultra', 'Samsung', 'RAM 12GB, Storage 256GB, Kamera 200MP, 5G', 2, 5),
(3, 'UltraGear 24GN600', 'LG', '24 Inch, Panel IPS, 144Hz, Full HD', 3, 11),
(4, 'ThinkPad X1 Carbon', 'Lenovo', 'Intel Core i7, RAM 16GB, SSD 1TB, Layar 14\"', 1, 8),
(5, 'iPhone 15 Pro', 'Apple', 'Chip A17 Pro, Storage 128GB, Titanium Design', 2, 10),
(6, 'King Infinix', 'Infinix', 'HP butut', 2, 17),
(7, 'Iphone TWS', 'Iphone', 'TWS mahal', 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Laptop'),
(2, 'Smartphone'),
(3, 'Monitor'),
(4, 'TWS');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `id_barang` int NOT NULL,
  `jenis_transaksi` enum('masuk','keluar') NOT NULL,
  `jumlah` int NOT NULL,
  `tanggal` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_barang`, `jenis_transaksi`, `jumlah`, `tanggal`, `keterangan`) VALUES
(1, 1, 'masuk', 2, '2026-06-04 14:09:32', 'Stok awal gudang baru'),
(2, 2, 'masuk', 15, '2026-06-04 14:09:32', 'Pasokan dari pusat'),
(3, 3, 'masuk', 5, '2026-06-04 14:09:32', 'Pembelian unit baru'),
(4, 3, 'keluar', 4, '2026-06-04 14:09:32', 'Barang dikirim ke cabang toko B'),
(5, 2, 'masuk', 5, '2026-06-06 05:12:15', 'restock dari bulan lalu'),
(6, 3, 'masuk', 10, '2026-06-06 05:13:06', 'masuk pak eko'),
(7, 2, 'keluar', 15, '2026-06-06 05:13:40', 'sultan borong'),
(8, 6, 'masuk', 17, '2026-06-06 05:15:29', 'tes'),
(9, 7, 'masuk', 2, '2026-06-06 05:25:21', 'tes');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_barang` (`id_barang`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
