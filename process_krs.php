<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen') {
    header("Location: login.php");
    exit();
}
if (!isset($_GET['nim']) || empty($_GET['nim'])) {
    header("Location: dashboard_dosen.php");
    exit();
}

$nim_mahasiswa = $_GET['nim'];
$id_dosen_login = $_SESSION['user_id'];

 $host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
    //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

// UPDATE: Setujui KRS DAN setel notifikasi ke 'belum dilihat' (FALSE/0)
$stmt = $conn->prepare("UPDATE mahasiswa SET krs_disetujui = TRUE, krs_notif_dilihat = FALSE WHERE nim = ? AND id_dosen_pa = ?");
$stmt->bind_param("si", $nim_mahasiswa, $id_dosen_login);
$stmt->execute();
$stmt->close();
$conn->close();

header("Location: dashboard_dosen.php");
exit();
?>