<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Keamanan: Pastikan yang mengakses adalah dosen yang sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen') {
    header("Location: login.php");
    exit();
}

// Validasi parameter GET
if (!isset($_GET['nim']) || !isset($_GET['action'])) {
    header("Location: dashboard_dosen.php");
    exit();
}

$nim_mahasiswa = trim($_GET['nim']);
$action = trim($_GET['action']);
$id_dosen_login = $_SESSION['user_id'];

// Validasi input
if (empty($nim_mahasiswa) || empty($action)) {
    header("Location: dashboard_dosen.php");
    exit();
}

// ==========================================================
// === KONEKSI DATABASE (Otomatis XAMPP atau InfinityFree) ===
// ==========================================================
require_once 'config.php';

// $conn sudah siap dari config.php

// Verifikasi kepemilikan mahasiswa (KEAMANAN)
$stmt_check = $conn->prepare("
    SELECT nim 
    FROM mahasiswa 
    WHERE nim = ? AND id_dosen_pa = ?
");

if (!$stmt_check) {
    error_log("Prepare check gagal: " . $conn->error);
    header("Location: dashboard_dosen.php");
    exit();
}

$stmt_check->bind_param("si", $nim_mahasiswa, $id_dosen_login);
$stmt_check->execute();

if ($stmt_check->get_result()->num_rows === 0) {
    $stmt_check->close();
    $conn->close();
    header("Location: dashboard_dosen.php");
    exit();
}
$stmt_check->close();

// Variabel untuk notifikasi logbook
$topik_notifikasi = '';
$isi_notifikasi = '';
$stmt = null;

// Validasi action dan siapkan pesan notifikasi
switch ($action) {
    case 'setujui_krs':
        $stmt = $conn->prepare("UPDATE mahasiswa SET krs_disetujui = 1, krs_notif_dilihat = 0 WHERE nim = ?");
        $topik_notifikasi = "Persetujuan KRS";
        $isi_notifikasi = "KRS Anda telah disetujui oleh Dosen PA. Anda dapat melanjutkan ke tahap berikutnya.";
        break;
        
    case 'tolak_krs':
        $stmt = $conn->prepare("UPDATE mahasiswa SET krs_disetujui = 0 WHERE nim = ?");
        $topik_notifikasi = "Persetujuan KRS Dibatalkan";
        $isi_notifikasi = "Persetujuan KRS Anda dibatalkan oleh Dosen PA. Silakan hubungi dosen untuk diskusi lebih lanjut.";
        break;
        
    case 'set_status_aktif':
        $stmt = $conn->prepare("UPDATE mahasiswa SET status = 'Aktif' WHERE nim = ?");
        $topik_notifikasi = "Pembaruan Status Akademik";
        $isi_notifikasi = "Status akademik Anda telah diubah menjadi AKTIF oleh Dosen PA.";
        break;
        
    case 'set_status_nonaktif':
        $stmt = $conn->prepare("UPDATE mahasiswa SET status = 'Non-Aktif' WHERE nim = ?");
        $topik_notifikasi = "Pembaruan Status Akademik";
        $isi_notifikasi = "Status akademik Anda telah diubah menjadi NON-AKTIF oleh Dosen PA. Mohon segera hubungi dosen Anda.";
        break;
        
    default:
        $conn->close();
        header("Location: dashboard_dosen.php");
        exit();
}

// Jalankan query update status
if (isset($stmt) && $stmt !== null) {
    if (!$stmt) {
        error_log("Prepare statement gagal: " . $conn->error);
        $conn->close();
        header("Location: dashboard_dosen.php");
        exit();
    }
    
    $stmt->bind_param("s", $nim_mahasiswa);
    $is_success = $stmt->execute();
    
    if (!$is_success) {
        error_log("Execute update gagal: " . $stmt->error);
        $stmt->close();
        $conn->close();
        header("Location: dashboard_dosen.php");
        exit();
    }
    
    $stmt->close();

    // ### BAGIAN UTAMA: Buat logbook jika update berhasil ###
    if ($is_success && !empty($topik_notifikasi) && !empty($isi_notifikasi)) {
        $stmt_log = $conn->prepare("
            INSERT INTO logbook 
            (nim_mahasiswa, id_dosen, pengisi, tanggal_bimbingan, topik_bimbingan, isi_bimbingan, status_baca) 
            VALUES (?, ?, 'Dosen', CURDATE(), ?, ?, 'Belum Dibaca')
        ");
        
        if (!$stmt_log) {
            error_log("Prepare logbook gagal: " . $conn->error);
        } else {
            $stmt_log->bind_param("siss", $nim_mahasiswa, $id_dosen_login, $topik_notifikasi, $isi_notifikasi);
            
            if (!$stmt_log->execute()) {
                error_log("Execute logbook gagal: " . $stmt_log->error);
            }
            $stmt_log->close();
        }
    }
} else {
    $conn->close();
    header("Location: dashboard_dosen.php");
    exit();
}

$conn->close();

// Kembalikan ke halaman dashboard
header("Location: dashboard_dosen.php");
exit();
?>
