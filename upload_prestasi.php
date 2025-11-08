<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'mahasiswa') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = $_SESSION['user_id'];
    $nama_prestasi = $_POST['nama_prestasi'];
    $jenis_prestasi = $_POST['jenis_prestasi'];
    
    // Validasi dan upload file
    if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] == 0) {
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        $filename = $_FILES['file_dokumen']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        $filesize = $_FILES['file_dokumen']['size'];
        
        // Validasi tipe dan ukuran file
        if (!in_array(strtolower($filetype), $allowed)) {
            $_SESSION['error'] = 'Tipe file tidak diizinkan. Hanya PDF, JPG, PNG yang diperbolehkan.';
            header("Location: edit_profil.php");
            exit();
        }
        
        if ($filesize > 5 * 1024 * 1024) { // 5MB
            $_SESSION['error'] = 'Ukuran file terlalu besar. Maksimal 5MB.';
            header("Location: edit_profil.php");
            exit();
        }
        
        // Generate nama file unik
        $new_filename = 'prestasi_' . $nim . '_' . time() . '.' . $filetype;
        $upload_dir = 'assets/uploads/prestasi/';
        
        // Buat folder jika belum ada
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $upload_path = $upload_dir . $new_filename;
        
        // Upload file
        if (move_uploaded_file($_FILES['file_dokumen']['tmp_name'], $upload_path)) {
            // Simpan ke database
            $stmt = $conn->prepare("INSERT INTO dokumen_prestasi (nim, nama_prestasi, jenis_prestasi, file_dokumen, tanggal_upload) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $nim, $nama_prestasi, $jenis_prestasi, $new_filename);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Dokumen prestasi berhasil diupload!';
            } else {
                $_SESSION['error'] = 'Gagal menyimpan data ke database.';
                unlink($upload_path); // Hapus file jika gagal simpan ke DB
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = 'Gagal mengupload file.';
        }
    } else {
        $_SESSION['error'] = 'File tidak ditemukan atau terjadi kesalahan.';
    }
}

$conn->close();
header("Location: edit_profil.php");
exit();
?>