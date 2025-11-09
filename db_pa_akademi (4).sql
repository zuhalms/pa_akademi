-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2025 at 12:45 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pa_akademi`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username_admin` varchar(50) NOT NULL,
  `nama_admin` varchar(100) NOT NULL,
  `email_admin` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_admin` enum('super_admin','admin') DEFAULT 'admin',
  `foto_admin` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username_admin`, `nama_admin`, `email_admin`, `password`, `role_admin`, `foto_admin`, `status`, `created_at`, `updated_at`) VALUES
(2, 'admin', 'Administrator', 'admin@example.com', '$2y$10$ovXHlAY2IJ736zWr3cdkseHvQdcgR4YDP7xz.HNs0ka64oTSWgrem', 'super_admin', NULL, 'active', '2025-11-07 15:01:35', '2025-11-07 15:01:35');

-- --------------------------------------------------------

--
-- Table structure for table `catatan_dosen`
--

CREATE TABLE `catatan_dosen` (
  `id_catatan` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `id_dosen` int(11) NOT NULL,
  `catatan` text NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `catatan_dosen`
--

INSERT INTO `catatan_dosen` (`id_catatan`, `nim`, `id_dosen`, `catatan`, `last_updated`) VALUES
(1, '2403030006', 12, 'silahkan menghadap ke saya dulu', '2025-11-05 21:00:06'),
(2, '18 0301 0015', 2, 'tolong menghadap ke saya\r\n', '2025-11-08 20:33:06');

-- --------------------------------------------------------

--
-- Table structure for table `dokumen`
--

CREATE TABLE `dokumen` (
  `id_dokumen` int(11) NOT NULL,
  `nim_mahasiswa` varchar(20) NOT NULL,
  `id_dosen` int(11) NOT NULL,
  `judul_dokumen` varchar(255) NOT NULL,
  `nama_file` varchar(255) NOT NULL COMMENT 'Nama file yang disimpan di server',
  `path_file` varchar(255) NOT NULL,
  `tipe_file` varchar(100) NOT NULL,
  `ukuran_file` int(11) NOT NULL COMMENT 'Dalam bytes',
  `tanggal_unggah` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_baca_dosen` enum('Belum Dilihat','Sudah Dilihat') NOT NULL DEFAULT 'Belum Dilihat'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dokumen`
--

INSERT INTO `dokumen` (`id_dokumen`, `nim_mahasiswa`, `id_dosen`, `judul_dokumen`, `nama_file`, `path_file`, `tipe_file`, `ukuran_file`, `tanggal_unggah`, `status_baca_dosen`) VALUES
(1, '18 0301 0015', 2, 'Khs salsabilla', '18 0301 0015_1760960589.pdf', 'dokumen/18 0301 0015_1760960589.pdf', 'pdf', 116943, '2025-10-20 11:43:09', 'Sudah Dilihat'),
(2, '18 0301 0015', 2, 'Khs salsabilla', '18 0301 0015_1760962982.pdf', 'dokumen/18 0301 0015_1760962982.pdf', 'pdf', 124311, '2025-10-20 12:23:02', 'Sudah Dilihat');

-- --------------------------------------------------------

--
-- Table structure for table `dokumen_prestasi`
--

CREATE TABLE `dokumen_prestasi` (
  `id_prestasi` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama_prestasi` varchar(255) NOT NULL,
  `jenis_prestasi` varchar(50) NOT NULL,
  `file_dokumen` varchar(255) NOT NULL,
  `tanggal_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dokumen_prestasi`
--

INSERT INTO `dokumen_prestasi` (`id_prestasi`, `nim`, `nama_prestasi`, `jenis_prestasi`, `file_dokumen`, `tanggal_upload`) VALUES
(1, '18 0301 0015', 'Debat Nasional', 'Akademik', 'prestasi_18 0301 0015_1762593825.pdf', '2025-11-08 17:23:45');

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id_dosen` int(11) NOT NULL,
  `nidn_dosen` varchar(20) DEFAULT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `nama_dosen` varchar(255) NOT NULL,
  `email_dosen` varchar(100) DEFAULT NULL,
  `telp_dosen` varchar(20) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `pendidikan_terakhir` varchar(50) DEFAULT NULL,
  `bidang_keahlian` varchar(255) DEFAULT NULL,
  `jabatan_akademik` varchar(100) DEFAULT NULL,
  `status_dosen` enum('Aktif','Non-Aktif','Cuti') DEFAULT 'Aktif',
  `password` varchar(255) NOT NULL,
  `foto_dosen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id_dosen`, `nidn_dosen`, `nip`, `nama_dosen`, `email_dosen`, `telp_dosen`, `tempat_lahir`, `tgl_lahir`, `jenis_kelamin`, `alamat`, `pendidikan_terakhir`, `bidang_keahlian`, `jabatan_akademik`, `status_dosen`, `password`, `foto_dosen`) VALUES
(1, 'Pembimbing Akademik', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aktif', '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL),
(2, '2002057203', '', 'Prof. Dr. A.SUKMAWATI ASSAAD, S.AG., M.PD', '', '', '', '0000-00-00', '', '', '', '', 'Lektor Kepala', 'Aktif', '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', 'dosen_2_1760900477.png'),
(3, '2015058001', NULL, 'SABARUDDIN, S.HI., M.HI', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aktif', '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL),
(4, '2001027703', NULL, 'Dr. FIRMAN MUHAMMAD ARIF, Lc., M.HI', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aktif', '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL),
(5, '0928119101', NULL, 'Ulfa, S.Sos.,M.Si', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aktif', '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL),
(6, '2007037002', NULL, 'Dr.,Dra. HELMI KAMAL, M.HI', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aktif', '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL),
(7, '2017029003', NULL, 'RIZKA AMELIA ARMIN, M.Si', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aktif', '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL),
(8, '2016128401', NULL, 'Dr. ARIFUDDIN, S.Pd.I., M.Pd.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aktif', '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL),
(9, '2030067402', NULL, 'Dr. MUHAMMAD TAHMID NUR, M.Ag', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aktif', '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL),
(10, '2031125811', NULL, 'Prof. Dr. HAMZAH K., M.HI', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aktif', '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL),
(11, '2021108901', NULL, 'Syamsuddin, S.H.I., M.H.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Aktif', '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `evaluasi_dosen`
--

CREATE TABLE `evaluasi_dosen` (
  `id_evaluasi_dosen` int(11) NOT NULL,
  `nim_mahasiswa` varchar(20) NOT NULL,
  `id_dosen` int(11) NOT NULL,
  `periode_evaluasi` varchar(50) NOT NULL,
  `skor_komunikasi` int(11) NOT NULL,
  `skor_membantu` int(11) NOT NULL,
  `skor_solusi` int(11) NOT NULL,
  `saran_kritik` text DEFAULT NULL,
  `tanggal_submit` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluasi_dosen`
--

INSERT INTO `evaluasi_dosen` (`id_evaluasi_dosen`, `nim_mahasiswa`, `id_dosen`, `periode_evaluasi`, `skor_komunikasi`, `skor_membantu`, `skor_solusi`, `saran_kritik`, `tanggal_submit`) VALUES
(1, '2103010022', 10, '2025 Ganjil', 5, 5, 5, 'sangat baik', '2025-10-16 02:10:51');

-- --------------------------------------------------------

--
-- Table structure for table `evaluasi_softskill`
--

CREATE TABLE `evaluasi_softskill` (
  `id_evaluasi` int(11) NOT NULL,
  `nim_mahasiswa` varchar(20) NOT NULL,
  `id_dosen` int(11) NOT NULL,
  `periode_evaluasi` varchar(50) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `skor` int(11) NOT NULL,
  `catatan` text DEFAULT NULL,
  `tanggal_evaluasi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluasi_softskill`
--

INSERT INTO `evaluasi_softskill` (`id_evaluasi`, `nim_mahasiswa`, `id_dosen`, `periode_evaluasi`, `kategori`, `skor`, `catatan`, `tanggal_evaluasi`) VALUES
(1, '18 0301 0015', 2, '2025 Ganjil', 'Disiplin & Komitmen', 5, '0', '2025-10-15 16:51:43'),
(2, '18 0301 0015', 2, '2025 Ganjil', 'Partisipasi & Keaktifan', 5, '0', '2025-10-15 16:51:43'),
(3, '18 0301 0015', 2, '2025 Ganjil', 'Etika & Sopan Santun', 5, '0', '2025-10-15 16:51:43'),
(4, '18 0301 0015', 2, '2025 Ganjil', 'Kepemimpinan & Kerjasama', 5, '0', '2025-10-15 16:51:43'),
(5, '18 0301 0015', 2, '2025 Ganjil', 'Disiplin & Komitmen', 4, NULL, '2025-10-25 11:47:45'),
(6, '18 0301 0015', 2, '2025 Ganjil', 'Partisipasi & Keaktifan', 4, NULL, '2025-10-25 11:47:45'),
(7, '18 0301 0015', 2, '2025 Ganjil', 'Etika & Sopan Santun', 5, NULL, '2025-10-25 11:47:45'),
(8, '18 0301 0015', 2, '2025 Ganjil', 'Kepemimpinan & Kerjasama', 5, NULL, '2025-10-25 11:47:45');

-- --------------------------------------------------------

--
-- Table structure for table `konsultasi_judul`
--

CREATE TABLE `konsultasi_judul` (
  `id_konsultasi` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `judul_usulan` text NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_pengajuan` datetime DEFAULT current_timestamp(),
  `status` enum('Menunggu','Disetujui','Ditolak','Revisi') DEFAULT 'Menunggu',
  `catatan_dosen` text DEFAULT NULL,
  `tanggal_respon` datetime DEFAULT NULL,
  `id_dosen` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `krs`
--

CREATE TABLE `krs` (
  `id_krs` int(11) NOT NULL,
  `nim_mahasiswa` varchar(20) NOT NULL,
  `id_mk` int(11) NOT NULL,
  `semester_diambil` int(2) NOT NULL,
  `nilai_huruf` char(2) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `sudah_dinilai` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logbook`
--

CREATE TABLE `logbook` (
  `id_log` int(11) NOT NULL,
  `nim_mahasiswa` varchar(20) NOT NULL,
  `id_dosen` int(11) NOT NULL,
  `pengisi` enum('Dosen','Mahasiswa') NOT NULL DEFAULT 'Dosen',
  `status_baca` enum('Dibaca','Belum Dibaca') NOT NULL DEFAULT 'Belum Dibaca',
  `tanggal_bimbingan` date NOT NULL,
  `topik_bimbingan` varchar(255) NOT NULL,
  `isi_bimbingan` text DEFAULT NULL,
  `tindak_lanjut` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logbook`
--

INSERT INTO `logbook` (`id_log`, `nim_mahasiswa`, `id_dosen`, `pengisi`, `status_baca`, `tanggal_bimbingan`, `topik_bimbingan`, `isi_bimbingan`, `tindak_lanjut`, `created_at`) VALUES
(2, '2103010022', 10, 'Dosen', 'Dibaca', '2025-10-15', 'pengusulan topik penelitian', 'tes', 'tes123\r\n', '2025-10-15 16:47:13'),
(9, '2103010022', 10, 'Dosen', 'Dibaca', '2025-10-18', 'pengusulan topik penelitian', 'silahkan menghadap ke saya untuk melakukan konsultasi tentang topik yang akan  kamu angkat', '', '2025-10-18 06:56:16'),
(10, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-10-19', 'pengusulan topik penelitian', 'tes', '', '2025-10-19 18:15:21'),
(13, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-20', 'Tindak Lanjut Nilai: Metode Penelitian Hukum', 'Berdasarkan laporan, nilai Anda untuk mata kuliah \"Metode Penelitian Hukum\" adalah C. Mohon segera diskusikan rencana perbaikannya.\r\n\r\n', 'menghadap ke saya ketika selesai', '2025-10-20 12:30:54'),
(14, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-20', 'Tindak Lanjut Nilai: Hukum Ketenagakerjaan', 'Berdasarkan laporan, nilai Anda untuk mata kuliah \"Hukum Ketenagakerjaan\" adalah D. Mohon segera diskusikan rencana perbaikannya.\r\n\r\nCatatan tambahan:\r\nmenghadap ke saya ketika selesai', '', '2025-10-20 12:31:08'),
(15, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-20', 'Tindak Lanjut Nilai: Filsafat Hukum', 'Berdasarkan laporan, nilai Anda untuk mata kuliah \"Filsafat Hukum\" adalah E. Mohon segera diskusikan rencana perbaikannya.\r\n\r\nCatatan tambahan:\r\nmenghadap ke saya ketika selesai\r\n', '', '2025-10-20 12:31:18'),
(16, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-21', 'Peringatan Akademik Terkait Nilai', 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\r\n\r\n- Metode Penelitian Hukum (Nilai: C)\r\n- Hukum Ketenagakerjaan (Nilai: E)\r\n- Filsafat Hukum (Nilai: E)\r\n\r\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.', '', '2025-10-21 11:38:07'),
(17, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-21', 'Peringatan Akademik Terkait Nilai', 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\r\n\r\n- Metode Penelitian Hukum (Nilai: C)\r\n- Hukum Ketenagakerjaan (Nilai: E)\r\n- Filsafat Hukum (Nilai: E)\r\n\r\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.', '', '2025-10-21 11:42:52'),
(18, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-21', 'Peringatan Akademik Terkait Nilai', 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\r\n\r\n- Metode Penelitian Hukum (Nilai: C)\r\n- Hukum Ketenagakerjaan (Nilai: E)\r\n- Filsafat Hukum (Nilai: E)\r\n\r\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.', '', '2025-10-21 11:44:53'),
(19, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-21', 'Peringatan Akademik Terkait Nilai', 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\r\n\r\n- Metode Penelitian Hukum (Nilai: C)\r\n- Hukum Ketenagakerjaan (Nilai: E)\r\n- Filsafat Hukum (Nilai: E)\r\n\r\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.', '', '2025-10-21 11:45:46'),
(20, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-21', 'Peringatan Akademik Terkait Nilai', 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\r\n\r\n- Metode Penelitian Hukum (Nilai: C)\r\n- Hukum Ketenagakerjaan (Nilai: E)\r\n- Filsafat Hukum (Nilai: E)\r\n\r\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.', '', '2025-10-21 11:52:28'),
(21, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-21', 'Peringatan Akademik Terkait Nilai', 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\r\n\r\n- Metode Penelitian Hukum (Nilai: C)\r\n- Hukum Ketenagakerjaan (Nilai: E)\r\n- Filsafat Hukum (Nilai: E)\r\n\r\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.', '', '2025-10-21 11:54:21'),
(22, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-21', 'Peringatan Akademik Terkait Nilai', 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\r\n\r\n- Array (Nilai: Ar)\r\n- Bahasa Indonesia (Nilai: C)\r\n- Fiqh Siyasah (Nilai: D)\r\n- Hukum Keuangan Negara (Nilai: E)\r\n\r\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.', '', '2025-10-21 12:03:56'),
(23, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-21', 'Peringatan Akademik Terkait Nilai', 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\r\n\r\n- Array (Nilai: Ar)\r\n- Bahasa Indonesia (Nilai: C)\r\n- Fiqh Siyasah (Nilai: D)\r\n- Hukum Keuangan Negara (Nilai: E)\r\n\r\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.', '', '2025-10-21 12:04:22'),
(24, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-21', 'Peringatan Akademik Terkait Nilai', 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\r\n\r\n- Array (Nilai: Ar)\r\n- Bahasa Indonesia (Nilai: C)\r\n- Fiqh Siyasah (Nilai: D)\r\n- Hukum Keuangan Negara (Nilai: E)\r\n\r\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.', '', '2025-10-21 12:05:10'),
(25, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-25', 'Peringatan Akademik Terkait Nilai', 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\r\n\r\n- Hukum Ketenagakerjaan (Nilai: D)\r\n- Hukum Perdata Islam di Indonesia (Nilai: E)\r\n\r\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.', '', '2025-10-25 07:36:00'),
(26, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-10-25', 'Peringatan Akademik Terkait Nilai', 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\r\n\r\n- Hukum Konstitusi (Nilai: E)\r\n\r\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.', '', '2025-10-25 11:59:11'),
(27, '18 0301 0015', 2, 'Mahasiswa', 'Dibaca', '2025-11-07', 'sadasdsad', 'asaasa', NULL, '2025-11-07 10:18:45'),
(28, '1903010004', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Peringatan Akademik Terkait Nilai', 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\r\n\r\n- Bahasa Arab (Nilai: D)\r\n\r\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.', '', '2025-11-07 10:54:16'),
(29, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS Dibatalkan', 'Persetujuan KRS Anda dibatalkan oleh Dosen PA. Silakan hubungi dosen untuk diskusi lebih lanjut.', NULL, '2025-11-07 11:01:43'),
(30, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS', 'KRS Anda telah disetujui oleh Dosen PA. Anda dapat melanjutkan ke tahap berikutnya.', NULL, '2025-11-07 11:01:47'),
(31, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Pembaruan Status Akademik', 'Status akademik Anda telah diubah menjadi NON-AKTIF oleh Dosen PA. Mohon segera hubungi dosen Anda.', NULL, '2025-11-07 11:01:49'),
(32, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Pembaruan Status Akademik', 'Status akademik Anda telah diubah menjadi AKTIF oleh Dosen PA.', NULL, '2025-11-07 11:01:52'),
(33, '2003010032', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS Dibatalkan', 'Persetujuan KRS Anda dibatalkan oleh Dosen PA. Silakan hubungi dosen untuk diskusi lebih lanjut.', NULL, '2025-11-07 11:02:06'),
(34, '2003010032', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS', 'KRS Anda telah disetujui oleh Dosen PA. Anda dapat melanjutkan ke tahap berikutnya.', NULL, '2025-11-07 11:02:08'),
(35, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS Dibatalkan', 'Persetujuan KRS Anda dibatalkan oleh Dosen PA. Silakan hubungi dosen untuk diskusi lebih lanjut.', NULL, '2025-11-07 11:02:09'),
(36, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS', 'KRS Anda telah disetujui oleh Dosen PA. Anda dapat melanjutkan ke tahap berikutnya.', NULL, '2025-11-07 11:02:12'),
(37, '2003010032', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS Dibatalkan', 'Persetujuan KRS Anda dibatalkan oleh Dosen PA. Silakan hubungi dosen untuk diskusi lebih lanjut.', NULL, '2025-11-07 11:02:18'),
(38, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS Dibatalkan', 'Persetujuan KRS Anda dibatalkan oleh Dosen PA. Silakan hubungi dosen untuk diskusi lebih lanjut.', NULL, '2025-11-07 11:02:19'),
(39, '2003010032', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS', 'KRS Anda telah disetujui oleh Dosen PA. Anda dapat melanjutkan ke tahap berikutnya.', NULL, '2025-11-07 11:02:22'),
(40, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS', 'KRS Anda telah disetujui oleh Dosen PA. Anda dapat melanjutkan ke tahap berikutnya.', NULL, '2025-11-07 11:02:23'),
(41, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Pembaruan Status Akademik', 'Status akademik Anda telah diubah menjadi NON-AKTIF oleh Dosen PA. Mohon segera hubungi dosen Anda.', NULL, '2025-11-07 11:02:32'),
(42, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS Dibatalkan', 'Persetujuan KRS Anda dibatalkan oleh Dosen PA. Silakan hubungi dosen untuk diskusi lebih lanjut.', NULL, '2025-11-07 11:02:40'),
(43, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS', 'KRS Anda telah disetujui oleh Dosen PA. Anda dapat melanjutkan ke tahap berikutnya.', NULL, '2025-11-07 11:04:20'),
(44, '1903010031', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Pembaruan Status Akademik', 'Status akademik Anda telah diubah menjadi AKTIF oleh Dosen PA.', NULL, '2025-11-07 11:04:22'),
(45, '2003010032', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Persetujuan KRS Dibatalkan', 'Persetujuan KRS Anda dibatalkan oleh Dosen PA. Silakan hubungi dosen untuk diskusi lebih lanjut.', NULL, '2025-11-07 11:04:25'),
(46, '2003010032', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Pembaruan Status Akademik', 'Status akademik Anda telah diubah menjadi NON-AKTIF oleh Dosen PA. Mohon segera hubungi dosen Anda.', NULL, '2025-11-07 11:04:27'),
(47, '2003010032', 2, 'Dosen', 'Belum Dibaca', '2025-11-07', 'Pembaruan Status Akademik', 'Status akademik Anda telah diubah menjadi AKTIF oleh Dosen PA.', NULL, '2025-11-07 11:04:35'),
(48, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', 'zsfasfd', '', '2025-11-07 12:12:51'),
(49, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', 'asdasdasd', '', '2025-11-07 12:13:01'),
(50, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', '', '', '2025-11-07 12:18:11'),
(51, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', '', '', '2025-11-07 12:18:20'),
(52, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', '', '', '2025-11-07 12:18:44'),
(53, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', 'safasfa\r\n', '', '2025-11-07 12:18:56'),
(54, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', '', '', '2025-11-07 12:20:47'),
(55, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', '', '', '2025-11-07 12:21:28'),
(56, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', 'hsdfgsdhfgsjd', '', '2025-11-07 12:21:43'),
(57, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', 'asdsadadad', '', '2025-11-07 12:24:29'),
(58, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', 'asdsadadad', '', '2025-11-07 12:52:46'),
(59, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', 'asdsadadad', '', '2025-11-07 13:22:44'),
(60, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-07', 'Catatan Dosen', 'astttdastdastd\r\n', '', '2025-11-07 13:23:02'),
(61, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-08', 'Catatan Dosen', 'tolong menghadap ke saya\r\n', '', '2025-11-08 20:33:06'),
(62, '18 0301 0015', 2, 'Dosen', 'Dibaca', '2025-11-08', 'Catatan Dosen', 'tolong menghadap ke saya\r\n', '', '2025-11-08 20:35:39');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `nim` varchar(20) NOT NULL,
  `nama_mahasiswa` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tgl_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `jenjang` varchar(10) DEFAULT 'S1',
  `hobi` text DEFAULT NULL,
  `angkatan` int(11) NOT NULL,
  `status_semester` char(1) DEFAULT NULL COMMENT 'Contoh: A (Aktif), N (Non-Aktif)',
  `semester_berjalan` int(11) DEFAULT NULL,
  `sks_semester` int(11) DEFAULT NULL,
  `batas_sks` int(11) DEFAULT NULL,
  `total_sks` int(11) DEFAULT NULL,
  `ips` decimal(3,2) DEFAULT NULL,
  `ipk` decimal(3,2) DEFAULT NULL,
  `krs_disetujui` tinyint(1) DEFAULT 0,
  `krs_notif_dilihat` tinyint(1) NOT NULL DEFAULT 0,
  `id_prodi` int(11) DEFAULT NULL,
  `id_dosen_pa` int(11) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `foto_mahasiswa` varchar(255) DEFAULT NULL,
  `status` enum('Aktif','Non-Aktif') NOT NULL DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`nim`, `nama_mahasiswa`, `email`, `telp`, `alamat`, `tempat_lahir`, `tgl_lahir`, `jenis_kelamin`, `jenjang`, `hobi`, `angkatan`, `status_semester`, `semester_berjalan`, `sks_semester`, `batas_sks`, `total_sks`, `ips`, `ipk`, `krs_disetujui`, `krs_notif_dilihat`, `id_prodi`, `id_dosen_pa`, `password`, `foto_mahasiswa`, `status`) VALUES
('18 0301 0015', 'Salsabila Syamsuddin', 'zuhalsamas@gmail.com', '1234567890000', 'Palopo', 'Masamba', '1998-08-22', 'Perempuan', 'S1', 'Membaca', 2018, 'A', 15, 0, 24, 126, 0.00, 2.96, 1, 1, 2, 2, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', 'mhs_18 0301 0015_1760900607.png', 'Aktif'),
('18 0301 0032', 'Hasriani', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 152, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0035', 'Wiranto', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 132, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0036', 'Ilham Adnan Zaiman', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'A', 15, 0, 24, 98, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0039', 'Rini Antika Sari', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 80, 0.00, 3.00, 0, 0, 2, 4, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0050', 'Rahmawati', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 96, 0.00, 3.00, 0, 0, 2, 4, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0051', 'Zulaika', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 96, 0.00, 3.00, 0, 0, 2, 4, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0054', 'Aprilia Dili Akoit', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 148, 0.00, 3.00, 0, 0, 2, 5, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0065', 'Finki Kumala Balisa', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 96, 0.00, 3.00, 0, 0, 2, 4, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0066', 'Nismawati Wallung', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 154, 0.00, 3.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0072', 'Dila Nurmalasari', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 96, 0.00, 3.00, 0, 0, 2, 4, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0079', 'Muh. Fahri Asyay T', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 108, 0.00, 3.00, 0, 0, 2, 7, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0084', 'Yulia Sari', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 96, 0.00, 3.00, 0, 0, 2, 7, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0085', 'Kurnia', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 96, 0.00, 3.00, 0, 0, 2, 7, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('18 0301 0086', 'Nursida', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2018, 'N', 15, 0, 24, 96, 0.00, 3.00, 0, 0, 2, 4, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010004', 'YULIA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 0.00, 1, 0, 2, 2, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010007', 'LUSI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 150, 0.00, 3.00, 0, 0, 2, 2, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010016', 'JAGRATARA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'A', 13, 0, 24, 48, 0.00, 2.00, 0, 0, 2, 2, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010017', 'NUR SYAFINA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 150, 0.00, 3.00, 0, 0, 2, 2, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010019', 'NURFITRA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 0.00, 0, 0, 2, 2, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010026', 'RISKA HANDAYANI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 150, 0.00, 3.00, 0, 0, 2, 2, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010028', 'FAJRUL FALAKH', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 156, 0.00, 3.00, 0, 0, 2, 2, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010031', 'ANNI\'', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'A', 13, 0, 24, 96, 0.00, 3.00, 1, 0, 2, 2, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010034', 'NURUL HALIMATUSSA\'DIYAH', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 154, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010035', 'SYAHRIANI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 150, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010039', 'PEBY PRASETYA PRAKARSA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010040', 'FADEL MUHAMMAD', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'A', 13, 4, 24, 155, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010041', 'NURMILA SARI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 72, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010044', 'WARDA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 116, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010045', 'UCCI ANDAYANI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 160, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010048', 'PITRI MILADIA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010051', 'SYAM WIJAYA PUTRA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 154, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010052', 'SUKMA AYU', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'A', 13, 4, 24, 151, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010053', 'RAHMAT HIDAYAT', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 134, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010058', 'HASDA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 140, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010060', 'ANDI TAWAKKAL RAMADHAN', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 2.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010062', 'MUSTIKA AYU J', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010063', 'MUH FAIZAL', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 2.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010064', 'DIAN FADILA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 2.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010067', 'AHMAD USHULUDDIN', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 3.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010069', 'SESIL DESWITA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 2.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010071', 'RIAN S', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 162, 0.00, 2.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010073', 'MUH ALFATH RAMADHAN', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 2.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010078', 'M. YUSUF SUNDY TABANG', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 150, 0.00, 3.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010080', 'ANGGA HASRI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 102, 0.00, 3.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010082', 'ASDAR', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'A', 13, 4, 24, 152, 0.00, 3.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010087', 'IRVAN', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 150, 0.00, 3.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010088', 'NURUL JIHAD', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 0.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010089', 'REGITA CAHYANI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'A', 13, 4, 24, 152, 0.00, 3.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010093', 'SYAHRUL', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 154, 0.00, 3.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010098', 'SYAHRIL', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 48, 0.00, 0.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('1903010099', 'RIFAI RIDWAN', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2019, 'N', 13, 0, 24, 52, 0.00, 3.00, 0, 0, 2, 6, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2003010004', 'NURILA SARIFUDDIN', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2020, 'N', 11, 0, 24, 44, 0.00, 3.00, 0, 0, 2, 8, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2003010010', 'NURUL ANNISA ALKEYZIA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2020, 'N', 11, 0, 24, 132, 0.00, 3.00, 0, 0, 2, 2, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2003010013', 'MUH AGUS ANUGRAH', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2020, 'A', 11, 0, 24, 154, 0.00, 3.00, 0, 0, 2, 2, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2003010017', 'ANANDA JULIA CHAIDIN', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2020, 'N', 11, 0, 24, 154, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2003010021', 'INAS MASYA\'IL', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2020, 'N', 11, 0, 24, 24, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2003010025', 'RESKY WIJAYA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2020, 'N', 11, 0, 24, 146, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2003010027', 'ANNISA ASLI SUFID', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2020, 'N', 11, 0, 24, 170, 0.00, 3.00, 0, 0, 2, 3, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2003010032', 'DARMAWAN', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2020, 'A', 11, 0, 24, 24, 0.00, 3.00, 0, 0, 2, 2, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010003', 'NUR AMILAN.S', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'N', 9, 0, 24, 154, 0.00, 3.00, 0, 0, 2, 9, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010004', 'KARINA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'A', 9, 4, 24, 150, 0.00, 3.00, 0, 0, 2, 9, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010008', 'ANDI NURJANNAH', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'N', 9, 0, 24, 24, 0.00, 3.00, 0, 0, 2, 9, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010009', 'MUHAMMAD THARIQ SYAUQY MUZHAFFAR', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'A', 9, 23, 24, 142, 0.00, 3.00, 0, 0, 2, 9, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010010', 'SAFARUDDIN', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'N', 9, 0, 24, 24, 0.00, 3.00, 0, 0, 2, 9, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010011', 'ANDI TENRI BATARI RAHMAN', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'A', 9, 4, 24, 152, 0.00, 3.00, 0, 0, 2, 10, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010012', 'MAYA FEBRIANTI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'N', 9, 0, 24, 142, 0.00, 3.00, 0, 0, 2, 10, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010013', 'WINDA RAWINTA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'N', 9, 0, 24, 72, 0.00, 3.00, 0, 0, 2, 10, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010014', 'MUFIDAH MAHMUD', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'A', 9, 4, 24, 150, 0.00, 3.00, 0, 0, 2, 10, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010015', 'IMAM HIDAYAT', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'N', 9, 0, 24, 24, 0.00, 3.00, 0, 0, 2, 10, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010016', 'DEWI SARFIKA NENGSI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'A', 9, 4, 24, 167, 0.00, 3.00, 0, 0, 2, 10, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010018', 'HARIONO', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'N', 9, 0, 24, 150, 0.00, 3.00, 0, 0, 2, 10, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010022', 'ARMY', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'A', 9, 4, 24, 160, 0.00, 3.00, 1, 1, 2, 10, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010026', 'Muh Fadhel Zacky Risal Syam', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'N', 9, 0, 24, 138, 0.00, 3.00, 0, 0, 2, 10, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010029', 'YUSMILASARI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'A', 9, 4, 24, 151, 0.00, 3.00, 0, 0, 2, 10, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010030', 'MUH IRSAN HUSAIN', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'N', 9, 4, 24, 150, 0.00, 3.00, 0, 0, 2, 9, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010031\nSapoetra,', 'MUH. RAFLI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'N', 9, 0, 24, 154, 0.00, 3.00, 0, 0, 2, 9, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010032', 'MUH. QAYYUM', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'A', 9, 4, 24, 150, 0.00, 3.00, 0, 0, 2, 9, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010033', 'DIAN RESA SUHARDI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'N', 9, 0, 24, 155, 0.00, 3.00, 0, 0, 2, 9, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010036', 'Fauziah Rahmi Ihsan', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'A', 9, 4, 24, 153, 0.00, 3.00, 0, 0, 2, 10, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2103010037', 'SITTI SAKINA MIMBO', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2021, 'N', 9, 0, 24, 118, 0.00, 3.00, 0, 0, 2, 10, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010001', 'ADE PUTRI RAHAYU', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010002', 'ALFIN NUR HIDAYAH', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010003', 'HILDA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010004', 'NURUL SALSABILA PATTY', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'N', 7, 0, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010005', 'AINUN JUNITA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010006', 'VIA ANANTA SYAM', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 10, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010007', 'NUR AULIA MALIK', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010008', 'MIFTAHUL HAIRA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010009', 'ULVARIANI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010010', 'HAFZA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010011', 'ANNISA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010012', 'NUR WULANG SAMSIYA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010013', 'MUH. ARDIANSYA', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010014', 'DIDI ARDIANSYAH', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'N', 7, 0, 24, 65, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010015', 'MUH.TAUFIK', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010016', 'ANDI FITRI HANDAYANI', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('2203010017', 'LILIS NURHALIZAH', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 2022, 'A', 7, 8, 24, 137, 0.00, 3.00, 0, 0, 2, 11, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif'),
('NIM', 'Nama', NULL, NULL, NULL, NULL, NULL, NULL, 'S1', NULL, 0, 'S', 0, 0, 0, 0, 0.00, 0.00, 0, 0, 1, 1, '$2y$10$3Rzf3.JfM1bzX7FxRbBqF.xyqutFaP3J/96AFaUqG8nJDNyPdXDo2', NULL, 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `mata_kuliah`
--

CREATE TABLE `mata_kuliah` (
  `id_mk` int(11) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `sks` int(2) DEFAULT NULL,
  `id_prodi` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mata_kuliah`
--

INSERT INTO `mata_kuliah` (`id_mk`, `nama_mk`, `sks`, `id_prodi`) VALUES
(1, 'Bahasa Arab', NULL, NULL),
(2, 'Bahasa Indonesia', NULL, NULL),
(3, 'Bahasa Inggris', NULL, NULL),
(4, 'Fiqh Jinayah', NULL, NULL),
(5, 'Fiqh Siyasah', NULL, NULL),
(6, 'Filsafat Hukum', NULL, NULL),
(7, 'Filsafat Hukum Islam', NULL, NULL),
(8, 'Hukum Acara Peradilan Agama', NULL, NULL),
(9, 'Hukum Acara PTUN', 2, NULL),
(10, 'Hukum Administrasi Negara', NULL, NULL),
(11, 'Hukum Agraria', NULL, NULL),
(12, 'Hukum Keuangan Negara', 2, NULL),
(13, 'Hukum Ketenagakerjaan', 2, NULL),
(14, 'Hukum Konstitusi', NULL, NULL),
(15, 'Hukum Perdata Islam di Indonesia', NULL, NULL),
(16, 'Ilmu Negara', NULL, NULL),
(17, 'Kaidah-kaidah Siyasah Syari\'iyyah', 2, NULL),
(18, 'Kapita Selekta HTN', NULL, NULL),
(19, 'Kewarganegaraan', NULL, NULL),
(20, 'Metode Penelitian Hukum', NULL, NULL),
(21, 'Pancasila', NULL, NULL),
(22, 'Pengantar Hukum Indonesia', NULL, NULL),
(23, 'Pengantar Ilmu Hukum', NULL, NULL),
(24, 'Perancangan Kontrak', NULL, NULL),
(25, 'Politik Hukum Islam', 3, NULL),
(26, 'Sosiologi Hukum', 2, NULL),
(27, 'Studi Keislaman', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `nilai_bermasalah`
--

CREATE TABLE `nilai_bermasalah` (
  `id_nilai` int(11) NOT NULL,
  `nim_mahasiswa` varchar(20) NOT NULL,
  `nama_mk` varchar(255) NOT NULL,
  `nilai_huruf` char(2) NOT NULL,
  `semester_diambil` int(2) NOT NULL,
  `status_perbaikan` enum('Belum','Sudah') NOT NULL DEFAULT 'Belum',
  `tanggal_lapor` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nilai_bermasalah`
--

INSERT INTO `nilai_bermasalah` (`id_nilai`, `nim_mahasiswa`, `nama_mk`, `nilai_huruf`, `semester_diambil`, `status_perbaikan`, `tanggal_lapor`) VALUES
(8, '1903010016', 'Hukum Keuangan Negara', 'E', 1, 'Belum', '2025-10-24 20:04:31');

-- --------------------------------------------------------

--
-- Table structure for table `nilai_mahasiswa`
--

CREATE TABLE `nilai_mahasiswa` (
  `id_nilai` int(11) NOT NULL,
  `nim_mahasiswa` varchar(20) NOT NULL,
  `kode_mk` varchar(20) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `nilai_huruf` char(2) NOT NULL,
  `semester_diambil` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nilai_mahasiswa`
--

INSERT INTO `nilai_mahasiswa` (`id_nilai`, `nim_mahasiswa`, `kode_mk`, `nama_mk`, `nilai_huruf`, `semester_diambil`) VALUES
(1, '18 0301 0015', 'MANUAL', 'Bahasa Arab', 'D', 3),
(2, '18 0301 0015', 'MANUAL', 'Bahasa Indonesia', 'D', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pencapaian`
--

CREATE TABLE `pencapaian` (
  `id_pencapaian` int(11) NOT NULL,
  `nim_mahasiswa` varchar(20) NOT NULL,
  `nama_pencapaian` varchar(100) NOT NULL,
  `status` enum('Selesai','Belum Selesai') NOT NULL DEFAULT 'Belum Selesai',
  `tanggal_selesai` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pencapaian`
--

INSERT INTO `pencapaian` (`id_pencapaian`, `nim_mahasiswa`, `nama_pencapaian`, `status`, `tanggal_selesai`) VALUES
(1, '2003010013', 'Seminar Proposal', 'Selesai', '2025-10-17'),
(2, '2003010013', 'Penelitian Selesai', 'Belum Selesai', NULL),
(3, '2003010013', 'Seminar Hasil', 'Belum Selesai', NULL),
(4, '2003010013', 'Ujian Skripsi (Yudisium)', 'Belum Selesai', NULL),
(5, '2003010013', 'Publikasi Jurnal', 'Belum Selesai', NULL),
(6, '18 0301 0015', 'Seminar Proposal', 'Selesai', '2025-11-09'),
(7, '18 0301 0015', 'Penelitian Selesai', 'Belum Selesai', NULL),
(8, '18 0301 0015', 'Seminar Hasil', 'Belum Selesai', NULL),
(9, '18 0301 0015', 'Ujian Skripsi (Yudisium)', 'Belum Selesai', NULL),
(10, '18 0301 0015', 'Publikasi Jurnal', 'Belum Selesai', NULL),
(53, '18 0301 0015', 'Ujian Komperehensif', 'Belum Selesai', NULL),
(66, '1903010004', 'Seminar Proposal', 'Selesai', '2025-11-07'),
(67, '1903010004', 'Ujian Komperehensif', 'Selesai', '2025-11-07'),
(68, '1903010004', 'Seminar Hasil', 'Belum Selesai', NULL),
(69, '1903010004', 'Ujian Skripsi (Yudisium)', 'Belum Selesai', NULL),
(70, '1903010004', 'Publikasi Jurnal', 'Belum Selesai', NULL),
(71, '18 0301 0015', 'Konsultasi Judul', 'Selesai', '2025-11-09');

-- --------------------------------------------------------

--
-- Table structure for table `program_studi`
--

CREATE TABLE `program_studi` (
  `id_prodi` int(11) NOT NULL,
  `nama_prodi` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_studi`
--

INSERT INTO `program_studi` (`id_prodi`, `nama_prodi`) VALUES
(2, 'Hukum Keluarga\n(Akhwal Syakhsiyyah)'),
(1, 'Program Studi');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_akademik`
--

CREATE TABLE `riwayat_akademik` (
  `id_riwayat` int(11) NOT NULL,
  `nim_mahasiswa` varchar(20) NOT NULL,
  `semester` int(11) NOT NULL,
  `ip_semester` decimal(3,2) NOT NULL,
  `sks_semester` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_akademik`
--

INSERT INTO `riwayat_akademik` (`id_riwayat`, `nim_mahasiswa`, `semester`, `ip_semester`, `sks_semester`) VALUES
(1, '2103010013', 1, 3.80, 23),
(2, '2103010013', 2, 3.43, 18),
(3, '2103010013', 3, 3.17, 24),
(4, '2103010013', 4, 3.02, 20),
(5, '2103010013', 5, 2.95, 20),
(6, '2103010013', 6, 2.81, 20),
(7, '2103010013', 7, 2.48, 21),
(8, '2103010013', 8, 2.08, 24),
(9, '18 0301 0035', 1, 3.80, 19),
(10, '18 0301 0035', 2, 3.35, 18),
(11, '18 0301 0035', 3, 3.10, 21),
(12, '18 0301 0035', 4, 2.99, 21),
(13, '18 0301 0035', 5, 2.76, 23),
(14, '18 0301 0035', 6, 2.49, 20),
(15, '18 0301 0035', 7, 2.39, 20),
(16, '18 0301 0035', 8, 2.12, 21),
(17, '2103010032', 1, 3.80, 19),
(18, '2103010032', 2, 3.32, 24),
(19, '2103010032', 3, 3.27, 20),
(20, '2103010032', 4, 2.89, 18),
(21, '2103010032', 5, 2.71, 20),
(22, '2103010032', 6, 2.29, 18),
(23, '2103010032', 7, 2.13, 24),
(24, '2103010032', 8, 2.03, 19),
(25, '2003010017', 1, 3.80, 22),
(26, '2003010017', 2, 3.31, 19),
(27, '2003010017', 3, 3.16, 20),
(28, '2003010017', 4, 2.73, 24),
(29, '2003010017', 5, 2.30, 23),
(30, '2003010017', 6, 2.18, 21),
(31, '2003010017', 7, 1.83, 22),
(32, '18 0301 0054', 1, 3.80, 22),
(33, '18 0301 0054', 2, 3.42, 18),
(34, '18 0301 0054', 3, 3.19, 19),
(35, '18 0301 0054', 4, 3.01, 24),
(36, '18 0301 0054', 5, 2.81, 19),
(37, '18 0301 0054', 6, 2.66, 23),
(38, '18 0301 0054', 7, 2.37, 20),
(39, '18 0301 0054', 8, 2.18, 19),
(40, '2103010031\nSapoetra,', 1, 3.80, 22),
(41, '2103010031\nSapoetra,', 2, 3.39, 23),
(42, '2103010031\nSapoetra,', 3, 3.31, 18),
(43, '2103010031\nSapoetra,', 4, 2.85, 19),
(44, '2103010031\nSapoetra,', 5, 2.48, 20),
(45, '2103010031\nSapoetra,', 6, 2.29, 22),
(46, '2103010031\nSapoetra,', 7, 1.88, 22),
(47, '2103010031\nSapoetra,', 8, 1.74, 20),
(48, '2003010004', 1, 3.80, 18),
(49, '2003010004', 2, 3.60, 20),
(50, '2003010004', 3, 3.18, 19),
(51, '2003010004', 4, 3.06, 22),
(52, '2003010004', 5, 2.82, 19),
(53, '2003010004', 6, 2.74, 20),
(54, '2003010004', 7, 2.39, 18),
(55, '2003010004', 8, 2.01, 24),
(56, '2203010004', 1, 3.80, 22),
(57, '2203010004', 2, 3.52, 21),
(58, '2203010004', 3, 3.23, 21),
(59, '2203010004', 4, 2.77, 18),
(60, '2203010004', 5, 2.61, 23),
(61, '2203010004', 6, 2.19, 19),
(62, '2203010004', 7, 1.91, 22),
(63, '2203010004', 8, 1.55, 24),
(64, '2103010030', 1, 3.80, 24),
(65, '2103010030', 2, 3.75, 20),
(66, '2103010030', 3, 3.39, 19),
(67, '2103010030', 4, 3.06, 23),
(68, '2103010030', 5, 2.85, 21),
(69, '2103010030', 6, 2.42, 23),
(70, '2103010030', 7, 2.02, 24),
(71, '2103010030', 8, 1.55, 19),
(72, '1903010048', 1, 3.80, 22),
(73, '1903010048', 2, 3.79, 22),
(74, '1903010048', 3, 3.37, 22),
(75, '1903010048', 4, 2.94, 21),
(76, '1903010048', 5, 2.89, 22),
(77, '1903010048', 6, 2.79, 18),
(78, '1903010048', 7, 2.76, 21),
(79, '1903010048', 8, 2.38, 22),
(80, '1903010035', 1, 3.80, 18),
(81, '1903010035', 2, 3.66, 20),
(82, '1903010035', 3, 3.23, 23),
(83, '1903010035', 4, 3.19, 19),
(84, '1903010035', 5, 3.08, 24),
(85, '1903010035', 6, 2.90, 22),
(86, '1903010035', 7, 2.53, 20),
(87, '1903010035', 8, 2.42, 23),
(88, '2203010009', 1, 3.80, 20),
(89, '2203010009', 2, 3.55, 24),
(90, '2203010009', 3, 3.43, 24),
(91, '2203010009', 4, 3.30, 22),
(92, '2203010009', 5, 2.81, 22),
(93, '2203010009', 6, 2.75, 22),
(94, '2203010009', 7, 2.53, 22),
(95, '2203010009', 8, 2.04, 18),
(96, '18 0301 0066', 1, 3.80, 24),
(97, '18 0301 0066', 2, 3.44, 23),
(98, '18 0301 0066', 3, 3.06, 18),
(99, '18 0301 0066', 4, 2.78, 20),
(100, '18 0301 0066', 5, 2.66, 24),
(101, '18 0301 0066', 6, 2.44, 18),
(102, '18 0301 0066', 7, 2.04, 22),
(103, '18 0301 0066', 8, 1.86, 20),
(104, '2103010004', 1, 3.80, 24),
(105, '2103010004', 2, 3.80, 21),
(106, '2103010004', 3, 3.74, 22),
(107, '2103010004', 4, 3.71, 21),
(108, '2103010004', 5, 3.52, 21),
(109, '2103010004', 6, 3.26, 19),
(110, '2103010004', 7, 3.15, 19),
(111, '2103010004', 8, 2.95, 24),
(112, '2003010013', 1, 3.80, 23),
(113, '2003010013', 2, 3.33, 18),
(114, '2003010013', 3, 2.85, 18),
(115, '2003010013', 4, 2.40, 19),
(116, '2003010013', 5, 1.99, 18),
(117, '2003010013', 6, 1.91, 21),
(118, '2003010013', 7, 1.80, 23),
(119, '2003010013', 8, 1.80, 24),
(120, '1903010041', 1, 3.80, 20),
(121, '1903010041', 2, 3.71, 18),
(122, '1903010041', 3, 3.37, 19),
(123, '1903010041', 4, 3.01, 23),
(124, '1903010041', 5, 2.81, 24),
(125, '1903010041', 6, 2.48, 18),
(126, '1903010041', 7, 2.40, 19),
(127, '1903010041', 8, 2.00, 22),
(128, '2103010029', 1, 3.80, 19),
(129, '2103010029', 2, 3.64, 22),
(130, '2103010029', 3, 3.50, 20),
(131, '2103010029', 4, 3.14, 20),
(132, '2103010029', 5, 2.73, 21),
(133, '2103010029', 6, 2.40, 21),
(134, '2103010029', 7, 2.15, 19),
(135, '2103010029', 8, 1.80, 22),
(136, '2203010011', 1, 3.80, 23),
(137, '2203010011', 2, 3.67, 21),
(138, '2203010011', 3, 3.47, 22),
(139, '2203010011', 4, 3.45, 20),
(140, '2203010011', 5, 3.21, 18),
(141, '2203010011', 6, 3.05, 21),
(142, '2203010011', 7, 2.81, 21),
(143, '2203010011', 8, 2.52, 23),
(144, '1903010093', 1, 3.80, 18),
(145, '1903010093', 2, 3.73, 22),
(146, '1903010093', 3, 3.62, 21),
(147, '1903010093', 4, 3.51, 18),
(148, '1903010093', 5, 3.30, 23),
(149, '1903010093', 6, 3.17, 21),
(150, '1903010093', 7, 2.76, 23),
(151, '1903010093', 8, 2.62, 20),
(152, '1903010040', 1, 3.80, 23),
(153, '1903010040', 2, 3.75, 20),
(154, '1903010040', 3, 3.49, 20),
(155, '1903010040', 4, 3.24, 22),
(156, '1903010040', 5, 3.02, 19),
(157, '1903010040', 6, 2.97, 23),
(158, '1903010040', 7, 2.94, 20),
(159, '1903010040', 8, 2.53, 18),
(160, '2103010022', 1, 3.40, 21),
(161, '2103010022', 2, 3.00, 21),
(162, '2103010022', 3, 3.25, 21),
(163, '2103010022', 4, 3.00, 21),
(164, '2103010022', 5, 2.75, 19),
(165, '2103010022', 6, 2.99, 21),
(166, '18 0301 0015', 1, 2.00, 21),
(167, '18 0301 0015', 2, 3.45, 21),
(168, '18 0301 0015', 3, 2.75, 21),
(169, '18 0301 0015', 4, 2.80, 21),
(170, '18 0301 0015', 5, 3.36, 21),
(171, '18 0301 0015', 6, 3.40, 21);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username_admin` (`username_admin`);

--
-- Indexes for table `catatan_dosen`
--
ALTER TABLE `catatan_dosen`
  ADD PRIMARY KEY (`id_catatan`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- Indexes for table `dokumen`
--
ALTER TABLE `dokumen`
  ADD PRIMARY KEY (`id_dokumen`),
  ADD KEY `nim_mahasiswa` (`nim_mahasiswa`),
  ADD KEY `id_dosen` (`id_dosen`);

--
-- Indexes for table `dokumen_prestasi`
--
ALTER TABLE `dokumen_prestasi`
  ADD PRIMARY KEY (`id_prestasi`),
  ADD KEY `nim` (`nim`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id_dosen`),
  ADD UNIQUE KEY `nidn_dosen` (`nidn_dosen`);

--
-- Indexes for table `evaluasi_dosen`
--
ALTER TABLE `evaluasi_dosen`
  ADD PRIMARY KEY (`id_evaluasi_dosen`),
  ADD UNIQUE KEY `nim_mahasiswa` (`nim_mahasiswa`,`id_dosen`,`periode_evaluasi`),
  ADD KEY `id_dosen` (`id_dosen`);

--
-- Indexes for table `evaluasi_softskill`
--
ALTER TABLE `evaluasi_softskill`
  ADD PRIMARY KEY (`id_evaluasi`),
  ADD KEY `nim_mahasiswa` (`nim_mahasiswa`),
  ADD KEY `id_dosen` (`id_dosen`);

--
-- Indexes for table `konsultasi_judul`
--
ALTER TABLE `konsultasi_judul`
  ADD PRIMARY KEY (`id_konsultasi`),
  ADD KEY `nim` (`nim`),
  ADD KEY `id_dosen` (`id_dosen`);

--
-- Indexes for table `krs`
--
ALTER TABLE `krs`
  ADD PRIMARY KEY (`id_krs`),
  ADD KEY `nim_mahasiswa` (`nim_mahasiswa`),
  ADD KEY `id_mk` (`id_mk`);

--
-- Indexes for table `logbook`
--
ALTER TABLE `logbook`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `nim_mahasiswa` (`nim_mahasiswa`),
  ADD KEY `id_dosen` (`id_dosen`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`nim`),
  ADD KEY `id_prodi` (`id_prodi`),
  ADD KEY `id_dosen_pa` (`id_dosen_pa`);

--
-- Indexes for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`id_mk`),
  ADD UNIQUE KEY `nama_mk` (`nama_mk`);

--
-- Indexes for table `nilai_bermasalah`
--
ALTER TABLE `nilai_bermasalah`
  ADD PRIMARY KEY (`id_nilai`);

--
-- Indexes for table `nilai_mahasiswa`
--
ALTER TABLE `nilai_mahasiswa`
  ADD PRIMARY KEY (`id_nilai`),
  ADD KEY `nim_mahasiswa` (`nim_mahasiswa`);

--
-- Indexes for table `pencapaian`
--
ALTER TABLE `pencapaian`
  ADD PRIMARY KEY (`id_pencapaian`),
  ADD UNIQUE KEY `mahasiswa_pencapaian` (`nim_mahasiswa`,`nama_pencapaian`);

--
-- Indexes for table `program_studi`
--
ALTER TABLE `program_studi`
  ADD PRIMARY KEY (`id_prodi`),
  ADD UNIQUE KEY `nama_prodi` (`nama_prodi`);

--
-- Indexes for table `riwayat_akademik`
--
ALTER TABLE `riwayat_akademik`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD UNIQUE KEY `nim_mahasiswa` (`nim_mahasiswa`,`semester`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `catatan_dosen`
--
ALTER TABLE `catatan_dosen`
  MODIFY `id_catatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `dokumen`
--
ALTER TABLE `dokumen`
  MODIFY `id_dokumen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `dokumen_prestasi`
--
ALTER TABLE `dokumen_prestasi`
  MODIFY `id_prestasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id_dosen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `evaluasi_dosen`
--
ALTER TABLE `evaluasi_dosen`
  MODIFY `id_evaluasi_dosen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `evaluasi_softskill`
--
ALTER TABLE `evaluasi_softskill`
  MODIFY `id_evaluasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `konsultasi_judul`
--
ALTER TABLE `konsultasi_judul`
  MODIFY `id_konsultasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `krs`
--
ALTER TABLE `krs`
  MODIFY `id_krs` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logbook`
--
ALTER TABLE `logbook`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  MODIFY `id_mk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `nilai_bermasalah`
--
ALTER TABLE `nilai_bermasalah`
  MODIFY `id_nilai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `nilai_mahasiswa`
--
ALTER TABLE `nilai_mahasiswa`
  MODIFY `id_nilai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pencapaian`
--
ALTER TABLE `pencapaian`
  MODIFY `id_pencapaian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `program_studi`
--
ALTER TABLE `program_studi`
  MODIFY `id_prodi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `riwayat_akademik`
--
ALTER TABLE `riwayat_akademik`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=196;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dokumen_prestasi`
--
ALTER TABLE `dokumen_prestasi`
  ADD CONSTRAINT `dokumen_prestasi_ibfk_1` FOREIGN KEY (`nim`) REFERENCES `mahasiswa` (`nim`) ON DELETE CASCADE;

--
-- Constraints for table `evaluasi_dosen`
--
ALTER TABLE `evaluasi_dosen`
  ADD CONSTRAINT `evaluasi_dosen_ibfk_1` FOREIGN KEY (`nim_mahasiswa`) REFERENCES `mahasiswa` (`nim`),
  ADD CONSTRAINT `evaluasi_dosen_ibfk_2` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id_dosen`);

--
-- Constraints for table `evaluasi_softskill`
--
ALTER TABLE `evaluasi_softskill`
  ADD CONSTRAINT `evaluasi_softskill_ibfk_1` FOREIGN KEY (`nim_mahasiswa`) REFERENCES `mahasiswa` (`nim`),
  ADD CONSTRAINT `evaluasi_softskill_ibfk_2` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id_dosen`);

--
-- Constraints for table `konsultasi_judul`
--
ALTER TABLE `konsultasi_judul`
  ADD CONSTRAINT `konsultasi_judul_ibfk_1` FOREIGN KEY (`nim`) REFERENCES `mahasiswa` (`nim`) ON DELETE CASCADE,
  ADD CONSTRAINT `konsultasi_judul_ibfk_2` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id_dosen`) ON DELETE SET NULL;

--
-- Constraints for table `logbook`
--
ALTER TABLE `logbook`
  ADD CONSTRAINT `logbook_ibfk_1` FOREIGN KEY (`nim_mahasiswa`) REFERENCES `mahasiswa` (`nim`),
  ADD CONSTRAINT `logbook_ibfk_2` FOREIGN KEY (`id_dosen`) REFERENCES `dosen` (`id_dosen`);

--
-- Constraints for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`id_prodi`) REFERENCES `program_studi` (`id_prodi`),
  ADD CONSTRAINT `mahasiswa_ibfk_2` FOREIGN KEY (`id_dosen_pa`) REFERENCES `dosen` (`id_dosen`);

--
-- Constraints for table `riwayat_akademik`
--
ALTER TABLE `riwayat_akademik`
  ADD CONSTRAINT `riwayat_akademik_ibfk_1` FOREIGN KEY (`nim_mahasiswa`) REFERENCES `mahasiswa` (`nim`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
