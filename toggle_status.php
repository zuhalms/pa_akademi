<?php
session_start();

// Keamanan: Pastikan yang mengakses adalah dosen
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen' || !isset($_GET['nim'])) {
    header("Location: login.php");
    exit();
}

$nim = $_GET['nim'];
$id_dosen = $_SESSION['user_id'];

// Koneksi ke database
$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
    //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);

// 1. Ambil status saat ini
$stmt_select = $conn->prepare("SELECT status_semester FROM mahasiswa WHERE nim = ? AND id_dosen_pa = ?");
$stmt_select->bind_param("si", $nim, $id_dosen);
$stmt_select->execute();
$current_status = $stmt_select->get_result()->fetch_assoc()['status_semester'];

// 2. Tentukan status baru (kebalikannya)
$new_status = ($current_status == 'A') ? 'N' : 'A';

// 3. Update status ke database
$stmt_update = $conn->prepare("UPDATE mahasiswa SET status_semester = ? WHERE nim = ? AND id_dosen_pa = ?");
$stmt_update->bind_param("ssi", $new_status, $nim, $id_dosen);
$stmt_update->execute();

$conn->close();

// Kembalikan ke dashboard
header("Location: dashboard_dosen.php");
exit();
?>