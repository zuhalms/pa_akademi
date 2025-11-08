<?php
session_start();

// Keamanan Tingkat 1: Pastikan yang mengakses adalah dosen yang sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen') {
    header("Location: login.php");
    exit();
}

// Keamanan Tingkat 2: Pastikan ada ID dokumen dan NIM yang dikirim
if (!isset($_GET['id']) || !isset($_GET['nim']) || empty($_GET['id']) || empty($_GET['nim'])) {
    header("Location: dashboard_dosen.php");
    exit();
}

$id_dokumen = $_GET['id'];
$nim_mahasiswa = $_GET['nim']; // Untuk redirect kembali
$id_dosen_login = $_SESSION['user_id'];

// Koneksi ke database
$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
  //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

// Langkah 1: Ambil path file dari database SEBELUM menghapus record
$stmt_get = $conn->prepare("SELECT path_file FROM dokumen WHERE id_dokumen = ? AND id_dosen = ?");
$stmt_get->bind_param("ii", $id_dokumen, $id_dosen_login);
$stmt_get->execute();
$result = $stmt_get->get_result();
if ($file_data = $result->fetch_assoc()) {
    $file_path = $file_data['path_file'];

    // Langkah 2: Hapus file fisik dari server jika ada
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Langkah 3: Hapus record dari database
    $stmt_delete = $conn->prepare("DELETE FROM dokumen WHERE id_dokumen = ? AND id_dosen = ?");
    $stmt_delete->bind_param("ii", $id_dokumen, $id_dosen_login);
    $stmt_delete->execute();
    $stmt_delete->close();
}
$stmt_get->close();
$conn->close();

// Setelah selesai, kembalikan dosen ke halaman detail mahasiswa yang sama
header("Location: detail_mahasiswa.php?nim=" . urlencode($nim_mahasiswa));
exit();
?>