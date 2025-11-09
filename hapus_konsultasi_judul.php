<?php
session_start();
require_once 'config.php';

// Keamanan: Hanya dosen yang bisa menghapus
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['nim'])) {
    $id_konsultasi = intval($_GET['id']);
    $nim = $_GET['nim'];
    $id_dosen = $_SESSION['user_id'];
    
    // Verifikasi bahwa dosen ini adalah PA dari mahasiswa tersebut
    $stmt_verify = $conn->prepare("SELECT COUNT(*) as valid FROM konsultasi_judul kj JOIN mahasiswa m ON kj.nim = m.nim WHERE kj.id_konsultasi = ? AND m.id_dosen_pa = ?");
    $stmt_verify->bind_param("ii", $id_konsultasi, $id_dosen);
    $stmt_verify->execute();
    $result = $stmt_verify->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['valid'] > 0) {
        // Hapus konsultasi judul
        $stmt_delete = $conn->prepare("DELETE FROM konsultasi_judul WHERE id_konsultasi = ?");
        $stmt_delete->bind_param("i", $id_konsultasi);
        
        if ($stmt_delete->execute()) {
            $_SESSION['pesan_sukses'] = 'Konsultasi judul berhasil dihapus!';
        } else {
            $_SESSION['pesan_error'] = 'Gagal menghapus konsultasi judul: ' . $stmt_delete->error;
        }
        $stmt_delete->close();
    } else {
        $_SESSION['pesan_error'] = 'Anda tidak memiliki akses untuk menghapus konsultasi ini!';
    }
    $stmt_verify->close();
    
    $conn->close();
    header("Location: detail_mahasiswa.php?nim=" . urlencode($nim));
    exit();
} else {
    $_SESSION['pesan_error'] = 'Parameter tidak lengkap!';
    header("Location: dashboard_dosen.php");
    exit();
}
?>