<?php
session_start();

// Security: Ensure a student is logged in and the form was submitted
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'mahasiswa' || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: profil.php?status=gagal");
    exit();
}

// Database connection
$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
    //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

$nim_mahasiswa = $_POST['nim'];

// Security: Ensure the logged-in student can only change their own photo
if ($nim_mahasiswa != $_SESSION['user_id']) {
    header("Location: profil.php?status=unauthorized");
    exit();
}

// Process the file upload
if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
    $target_dir = "assets/uploads/"; // Ensure this folder exists and is writable

    // Create a unique filename to prevent overwriting
    $imageFileType = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));
    $new_filename = 'mhs_' . $nim_mahasiswa . '_' . time() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;

    // Validate if the file is a real image
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if($check === false) {
        header("Location: profil.php?status=not_image");
        exit();
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        // Delete the old photo file if it exists
        $stmt = $conn->prepare("SELECT foto_mahasiswa FROM mahasiswa WHERE nim = ?");
        $stmt->bind_param("s", $nim_mahasiswa);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($old_photo = $result->fetch_assoc()) {
            if (!empty($old_photo['foto_mahasiswa']) && file_exists($target_dir . $old_photo['foto_mahasiswa'])) {
                unlink($target_dir . $old_photo['foto_mahasiswa']);
            }
        }

        // Update the database with the new photo filename
        $stmt = $conn->prepare("UPDATE mahasiswa SET foto_mahasiswa = ? WHERE nim = ?");
        $stmt->bind_param("ss", $new_filename, $nim_mahasiswa);
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