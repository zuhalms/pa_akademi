<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_konsultasi = $_POST['id_konsultasi'];
    $nim = $_POST['nim'];
    $catatan_dosen = trim($_POST['catatan_dosen']);
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE konsultasi_judul SET status = ?, catatan_dosen = ?, tanggal_respon = NOW() WHERE id_konsultasi = ?");
    $stmt->bind_param("ssi", $status, $catatan_dosen, $id_konsultasi);
    
    if ($stmt->execute()) {
        $_SESSION['pesan_sukses'] = 'Tanggapan berhasil dikirim ke mahasiswa!';
    } else {
        $_SESSION['pesan_error'] = 'Gagal mengirim tanggapan: ' . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
header("Location: detail_mahasiswa.php?nim=" . urlencode($nim));
exit();
?>