<?php
session_start();

// Keamanan: Pastikan yang mengakses adalah dosen dan form telah disubmit
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: profil.php?status=gagal");
    exit();
}

// Koneksi ke database
$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
    //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

$id_dosen = $_POST['id_dosen'];

// Pastikan dosen yang login hanya bisa mengubah fotonya sendiri
if ($id_dosen != $_SESSION['user_id']) {
    header("Location: profil.php?status=unauthorized");
    exit();
}

// Proses unggah file
if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
    $target_dir = "assets/uploads/"; // Pastikan folder ini ada dan bisa ditulis (writable)
    
    // Buat nama file yang unik untuk mencegah tumpang tindih
    $imageFileType = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
    $new_filename = 'dosen_' . $id_dosen . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;

    // Validasi file (opsional tapi sangat disarankan)
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if($check === false) {
        header("Location: profil.php?status=not_image");
        exit();
    }
    // (Anda bisa menambahkan validasi ukuran file di sini jika perlu)

    // Pindahkan file yang diunggah
    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        // Hapus foto lama jika ada
        $stmt = $conn->prepare("SELECT foto_dosen FROM dosen WHERE id_dosen = ?");
        $stmt->bind_param("i", $id_dosen);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($old_photo = $result->fetch_assoc()) {
            if (!empty($old_photo['foto_dosen']) && file_exists($target_dir . $old_photo['foto_dosen'])) {
                unlink($target_dir . $old_photo['foto_dosen']);
            }
        }

        // Update database dengan nama file foto baru
        $stmt = $conn->prepare("UPDATE dosen SET foto_dosen = ? WHERE id_dosen = ?");
        $stmt->bind_param("si", $new_filename, $id_dosen);
        $stmt->execute();
        
        header("Location: profil.php?status=sukses");
    } else {
        header("Location: profil.php?status=gagal_upload");
    }
} else {
    header("Location: profil.php?status=gagal_file");
}

$conn->close();
exit();
?>