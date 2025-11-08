<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if ($user_role == 'dosen') {
        // Update data dosen
        $nama_dosen = trim($_POST['nama_dosen']);
        $nip = trim($_POST['nip'] ?? '');
        $tempat_lahir = trim($_POST['tempat_lahir'] ?? '');
        $tgl_lahir = trim($_POST['tgl_lahir'] ?? '');
        $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
        $email_dosen = trim($_POST['email_dosen'] ?? '');
        $telp_dosen = trim($_POST['telp_dosen'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $pendidikan_terakhir = trim($_POST['pendidikan_terakhir'] ?? '');
        $bidang_keahlian = trim($_POST['bidang_keahlian'] ?? '');
        $jabatan_akademik = trim($_POST['jabatan_akademik'] ?? '');
        
        // Cek kolom yang ada di database
        $check_columns = $conn->query("SHOW COLUMNS FROM dosen");
        $existing_columns = [];
        
        if ($check_columns) {
            while ($row = $check_columns->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
        }
        
        // Build query berdasarkan kolom yang ada
        $update_fields = ["nama_dosen = ?"];
        $params = [$nama_dosen];
        $types = "s";
        
        if (in_array('nip', $existing_columns)) {
            $update_fields[] = "nip = ?";
            $params[] = $nip;
            $types .= "s";
        }
        
        if (in_array('tempat_lahir', $existing_columns)) {
            $update_fields[] = "tempat_lahir = ?";
            $params[] = $tempat_lahir;
            $types .= "s";
        }
        
        if (in_array('tgl_lahir', $existing_columns)) {
            $update_fields[] = "tgl_lahir = ?";
            $params[] = $tgl_lahir;
            $types .= "s";
        }
        
        if (in_array('jenis_kelamin', $existing_columns)) {
            $update_fields[] = "jenis_kelamin = ?";
            $params[] = $jenis_kelamin;
            $types .= "s";
        }
        
        if (in_array('email_dosen', $existing_columns)) {
            $update_fields[] = "email_dosen = ?";
            $params[] = $email_dosen;
            $types .= "s";
        }
        
        if (in_array('telp_dosen', $existing_columns)) {
            $update_fields[] = "telp_dosen = ?";
            $params[] = $telp_dosen;
            $types .= "s";
        }
        
        if (in_array('alamat', $existing_columns)) {
            $update_fields[] = "alamat = ?";
            $params[] = $alamat;
            $types .= "s";
        }
        
        if (in_array('pendidikan_terakhir', $existing_columns)) {
            $update_fields[] = "pendidikan_terakhir = ?";
            $params[] = $pendidikan_terakhir;
            $types .= "s";
        }
        
        if (in_array('bidang_keahlian', $existing_columns)) {
            $update_fields[] = "bidang_keahlian = ?";
            $params[] = $bidang_keahlian;
            $types .= "s";
        }
        
        if (in_array('jabatan_akademik', $existing_columns)) {
            $update_fields[] = "jabatan_akademik = ?";
            $params[] = $jabatan_akademik;
            $types .= "s";
        }
        
        // Tambahkan id_dosen di akhir
        $params[] = $user_id;
        $types .= "i";
        
        // Build dan execute query
        $sql = "UPDATE dosen SET " . implode(", ", $update_fields) . " WHERE id_dosen = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Profil berhasil diperbarui!';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui profil: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = 'Error prepare statement: ' . $conn->error;
        }
        
    } elseif ($user_role == 'mahasiswa') {
        // Update data mahasiswa
        $nama_mahasiswa = trim($_POST['nama_mahasiswa']);
        $tempat_lahir = trim($_POST['tempat_lahir'] ?? '');
        $tgl_lahir = trim($_POST['tgl_lahir'] ?? '');
        $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telp = trim($_POST['telp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $hobi = trim($_POST['hobi'] ?? '');
        
        // Cek kolom yang ada di database
        $check_columns = $conn->query("SHOW COLUMNS FROM mahasiswa");
        $existing_columns = [];
        
        if ($check_columns) {
            while ($row = $check_columns->fetch_assoc()) {
                $existing_columns[] = $row['Field'];
            }
        }
        
        // Build query berdasarkan kolom yang ada
        $update_fields = ["nama_mahasiswa = ?"];
        $params = [$nama_mahasiswa];
        $types = "s";
        
        if (in_array('tempat_lahir', $existing_columns)) {
            $update_fields[] = "tempat_lahir = ?";
            $params[] = $tempat_lahir;
            $types .= "s";
        }
        
        if (in_array('tgl_lahir', $existing_columns)) {
            $update_fields[] = "tgl_lahir = ?";
            $params[] = $tgl_lahir;
            $types .= "s";
        }
        
        if (in_array('jenis_kelamin', $existing_columns)) {
            $update_fields[] = "jenis_kelamin = ?";
            $params[] = $jenis_kelamin;
            $types .= "s";
        }
        
        if (in_array('email', $existing_columns)) {
            $update_fields[] = "email = ?";
            $params[] = $email;
            $types .= "s";
        }
        
        if (in_array('telp', $existing_columns)) {
            $update_fields[] = "telp = ?";
            $params[] = $telp;
            $types .= "s";
        }
        
        if (in_array('alamat', $existing_columns)) {
            $update_fields[] = "alamat = ?";
            $params[] = $alamat;
            $types .= "s";
        }
        
        if (in_array('hobi', $existing_columns)) {
            $update_fields[] = "hobi = ?";
            $params[] = $hobi;
            $types .= "s";
        }
        
        // Tambahkan NIM di akhir
        $params[] = $user_id;
        $types .= "s";
        
        // Build dan execute query
        $sql = "UPDATE mahasiswa SET " . implode(", ", $update_fields) . " WHERE nim = ?";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Profil berhasil diperbarui!';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui profil: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = 'Error prepare statement: ' . $conn->error;
        }
    }
}

$conn->close();
header("Location: edit_profil.php");
exit();
?>