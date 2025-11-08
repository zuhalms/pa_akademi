<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'mahasiswa') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = $_SESSION['user_id'];
    $judul_usulan = trim($_POST['judul_usulan']);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    
    // Ambil id_dosen_pa
    $stmt_dosen = $conn->prepare("SELECT id_dosen_pa FROM mahasiswa WHERE nim = ?");
    $stmt_dosen->bind_param("s", $nim);
    $stmt_dosen->execute();
    $result_dosen = $stmt_dosen->get_result();
    $id_dosen = null;
    if ($result_dosen->num_rows > 0) {
        $data = $result_dosen->fetch_assoc();
        $id_dosen = $data['id_dosen_pa'];
    }
    $stmt_dosen->close();
    
    // Insert konsultasi judul
    $stmt = $conn->prepare("INSERT INTO konsultasi_judul (nim, judul_usulan, deskripsi, id_dosen) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $nim, $judul_usulan, $deskripsi, $id_dosen);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Judul berhasil diajukan! Tunggu tanggapan dari Dosen PA.';
    } else {
        $_SESSION['error'] = 'Gagal mengajukan judul: ' . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
header("Location: konsultasi_judul.php");
exit();
?>