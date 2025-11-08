<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen') {
    header("Location: login.php"); exit();
}

$nim = $_GET['nim'] ?? '';
$action = $_GET['action'] ?? '';
if (empty($nim) || empty($action)) {
    header("Location: dashboard_dosen.php"); exit();
}

$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
    //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

$dosen_id = $_SESSION['user_id'];

if ($action === 'deactivate') {
    $stmt = $conn->prepare("UPDATE mahasiswa SET status_semester = 'N' WHERE nim = ? AND id_dosen_pa = ?");
    $stmt->bind_param('si', $nim, $dosen_id);
    $stmt->execute();
    $stmt->close();
} elseif ($action === 'activate') {
    $stmt = $conn->prepare("UPDATE mahasiswa SET status_semester = 'A' WHERE nim = ? AND id_dosen_pa = ?");
    $stmt->bind_param('si', $nim, $dosen_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: dashboard_dosen.php");
exit();

?>
