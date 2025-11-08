<?php
session_start();

// Keamanan: Pastikan ada pengguna yang login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

// Koneksi ke database
$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
  //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($user_role == 'dosen') {
    // Aksi untuk Dosen
    if (!isset($_GET['nim']) || empty($_GET['nim'])) {
        // Jika tidak ada NIM, tidak ada yang bisa dihapus
        header("Location: dashboard_dosen.php");
        exit();
    }
    $nim_mahasiswa = $_GET['nim'];
    
    // Keamanan: Dosen hanya bisa menghapus logbook mahasiswa bimbingannya
    $stmt = $conn->prepare("DELETE FROM logbook WHERE nim_mahasiswa = ? AND id_dosen = ?");
    $stmt->bind_param("si", $nim_mahasiswa, $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Kembalikan ke halaman detail
    header("Location: detail_mahasiswa.php?nim=" . urlencode($nim_mahasiswa));
    exit();

} elseif ($user_role == 'mahasiswa') {
    // Aksi untuk Mahasiswa
    
    // Mahasiswa hanya bisa menghapus logbook miliknya sendiri
    $stmt = $conn->prepare("DELETE FROM logbook WHERE nim_mahasiswa = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Kembalikan ke dashboard
    header("Location: dashboard_mahasiswa.php");
    exit();
}

$conn->close();

// Jika peran tidak dikenali, kembalikan ke login
header("Location: login.php");
exit();
?>