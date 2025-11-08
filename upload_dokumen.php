<?php
session_start();

// Keamanan: Pastikan mahasiswa yang login dan form telah disubmit
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'mahasiswa' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION['upload_message'] = 'Akses tidak sah.';
    $_SESSION['upload_status'] = 'danger';
    header("Location: dashboard_mahasiswa.php");
    exit();
}

// Koneksi ke database LOKAL (XAMPP)
$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
    //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$nim_mahasiswa = $_SESSION['user_id'];
$judul_dokumen = $_POST['judul_dokumen'];

// Ambil id_dosen_pa dari mahasiswa yang sedang login
$stmt_dosen = $conn->prepare("SELECT id_dosen_pa FROM mahasiswa WHERE nim = ?");
$stmt_dosen->bind_param("s", $nim_mahasiswa);
$stmt_dosen->execute();
$id_dosen_pa = $stmt_dosen->get_result()->fetch_assoc()['id_dosen_pa'];
$stmt_dosen->close();

if (!$id_dosen_pa) {
    $_SESSION['upload_message'] = 'Gagal: Dosen Pembimbing belum ditentukan.';
    $_SESSION['upload_status'] = 'danger';
    header("Location: dashboard_mahasiswa.php");
    exit();
}

// Proses unggah file
if (isset($_FILES['file_dokumen']) && $_FILES['file_dokumen']['error'] == UPLOAD_ERR_OK) {

    // Tentukan folder tujuan. Folder ini akan dibuat otomatis jika belum ada.
    $target_dir = "dokumen/"; 
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $ukuran_file = $_FILES["file_dokumen"]["size"];
    $file_extension = strtolower(pathinfo($_FILES["file_dokumen"]["name"], PATHINFO_EXTENSION));

    // Validasi tipe file
    $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'png', 'jpeg'];
    if (!in_array($file_extension, $allowed_types)) {
        $_SESSION['upload_message'] = 'Gagal: Tipe file tidak diizinkan.';
        $_SESSION['upload_status'] = 'danger';
        header("Location: dashboard_mahasiswa.php");
        exit();
    }

    // Validasi ukuran file (5MB)
    if ($ukuran_file > 5 * 1024 * 1024) {
        $_SESSION['upload_message'] = 'Gagal: Ukuran file terlalu besar (Maks 5MB).';
        $_SESSION['upload_status'] = 'danger';
        header("Location: dashboard_mahasiswa.php");
        exit();
    }

    // Buat nama file unik untuk menghindari penimpaan file
    $nama_file_unik = $nim_mahasiswa . '_' . time() . '.' . $file_extension;
    $target_file_path = $target_dir . $nama_file_unik;

    if (move_uploaded_file($_FILES["file_dokumen"]["tmp_name"], $target_file_path)) {

        // Simpan informasi file ke database, TERMASUK TIPE FILE
        $stmt_insert = $conn->prepare("INSERT INTO dokumen (nim_mahasiswa, id_dosen, judul_dokumen, nama_file, path_file, tipe_file, ukuran_file) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sissssi", $nim_mahasiswa, $id_dosen_pa, $judul_dokumen, $nama_file_unik, $target_file_path, $file_extension, $ukuran_file);

        if ($stmt_insert->execute()) {
            $_SESSION['upload_message'] = 'File berhasil diunggah.';
            $_SESSION['upload_status'] = 'success';
        } else {
            $_SESSION['upload_message'] = 'Gagal menyimpan informasi file ke database.';
            $_SESSION['upload_status'] = 'danger';
        }
        $stmt_insert->close();

    } else {
        $_SESSION['upload_message'] = 'Terjadi kesalahan saat memindahkan file.';
        $_SESSION['upload_status'] = 'danger';
    }
} else {
    $_SESSION['upload_message'] = 'Tidak ada file yang dipilih atau terjadi kesalahan unggah.';
    $_SESSION['upload_status'] = 'danger';
}

$conn->close();
header("Location: dashboard_mahasiswa.php");
exit();
?>