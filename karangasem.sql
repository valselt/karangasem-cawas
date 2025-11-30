-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mariadb
-- Generation Time: Nov 30, 2025 at 09:55 PM
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

--
-- Dumping data for table `laporandesa`
--

INSERT INTO `laporandesa` (`id`, `ticket`, `nama`, `email`, `nomor_telepon`, `alamat`, `rw`, `pesan_keluhan`, `path_keluhan_foto`, `latitude`, `longitude`, `created_at`, `status`, `tanggapan`, `id_penanggap`, `is_archived`) VALUES
(34, '#IC996MESYH', 'Muhammad Ivan Aldorino', '', '085697498080', 'Jonggo', 'rw-8', 'MALINGGGG!', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lapordesa/20251124-192414-muhammad-ivan-aldorino.webp', -7.785676800000000, 110.690304000000000, '2025-11-24 19:24:20', 'diproses', 'Saya Sedang Selidiki', 8, 0),
(35, '#FIHTWBWM64', 'Budi', '', '0888', 'karangasem', 'rw-3', 'kehilangan benda', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lapordesa/20251126-043708-budi.webp', -7.782400000000000, 110.385561600000000, '2025-11-26 04:37:17', 'menunggu', NULL, NULL, 0);

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

--
-- Dumping data for table `lokasi_umum`
--

INSERT INTO `lokasi_umum` (`id`, `nama`, `jenis`, `path_foto`, `latitude`, `longitude`) VALUES
(1, 'Posko KKN GIAT 13 UNNES', 'tempat-penting', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lokasi_umum/foto.webp', -7.7907616, 110.6984827),
(2, 'Embung Karangasem', 'tempat-tourist', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lokasi_umum/embung.webp', -7.7921621, 110.7018881),
(3, 'Karangasem International Mini Soccer', 'tempat-tourist', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lokasi_umum/minsoc.webp', -7.7921185, 110.6983959),
(4, 'Joglo Pertemuan Karangasem', 'tempat-tourist', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lokasi_umum/joglo.webp', -7.8046516, 110.6949759),
(5, 'Monumen Patung Kemerdekaan Soekarno', 'tempat-tourist', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lokasi_umum/monumen.webp', -7.7898324, 110.7015344),
(6, 'Kantor Desa Karangasem', 'tempat-penting', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lokasi_umum/kantordesa.webp', -7.8042388, 110.6949666),
(7, 'Lapangan Voli Karangasem', 'tempat-penting', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lokasi_umum/voli.webp', -7.8059130, 110.6980361),
(8, 'Pasar Memble', 'tempat-penting', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lokasi_umum/pasarmemble.webp', -7.8073498, 110.6942984),
(9, 'Pemancingan Samira Pring Sedapor', 'tempat-penting', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lokasi_umum/pemancingan.webp', -7.7886275, 110.6964837);

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

--
-- Dumping data for table `potensi_desa`
--

INSERT INTO `potensi_desa` (`id`, `nama_potensi`, `deskripsi_potensi`, `jenis_potensi`, `path_foto_potensi`, `link_potensi`, `latitude_potensi`, `longitude_potensi`, `urutan`) VALUES
(1, 'Embung Karangasem', 'Embung Karangasem di Kecamatan Cawas, Klaten, merupakan infrastruktur desa multifungsi yang berhasil menggabungkan peran vital irigasi pertanian dengan potensi pariwisata lokal. Meskipun fungsi utamanya adalah sebagai penampung air untuk menjaga produktivitas sawah dan pengendali banjir, embung ini telah bertransformasi menjadi destinasi rekreasi \"murah meriah\" yang populer, khususnya bagi pesepeda dan pemburu pemandangan matahari terbenam (sunset). Keberadaannya di tengah hamparan sawah dengan latar perbukitan tidak hanya menunjang ketahanan pangan petani setempat, tetapi juga menciptakan peluang ekonomi baru bagi warga sekitar melalui warung-warung kuliner dan aktivitas wisata yang tumbuh di sekelilingnya.', 'tempat', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/potensi_desa/embung.png', NULL, -7.79215637, 110.70187428, 2),
(2, 'Karangasem Mini Soccer', 'Karangasem Minisoccer (KIM Soccerfield) di Cawas, Klaten, secara konsisten membuktikan kualitasnya sebagai destinasi olahraga premium lokal dengan mempertahankan rating sempurna (5.0) dan layanan operasional 24 jam penuh. Tempat ini menjadi solusi vital bagi komunitas pecinta sepak bola di wilayah Cawas berkat kombinasi fasilitas berstandar tinggi dan fleksibilitas waktu yang ditawarkan, menjadikannya pilihan terpercaya dan sangat direkomendasikan untuk aktivitas minisoccer baik yang bersifat kompetitif maupun rekreasional.', 'tempat', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/potensi_desa/minsoc.png', NULL, -7.79189482, 110.69845457, 1),
(3, 'Joglo Pertemuan Karangasem', 'Joglo Pertemuan Karangasem di Cawas, Klaten, merupakan pusat kegiatan sosial dan pemerintahan desa yang strategis dan aktif. Berlokasi di Jalan Cawas-Bayat, tempat ini berfungsi vital sebagai wadah aspirasi masyarakat, dibuktikan dengan menjadi lokasi utama acara \"Sambung Rasa\" yang dihadiri langsung oleh Bupati dan Wakil Bupati Klaten pada Oktober 2025 lalu. Selain sebagai tempat pertemuan formal, joglo ini juga berperan dalam mendukung ekonomi lokal dengan memfasilitasi pameran UMKM desa (seperti kerajinan lurik). Dengan fasilitas pendopo tradisional yang luas, tempat ini menjadi simbol gotong royong dan pusat pengembangan potensi Desa Wisata Karangasem.', 'tempat', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/potensi_desa/joglo.png', NULL, -7.80463582, 110.69481871, 3),
(4, 'Monumen Patung Kemerdekaan Soekarno', 'Monumen Patung Kemerdekaan Soekarno yang terletak di Dusun Bengkalan, Desa Karangasem, Kecamatan Cawas, merupakan tugu peringatan bersejarah yang dibangun pada tahun 2002 dan diresmikan oleh H. Muhamad Pranada pada 6 Juni 2002. Monumen ini didirikan atas inisiasi Kepala Desa Sugiyanto guna mengabadikan perjuangan Bung Karno bersama para perintis kemerdekaan lokal dari Dukuh Jonggo—yakni Syamsi Mangun Dimejo, Syayat Prawiro Dinomo, dan Wakiman Ponco Mulyono—yang pernah mengalami pengasingan demi membela tanah air, sekaligus mengenang momentum historis kunjungan Bung Karno ke wilayah tersebut pada kurun waktu 1927–1929 dalam rangka menggalang semangat perlawanan rakyat melawan penjajahan.', 'tempat', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/potensi_desa/monumen.png', NULL, -7.78984074, 110.70165386, 4),
(6, 'Karawitan', 'Karawitan merupakan salah satu kesenian tradisional yang masih lestari di Desa Karangasem. Kesenian ini tidak hanya menjadi sarana hiburan, tetapi juga menjadi media pelestarian budaya Jawa yang penuh makna dan nilai-nilai luhur. Di Desa Karangasem, karawitan tumbuh dan berkembang berkat dedikasi serta kecintaan terhadap seni dari Bapak Sugeng Suwarna, seorang sesepuh seni yang dikenal memiliki peran besar dalam menjaga warisan budaya lokal. Beliau merupakan pendiri sekaligus penggerak utama kelompok karawitan yang diberi nama “Sarilaras.”\r\n\r\nKelompok karawitan Sarilaras beranggotakan sekitar 21 orang yang terdiri dari warga desa dengan latar belakang dan usia yang beragam. Mereka bersama-sama berlatih dan tampil dalam berbagai acara desa, seperti upacara adat, peringatan hari besar, serta kegiatan kebudayaan lainnya. Dalam setiap penampilannya, kelompok ini menampilkan harmoni suara gamelan yang indah dan sarat makna, menggambarkan kekayaan budaya Jawa yang penuh filosofi.\r\nKegiatan karawitan Sarilaras dilaksanakan di kediaman Bapak Sugeng Suwarna yang sekaligus berfungsi sebagai sanggar karawitan desa. Di tempat tersebut tersimpan berbagai alat gamelan, seperti saron, demung, kending, siter, rebab, dan lain-lain yang menjadi perangkat utama dalam setiap pementasan. Sanggar ini tidak hanya menjadi tempat latihan, tetapi juga menjadi wadah pembelajaran bagi generasi muda untuk mengenal, mencintai, dan melestarikan seni karawitan sebagai bagian dari identitas budaya Desa Karangasem\r\n', 'budaya', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/potensi_desa/karawitan-1764073784.webp', 'https://www.youtube.com/', NULL, NULL, 5),
(7, 'STJ (Sekar Turonggo Jati)', 'Sekar Turonggo Jati (STJ) merupakan kelompok kesenian tari Jathilan/Kuda Lumping kebanggaan Desa Karangasem, yang secara resmi didirikan pada tanggal 23 Juli 2023. Nama ini kaya akan filosofi Jawa: Sekar (Bunga), Turonggo (Kuda/Jaran), dan Jati (Asli/Murni), yang melambangkan tekad untuk nguri-nguri (melestarikan) budaya sembari menciptakan kreasi tari yang otentik dan murni.\r\n\r\nKelompok ini lahir dari jiwa persatuan untuk mempersatukan masyarakat Karangasem yang majemuk dan memiliki beragam keyakinan serta pemikiran budaya. STJ berfokus pada kreasi tari kepang, menjadikannya kesenian baru yang khas di desa tersebut. Berbeda dengan pakem jathilan lain, STJ tidak menggunakan sajen dalam setiap pementasannya, menekankan fungsinya sebagai pertunjukan, hiburan, estetika, dan kesakralan (tanpa sajen).', 'budaya', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/potensi_desa/stj-sekar-turonggo-jati--1764073961.webp', 'https://youtube.com/@STJ-KARANGASEM?si=vMmV16beCMBqNgIH', NULL, NULL, 6);

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

--
-- Dumping data for table `riwayat_tanggapan`
--

INSERT INTO `riwayat_tanggapan` (`id`, `id_laporan`, `id_user`, `isi_tanggapan`, `status_laporan`, `created_at`) VALUES
(3, 34, 8, 'Saya Sedang Selidiki', 'diproses', '2025-11-24 19:49:12');

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

--
-- Dumping data for table `umkm`
--

INSERT INTO `umkm` (`id`, `id_user`, `diacc`, `nama_usaha`, `deskripsi_usaha`, `kategori_usaha`, `nama_pemilik_usaha`, `kontak_usaha`, `alamat_usaha`, `latitude`, `longitude`, `path_foto_usaha`, `created_at`, `qris`, `punya_whatsapp`, `no_wa_apakahsama`, `no_wa_berbeda`, `punya_instagram`, `username_instagram`, `punya_facebook`, `link_facebook`) VALUES
(11, 5, 1, 'Posko Giat 13 UNNES', '', 'warung', 'Nurfajar Pancareksa', '085697498080', 'Jonggo', -7.790751835513780, 110.698543361262950, 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/umkm/poskogiat13unnes_20251125090323.webp', '2025-11-24 07:40:37', 0, 1, 1, NULL, 1, '', 0, NULL);

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

--
-- Dumping data for table `umkmproduk`
--

INSERT INTO `umkmproduk` (`id`, `umkm_id`, `nama_produk`, `harga_produk`, `deskripsi_produk`, `path_foto_produk`, `created_at`) VALUES
(27, 11, 'Alya', 15000, '', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/umkm/fotoprodukumkm/alyaposkogiat13unnes20251124-07403644.webp', '2025-11-24 07:40:45'),
(28, 11, 'Dewi', 15000, '', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/umkm/fotoprodukumkm/dewiposkogiat13unnes20251124-07404342.webp', '2025-11-24 07:40:50'),
(29, 11, 'Fajar', 2500, '', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/umkm/fotoprodukumkm/fajarposkogiat13unnes20251124-07404944.webp', '2025-11-24 07:40:56'),
(30, 11, 'Ivan', 1000000, '', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/umkm/fotoprodukumkm/ivan20251125090405.webp', '2025-11-24 07:41:01'),
(31, 11, 'Sandi', 696969, '', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/umkm/fotoprodukumkm/sandiposkogiat13unnes20251124-07410080.webp', '2025-11-24 07:41:10');

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
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `alamat`, `jenis_kelamin`, `rw`, `no_hp`, `punya_whatsapp`, `username`, `password`, `level`, `path_foto_user`, `created_at`) VALUES
(3, 'ADMIN KARANGASEM 1', '<br />\r\n<b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>/var/www/html/index.php</b> on line <b>1266</b><br />\r\n', 'L', '3', '085697498080', 1, 'adminkarangasem1', '$2y$10$0EH/BLBmvM7FZ9cxUcyW0uySZjw/UZxqyDR8Jh2GM8r165A6SDAta', 'perangkat_desa', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/users/karangasem-defaultprofile.webp', '2025-11-23 12:41:16'),
(4, 'RW 1 KARANGASEM', 'rw 1', 'L', '1', '085697498080', 1, 'rw1karangasem', '$2y$10$OAIHnuRKgUVrvFMC4A6zZe5kyrfnk7HGjwS2ejh5g.ZmbGAVOSBPC', 'rw', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/users/karangasem-defaultprofile.webp', '2025-11-23 13:37:25'),
(5, 'Muhammad Ivan Aldorino', 'Jonggo', 'L', '6', '085697498080', 1, 'ivanaldorino', '$2y$10$Z36Z0a1yNTF8FiS.JX7fiu.KarKyWSVy6J4/LQI3ul5JyTTZB.CvO', 'user', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/users/20251124-210224muhammadivanaldorino.webp', '2025-11-24 05:46:17'),
(6, 'RW 7 KARANGASEM', 'rw7', 'L', '7', '085697498080', 1, 'rw7karangasem', '$2y$10$Tpe/ejTgy0HOaRJ5W5XmP.X6Rm14waEOVJWsb8MN4xbSThX9WedTG', 'rw', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/users/karangasem-defaultprofile.webp', '2025-11-24 16:16:34'),
(7, 'Masyarakat RW 7', 'rw 7', 'P', '7', '085697498080', 1, 'masyarakatrw7karangasem', '$2y$10$8eBumswv6w95NzYEtOBfvOszZotLaWyOuCpyn.WPRMWWM5SQiw6mC', 'user', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/users/karangasem-defaultprofile.webp', '2025-11-24 16:19:01'),
(8, 'RW 8 Karangasem', 'rw 8', 'L', '8', '085697498080', 1, 'rw8karangasem', '$2y$10$ZHIFKDDEcnft.PcGVLBZB.vjRw0Hbi7SAoyPaeMKrCVl1XUEfwlbm', 'rw', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/users/karangasem-defaultprofile.webp', '2025-11-24 17:40:52'),
(9, 'RW 10 Karangasem', 'RW10', 'L', '10', '085697498080', 1, 'rw10karangasem', '$2y$10$wPQaHJdtMVE30YQWetjE9eT8q1w7J264RABd7Zq7Pkh8hjHZWXori', 'user', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/users/karangasem-defaultprofile.webp', '2025-11-25 09:32:40'),
(10, 'RW 9 Karangasem', 'RW 9', 'L', '9', '085697498080', 1, 'rw9karangasem', '$2y$10$2b1fMua7qrmofs9BEdTLT.XC3H/zaeWfIYbfSYKat/2sFUyJK9z22', 'user', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/users/karangasem-defaultprofile.webp', '2025-11-25 09:39:40'),
(11, 'Wildan', 'Pundungsari, Karangasem, Cawas, Klaten ', 'L', '1', '087708303656', 1, 'OdadingMangOlee', '$2y$10$lRwImeaomwCz.xNTDK0BceW55E18f1UhNyOWv.BkvSRScYnsuPSIa', 'user', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/users/karangasem-defaultprofile.webp', '2025-11-26 04:35:24'),
(12, 'Budi', 'Jonggo', 'L', '8', '088888', 1, 'Budi01', '$2y$10$OZWigNxiUwVeW1ivY/njhOJVw/gj99JukbdxY.pVsj7LLoQDdiXA.', 'user', 'https://cdn.ivanaldorino.web.id/karangasem/websiteutama/users/karangasem-defaultprofile.webp', '2025-11-26 04:40:40');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `lokasi_umum`
--
ALTER TABLE `lokasi_umum`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `potensi_desa`
--
ALTER TABLE `potensi_desa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `riwayat_tanggapan`
--
ALTER TABLE `riwayat_tanggapan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `umkm`
--
ALTER TABLE `umkm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `umkmproduk`
--
ALTER TABLE `umkmproduk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
