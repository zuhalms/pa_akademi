<?php
session_start();

// Keamanan dasar: Pastikan Dosen yang login dan ini adalah POST request
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen' || $_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['nim_mahasiswa'])) {
    header("Location: dashboard_dosen.php");
    exit();
}

$nim_mahasiswa = trim($_POST['nim_mahasiswa']);
$id_dosen_login = $_SESSION['user_id'];

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

// Verifikasi apakah dosen ini adalah PA mahasiswa tersebut (KEAMANAN)
$stmt_check = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM mahasiswa 
    WHERE nim = ? AND id_dosen_pa = ?
");

if (!$stmt_check) {
    error_log("Prepare check gagal: " . $conn->error);
    header("Location: detail_mahasiswa.php?nim=" . urlencode($nim_mahasiswa) . "&update=error_db");
    exit();
}

$stmt_check->bind_param("si", $nim_mahasiswa, $id_dosen_login);
$stmt_check->execute();
$result_check = $stmt_check->get_result()->fetch_assoc();
$count = $result_check['total'];
$stmt_check->close();

if ($count > 0) {
    // Dosen terverifikasi - lanjutkan delete
    $stmt_delete = $conn->prepare("DELETE FROM nilai_bermasalah WHERE nim_mahasiswa = ?");
    
    if (!$stmt_delete) {
        error_log("Prepare delete gagal: " . $conn->error);
        header("Location: detail_mahasiswa.php?nim=" . urlencode($nim_mahasiswa) . "&update=error_db");
        exit();
    }

    $stmt_delete->bind_param("s", $nim_mahasiswa);
    
    if ($stmt_delete->execute()) {
        // Berhasil dihapus
        $stmt_delete->close();
        $conn->close();
        header("Location: detail_mahasiswa.php?nim=" . urlencode($nim_mahasiswa) . "&update=nilai_ok");
        exit();
    } else {
        // Gagal execute
        error_log("Eksekusi delete gagal: " . $stmt_delete->error);
        $stmt_delete->close();
        $conn->close();
        header("Location: detail_mahasiswa.php?nim=" . urlencode($nim_mahasiswa) . "&update=error_delete");
        exit();
    }
} else {
    // Dosen mencoba akses data mahasiswa orang lain (KEAMANAN)
    $conn->close();
    header("Location: dashboard_dosen.php");
    exit();
}
?>
