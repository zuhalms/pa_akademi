<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'mahasiswa') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_prestasi = $_GET['id'];
    $nim = $_SESSION['user_id'];
    
    // Ambil data file
    $stmt = $conn->prepare("SELECT file_dokumen FROM dokumen_prestasi WHERE id_prestasi = ? AND nim = ?");
    $stmt->bind_param("is", $id_prestasi, $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $file_path = 'assets/uploads/prestasi/' . $data['file_dokumen'];
        
        // Hapus file
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Hapus dari database
        $stmt_delete = $conn->prepare("DELETE FROM dokumen_prestasi WHERE id_prestasi = ? AND nim = ?");
        $stmt_delete->bind_param("is", $id_prestasi, $nim);
        
        if ($stmt_delete->execute()) {
            $_SESSION['success'] = 'Dokumen prestasi berhasil dihapus!';
        } else {
            $_SESSION['error'] = 'Gagal menghapus dokumen prestasi.';
        }
        $stmt_delete->close();
    } else {
        $_SESSION['error'] = 'Dokumen tidak ditemukan.';
    }
    $stmt->close();
}

$conn->close();
header("Location: edit_profil.php");
exit();
?>