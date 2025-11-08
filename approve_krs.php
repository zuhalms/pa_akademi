<?php
session_start();

// Keamanan: Pastikan yang mengakses adalah dosen yang sudah login dan ada NIM yang dikirim
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen' || !isset($_GET['nim'])) {
    header("Location: login.php");
    exit();
}

$nim_mahasiswa = $_GET['nim'];
$id_dosen_login = $_SESSION['user_id'];

// Koneksi ke database
$host = 'localhost'; 
$db_user = 'root'; 
$db_pass = ''; 
$db_name = 'db_pa_akademi';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Siapkan query untuk update status krs_disetujui menjadi TRUE (atau 1)
// Tambahkan klausa 'id_dosen_pa' untuk keamanan, agar dosen hanya bisa menyetujui mahasiswanya sendiri
$stmt = $conn->prepare("UPDATE mahasiswa SET krs_disetujui = 1 WHERE nim = ? AND id_dosen_pa = ?");
$stmt->bind_param("si", $nim_mahasiswa, $id_dosen_login);

// Jalankan query
$stmt->execute();

// Tutup koneksi
$stmt->close();
$conn->close();

// Setelah selesai, kembalikan dosen ke halaman dashboard
header("Location: dashboard_dosen.php");
exit();
?>