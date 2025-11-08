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

// Update status krs_disetujui menjadi FALSE (0)
$stmt = $conn->prepare("UPDATE mahasiswa SET krs_disetujui = 0 WHERE nim = ? AND id_dosen_pa = ?");
$stmt->bind_param("si", $nim, $id_dosen);
$stmt->execute();

$conn->close();

// Kembalikan ke dashboard
header("Location: dashboard_dosen.php");
exit();
?>