-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mariadb
-- Generation Time: Nov 30, 2025 at 10:32 PM
-- Server version: 11.4.5-MariaDB-log
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `karangasem`
--

-- --------------------------------------------------------

--
-- Table structure for table `laporandesa`
--

CREATE TABLE `laporandesa` (
  `id` int(11) NOT NULL,
  `ticket` varchar(255) DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nomor_telepon` varchar(50) NOT NULL,
  `alamat` text NOT NULL,
  `rw` varchar(50) NOT NULL,
  `pesan_keluhan` text NOT NULL,
  `path_keluhan_foto` varchar(255) NOT NULL,
  `latitude` decimal(20,15) DEFAULT NULL,
  `longitude` decimal(20,15) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('menunggu','diproses','selesai','ditolak') DEFAULT 'menunggu' COMMENT 'Status pengerjaan laporan',
  `tanggapan` text DEFAULT NULL COMMENT 'Isi jawaban dari RW atau Perangkat Desa',
  `id_penanggap` int(11) DEFAULT NULL COMMENT 'ID User yang menjawab (Relasi ke tabel users)',
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lokasi_umum`
--

CREATE TABLE `lokasi_umum` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `jenis` enum('tempat-penting','tempat-tourist') NOT NULL,
  `path_foto` varchar(255) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `potensi_desa`
--

CREATE TABLE `potensi_desa` (
  `id` int(11) NOT NULL,
  `nama_potensi` varchar(255) NOT NULL,
  `deskripsi_potensi` text DEFAULT NULL,
  `jenis_potensi` enum('tempat','budaya','none') DEFAULT NULL,
  `path_foto_potensi` varchar(255) DEFAULT NULL,
  `link_potensi` varchar(255) DEFAULT NULL,
  `latitude_potensi` decimal(10,8) DEFAULT NULL,
  `longitude_potensi` decimal(11,8) DEFAULT NULL,
  `urutan` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_tanggapan`
--

CREATE TABLE `riwayat_tanggapan` (
  `id` int(11) NOT NULL,
  `id_laporan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `isi_tanggapan` text NOT NULL,
  `status_laporan` enum('menunggu','diproses','selesai','ditolak') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `umkm`
--

CREATE TABLE `umkm` (
  `id` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `diacc` tinyint(1) DEFAULT 0 COMMENT '0 = Belum Diacc, 1 = Sudah Diacc',
  `nama_usaha` varchar(200) NOT NULL,
  `deskripsi_usaha` text DEFAULT NULL,
  `kategori_usaha` enum('warung','pedagangkakilima','pengrajin') NOT NULL,
  `nama_pemilik_usaha` varchar(150) DEFAULT NULL,
  `kontak_usaha` varchar(50) DEFAULT NULL,
  `alamat_usaha` varchar(255) DEFAULT NULL,
  `latitude` decimal(18,15) NOT NULL,
  `longitude` decimal(18,15) NOT NULL,
  `path_foto_usaha` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `qris` tinyint(1) NOT NULL,
  `punya_whatsapp` tinyint(1) DEFAULT 0,
  `no_wa_apakahsama` tinyint(1) DEFAULT 0,
  `no_wa_berbeda` varchar(255) DEFAULT NULL,
  `punya_instagram` tinyint(1) DEFAULT 0,
  `username_instagram` varchar(255) DEFAULT NULL,
  `punya_facebook` tinyint(1) DEFAULT 0,
  `link_facebook` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `umkmproduk`
--

CREATE TABLE `umkmproduk` (
  `id` int(11) NOT NULL,
  `umkm_id` int(11) NOT NULL,
  `nama_produk` varchar(200) NOT NULL,
  `harga_produk` int(11) NOT NULL,
  `deskripsi_produk` text DEFAULT NULL,
  `path_foto_produk` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `rw` enum('1','2','3','4','5','6','7','8','9','10') DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `punya_whatsapp` tinyint(1) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level` enum('perangkat_desa','rw','user') NOT NULL DEFAULT 'user',
  `path_foto_user` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `laporandesa`
--
ALTER TABLE `laporandesa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lokasi_umum`
--
ALTER TABLE `lokasi_umum`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `potensi_desa`
--
ALTER TABLE `potensi_desa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `riwayat_tanggapan`
--
ALTER TABLE `riwayat_tanggapan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_laporan` (`id_laporan`),
  ADD KEY `idx_user` (`id_user`);

--
-- Indexes for table `umkm`
--
ALTER TABLE `umkm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_umkm_user` (`id_user`);

--
-- Indexes for table `umkmproduk`
--
ALTER TABLE `umkmproduk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_umkm_id` (`umkm_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `laporandesa`
--
ALTER TABLE `laporandesa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lokasi_umum`
--
ALTER TABLE `lokasi_umum`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `potensi_desa`
--
ALTER TABLE `potensi_desa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `riwayat_tanggapan`
--
ALTER TABLE `riwayat_tanggapan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `umkm`
--
ALTER TABLE `umkm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `umkmproduk`
--
ALTER TABLE `umkmproduk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `umkm`
--
ALTER TABLE `umkm`
  ADD CONSTRAINT `fk_umkm_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `umkmproduk`
--
ALTER TABLE `umkmproduk`
  ADD CONSTRAINT `fk_umkm_produk` FOREIGN KEY (`umkm_id`) REFERENCES `umkm` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
