<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Keamanan: Pastikan yang mengakses adalah dosen yang sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen' || !isset($_POST['nim_mahasiswa'])) {
    header("Location: login.php");
    exit();
}

$nim_mahasiswa = trim($_POST['nim_mahasiswa']);
$pencapaian_dikirim = $_POST['pencapaian'] ?? [];
$tanggal_dikirim = $_POST['tanggal_pencapaian'] ?? [];

// Validasi input
if (empty($nim_mahasiswa)) {
    header("Location: dashboard_dosen.php");
    exit();
}

// ==========================================================
// === KONEKSI DATABASE (Otomatis XAMPP atau InfinityFree) ===
// ==========================================================
require_once 'config.php';

// $conn sudah siap dari config.php

// Definisikan daftar pencapaian yang valid
$daftar_pencapaian_valid = [
    'Konsultasi Judul',
    'Seminar Proposal', 
    'Ujian Komperehensif', 
    'Seminar Hasil', 
    'Ujian Skripsi (Yudisium)', 
    'Publikasi Jurnal'
];

// Siapkan query dengan prepared statement
$stmt = $conn->prepare("
    INSERT INTO pencapaian (nim_mahasiswa, nama_pencapaian, status, tanggal_selesai) 
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        status = VALUES(status), 
        tanggal_selesai = VALUES(tanggal_selesai)
");

if (!$stmt) {
    error_log("Prepare statement gagal: " . $conn->error);
    header("Location: detail_mahasiswa.php?nim=" . urlencode($nim_mahasiswa) . "&update=error");
    exit();
}

$berhasil = true;

// Loop melalui semua pencapaian yang valid
foreach ($daftar_pencapaian_valid as $item) {
    // Cek apakah item ini dicentang di form yang dikirim
    if (isset($pencapaian_dikirim[$item]) && $pencapaian_dikirim[$item] == 'Selesai') {
        // Jika dicentang, statusnya 'Selesai'
        $status = 'Selesai';
        
        // Ambil tanggal dari input, jika kosong gunakan tanggal hari ini
        $tanggal = !empty($tanggal_dikirim[$item]) ? trim($tanggal_dikirim[$item]) : date('Y-m-d');
        
        // Validasi format tanggal
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            $tanggal = date('Y-m-d');
        }
    } else {
        // Jika tidak dicentang, statusnya 'Belum Selesai' dan tidak ada tanggal
        $status = 'Belum Selesai';
        $tanggal = null;
    }
    
    // Sanitasi data
    $item_clean = htmlspecialchars($item);
    $status_clean = htmlspecialchars($status);
    
    // Jalankan query untuk item ini
    $stmt->bind_param("ssss", $nim_mahasiswa, $item_clean, $status_clean, $tanggal);
    
    if (!$stmt->execute()) {
        error_log("Execute gagal untuk $item: " . $stmt->error);
        $berhasil = false;
        break;
    }
}

$stmt->close();
$conn->close();

// Setelah selesai, kembalikan dosen ke halaman detail dengan pesan status
if ($berhasil) {
    header("Location: detail_mahasiswa.php?nim=" . urlencode($nim_mahasiswa) . "&update=sukses");
} else {
    header("Location: detail_mahasiswa.php?nim=" . urlencode($nim_mahasiswa) . "&update=error");
}
exit();
?>
