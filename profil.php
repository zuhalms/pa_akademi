<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Tentukan judul halaman sebelum memanggil header
$page_title = 'Profil Saya';

// Include konfigurasi database (otomatis XAMPP atau InfinityFree)
require_once 'config.php';
require 'templates/header.php';

// Keamanan
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];
$data_profil = [];

// $conn sudah siap dari config.php (otomatis XAMPP atau InfinityFree)

// Ambil data profil berdasarkan peran pengguna
if ($user_role == 'mahasiswa') {
    $stmt = $conn->prepare("
        SELECT m.*, p.nama_prodi, d.nama_dosen as nama_dosen_pa
        FROM mahasiswa m
        LEFT JOIN program_studi p ON m.id_prodi = p.id_prodi
        LEFT JOIN dosen d ON m.id_dosen_pa = d.id_dosen
        WHERE m.nim = ?
    ");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data_profil = $result->fetch_assoc();
    }
    $stmt->close();
    
} elseif ($user_role == 'dosen') {
    $stmt = $conn->prepare("SELECT * FROM dosen WHERE id_dosen = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data_profil = $result->fetch_assoc();
    }
    $stmt->close();
}

// Path foto profil
$foto_path = 'assets/uploads/default-profile.png';
if ($user_role == 'dosen' && !empty($data_profil['foto_dosen']) && file_exists('assets/uploads/' . $data_profil['foto_dosen'])) {
    $foto_path = 'assets/uploads/' . $data_profil['foto_dosen'];
} elseif ($user_role == 'mahasiswa' && !empty($data_profil['foto_mahasiswa']) && file_exists('assets/uploads/' . $data_profil['foto_mahasiswa'])) {
    $foto_path = 'assets/uploads/' . $data_profil['foto_mahasiswa'];
}
?>

<style>
    /* ========== IMPROVED PROFIL CSS ========== */
    :root {
        --campus-green: #049D6F;
        --smart-blue: #0d6efd;
        --light-gray: #f8f9fa;
        --gray: #e9ecef;
        --dark-gray: #495057;
        --card-shadow: 0 4px 20px rgba(0,0,0,0.08);
        --card-shadow-hover: 0 8px 30px rgba(0,0,0,0.12);
        --card-radius: 1rem;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    /* ========== PROFILE HEADER CARD ========== */
    .profile-header-banner {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: var(--card-radius) var(--card-radius) 0 0;
        padding: 3rem 2rem 8rem 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .profile-header-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        animation: pulse 10s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1) rotate(0deg); }
        50% { transform: scale(1.2) rotate(180deg); }
    }
    
    .profile-header-banner h1 {
        color: white;
        font-weight: 800;
        font-size: 2rem;
        margin: 0;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        position: relative;
        z-index: 1;
    }
    
    .profile-header-banner .badge-role {
        background: rgba(255,255,255,0.3);
        backdrop-filter: blur(10px);
        color: white;
        padding: 0.5rem 1.25rem;
        border-radius: 2rem;
        font-weight: 600;
        font-size: 0.9rem;
        border: 2px solid rgba(255,255,255,0.5);
        position: relative;
        z-index: 1;
    }
    
    /* ========== MAIN CARD ========== */
    .card {
        border: none;
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        overflow: visible;
    }
    
    .card:hover {
        box-shadow: var(--card-shadow-hover);
    }
    
    /* ========== PHOTO SECTION ========== */
    .photo-container {
        position: relative;
        margin-top: -6rem;
        z-index: 10;
    }
    
    .photo-card {
        background: white;
        border-radius: var(--card-radius);
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
        text-align: center;
        transition: var(--transition);
    }
    
    .photo-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-shadow-hover);
    }
    
    .profile-photo-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 1.5rem;
    }
    
    .profile-photo {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border-radius: 50%;
        border: 6px solid white;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: var(--transition);
    }
    
    .profile-photo:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 35px rgba(0,0,0,0.2);
    }
    
    .photo-badge {
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        border: 4px solid white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    /* ========== UPLOAD FORM ========== */
    .upload-form {
        margin-top: 1rem;
    }
    
    .upload-form .form-control {
        border-radius: 0.5rem;
        border: 2px dashed var(--gray);
        transition: var(--transition);
        padding: 0.75rem;
        font-size: 0.9rem;
    }
    
    .upload-form .form-control:hover {
        border-color: var(--campus-green);
        background-color: rgba(4, 157, 111, 0.05);
    }
    
    .upload-form .form-control:focus {
        border-color: var(--campus-green);
        border-style: solid;
        box-shadow: 0 0 0 0.25rem rgba(4, 157, 111, 0.15);
    }
    
    /* ========== INFO SECTION ========== */
    .info-section {
        padding: 2rem;
    }
    
    .info-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--gray);
    }
    
    .info-header h5 {
        color: var(--campus-green);
        font-weight: 800;
        margin: 0;
        font-size: 1.5rem;
    }
    
    /* ========== TABLE IMPROVEMENTS ========== */
    .table {
        margin-bottom: 0;
    }
    
    .table tbody tr {
        transition: var(--transition);
        border-bottom: 1px solid #f0f2f5;
    }
    
    .table tbody tr:hover {
        background: linear-gradient(135deg, rgba(4, 157, 111, 0.03) 0%, rgba(4, 157, 111, 0.01) 100%);
        transform: translateX(5px);
    }
    
    .table tbody th {
        color: var(--dark-gray);
        font-weight: 700;
        padding: 1rem 0.5rem;
        width: 35%;
        position: relative;
    }
    
    .table tbody th::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 60%;
        background: linear-gradient(to bottom, var(--campus-green), #43e97b);
        border-radius: 0 4px 4px 0;
        opacity: 0;
        transition: var(--transition);
    }
    
    .table tbody tr:hover th::before {
        opacity: 1;
    }
    
    .table tbody td {
        padding: 1rem 0.5rem;
        color: var(--dark-gray);
        font-weight: 500;
    }
    
    /* ========== HOBI BADGE STYLE ========== */
    .hobi-badge {
        display: inline-block;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 0.4rem 0.9rem;
        border-radius: 1.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
        transition: var(--transition);
    }
    
    .hobi-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    }
    
    .hobi-badge i {
        margin-right: 0.3rem;
    }
    
    /* ========== LIST GROUP (DOSEN - OLD STYLE) ========== */
    .list-group-item {
        border: none;
        border-bottom: 1px solid #f0f2f5;
        padding: 1.25rem 1rem;
        transition: var(--transition);
        background: transparent;
    }
    
    .list-group-item:hover {
        background: linear-gradient(135deg, rgba(4, 157, 111, 0.05) 0%, rgba(4, 157, 111, 0.02) 100%);
        transform: translateX(8px);
        padding-left: 1.5rem;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    .list-group-item strong {
        color: var(--campus-green);
        font-weight: 700;
        display: inline-block;
        min-width: 140px;
    }
    
    /* ========== BUTTONS ========== */
    .btn {
        border-radius: 0.5rem;
        font-weight: 600;
        padding: 0.65rem 1.5rem;
        transition: var(--transition);
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .btn:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .btn-light {
        background: rgba(255,255,255,0.95);
        color: var(--campus-green);
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .btn-light:hover {
        background: white;
        color: var(--campus-green);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    
    .btn-outline-secondary {
        border: 2px solid var(--gray);
        color: var(--dark-gray);
        background: transparent;
    }
    
    .btn-outline-secondary:hover {
        background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%);
        color: white;
        border-color: transparent;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(4, 157, 111, 0.3);
    }
    
    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
    
    /* ========== EMPTY STATE ========== */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #cbd5e0;
        margin-bottom: 1rem;
    }
    
    .empty-state h6 {
        color: #718096;
        font-weight: 600;
    }
    
    .empty-state p {
        color: #a0aec0;
        margin: 0;
    }
    
    /* ========== LOADING STATE ========== */
    .btn-loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }
    
    .btn-loading::after {
        content: "";
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        right: 10px;
        margin-top: -8px;
        border: 2px solid currentColor;
        border-radius: 50%;
        border-right-color: transparent;
        animation: spinner-border 0.75s linear infinite;
    }
    
    @keyframes spinner-border {
        to { transform: rotate(360deg); }
    }
    
    /* ========== RESPONSIVE ========== */
    @media (max-width: 992px) {
        .profile-header-banner {
            padding: 2rem 1.5rem 6rem 1.5rem;
        }
        
        .profile-header-banner h1 {
            font-size: 1.5rem;
        }
        
        .photo-container {
            margin-top: -4rem;
        }
        
        .profile-photo {
            width: 150px;
            height: 150px;
        }
        
        .photo-badge {
            width: 40px;
            height: 40px;
            font-size: 1.25rem;
        }
    }
    
    @media (max-width: 768px) {
        body {
            background: var(--light-gray);
        }
        
        .profile-header-banner {
            padding: 1.5rem 1rem 5rem 1rem;
        }
        
        .profile-header-banner h1 {
            font-size: 1.25rem;
        }
        
        .info-section {
            padding: 1.5rem;
        }
        
        .table tbody th {
            width: 40%;
            font-size: 0.9rem;
        }
        
        .table tbody td {
            font-size: 0.9rem;
        }
        
        .hobi-badge {
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
        }
    }
    
    @media (max-width: 576px) {
        .profile-photo {
            width: 120px;
            height: 120px;
        }
        
        .photo-card {
            padding: 1rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
    }
</style>

<div class="container my-5">
    <div class="col-lg-10 mx-auto">
        <!-- Header Banner -->
        <div class="profile-header-banner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1><i class="bi bi-person-circle me-2"></i>Profil Saya</h1>
                    <span class="badge-role">
                        <i class="bi bi-<?= $user_role == 'mahasiswa' ? 'mortarboard' : 'person-workspace' ?> me-2"></i>
                        <?= ucfirst($user_role); ?>
                    </span>
                </div>
                <a href="edit_profil.php" class="btn btn-light">
                    <i class="bi bi-pencil-square me-2"></i>Edit Profil
                </a>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-body p-0">
                <?php if ($user_role == 'mahasiswa' && !empty($data_profil)): ?>
                    <div class="row g-0">
                        <!-- Photo Section Mahasiswa -->
                        <div class="col-lg-4">
                            <div class="photo-container">
                                <div class="photo-card">
                                    <div class="profile-photo-wrapper">
                                        <img src="<?= htmlspecialchars($foto_path); ?>" 
                                             class="profile-photo" 
                                             alt="Foto Profil"
                                             onerror="this.src='assets/uploads/default-profile.png';">
                                        <div class="photo-badge">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                    </div>
                                    
                                    <h5 class="mb-1 fw-bold"><?= htmlspecialchars($data_profil['nama_mahasiswa']); ?></h5>
                                    <p class="text-muted mb-3">
                                        <i class="bi bi-credit-card-2-front me-1"></i>
                                        <?= htmlspecialchars($data_profil['nim']); ?>
                                    </p>
                                    
                                    <form action="upload_photo.php" 
                                          method="POST" 
                                          enctype="multipart/form-data" 
                                          class="upload-form"
                                          id="uploadPhotoForm">
                                        <input type="hidden" name="nim" value="<?= htmlspecialchars($data_profil['nim']); ?>">
                                        <div class="mb-3">
                                            <input type="file" 
                                                   name="photo" 
                                                   accept="image/*" 
                                                   class="form-control form-control-sm" 
                                                   id="photoInput"
                                                   required>
                                            <div class="form-text">JPG, PNG, max 2MB</div>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-outline-secondary" id="btnUpload">
                                                <i class="bi bi-cloud-upload me-2"></i>Ganti Foto
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Info Section Mahasiswa -->
                        <div class="col-lg-8">
                            <div class="info-section">
                                <div class="info-header">
                                    <h5><i class="bi bi-info-circle-fill me-2"></i>Informasi Pribadi</h5>
                                    <span class="badge bg-<?= isset($data_profil['status_semester']) && $data_profil['status_semester'] == 'A' ? 'success' : 'danger' ?> rounded-pill">
                                        <?= isset($data_profil['status_semester']) && $data_profil['status_semester'] == 'A' ? 'Aktif' : 'Tidak Aktif' ?>
                                    </span>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <th><i class="bi bi-card-text me-2"></i>NIM</th>
                                                <td>: <?= htmlspecialchars($data_profil['nim']); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-person-fill me-2"></i>Nama Lengkap</th>
                                                <td>: <?= htmlspecialchars($data_profil['nama_mahasiswa']); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-geo-alt-fill me-2"></i>Alamat</th>
                                                <td>: <?= htmlspecialchars($data_profil['alamat'] ?? 'Belum diisi'); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-calendar-event me-2"></i>Tempat/Tgl Lahir</th>
                                                <td>: <?= htmlspecialchars(($data_profil['tempat_lahir'] ?? 'Belum diisi') . ', ' . (!empty($data_profil['tgl_lahir']) ? date('d F Y', strtotime($data_profil['tgl_lahir'])) : '-')); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-envelope-fill me-2"></i>Email</th>
                                                <td>: <?= htmlspecialchars($data_profil['email'] ?? 'Belum diisi'); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-telephone-fill me-2"></i>No. Telepon</th>
                                                <td>: <?= htmlspecialchars($data_profil['telp'] ?? 'Belum diisi'); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-gender-ambiguous me-2"></i>Jenis Kelamin</th>
                                                <td>: <?= htmlspecialchars($data_profil['jenis_kelamin'] ?? 'Belum diisi'); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-mortarboard-fill me-2"></i>Program Studi</th>
                                                <td>: <?= htmlspecialchars($data_profil['nama_prodi'] ?? 'Belum diisi'); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-ladder me-2"></i>Jenjang</th>
                                                <td>: <?= htmlspecialchars($data_profil['jenjang'] ?? 'S1'); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-person-workspace me-2"></i>Dosen PA</th>
                                                <td>: <?= htmlspecialchars($data_profil['nama_dosen_pa'] ?? 'Belum ditentukan'); ?></td>
                                            </tr>
                                            <!-- HOBI SECTION -->
                                            <tr>
                                                <th><i class="bi bi-heart-fill me-2" style="color: #f59e0b;"></i>Hobi</th>
                                                <td>: 
                                                    <?php 
                                                    if (!empty($data_profil['hobi'])) {
                                                        $hobi_list = explode(',', $data_profil['hobi']);
                                                        foreach ($hobi_list as $hobi) {
                                                            $hobi_trimmed = trim($hobi);
                                                            if (!empty($hobi_trimmed)) {
                                                                echo '<span class="hobi-badge">
                                                                        <i class="bi bi-star-fill"></i>' . 
                                                                        htmlspecialchars($hobi_trimmed) . 
                                                                      '</span>';
                                                            }
                                                        }
                                                    } else {
                                                        echo '<span class="text-muted">Belum diisi</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($user_role == 'dosen' && !empty($data_profil)): ?>
                    <div class="row g-0">
                        <!-- Photo Section Dosen -->
                        <div class="col-lg-4">
                            <div class="photo-container">
                                <div class="photo-card">
                                    <div class="profile-photo-wrapper">
                                        <img src="<?= htmlspecialchars($foto_path); ?>" 
                                             class="profile-photo" 
                                             alt="Foto Profil"
                                             onerror="this.src='assets/uploads/default-profile.png';">
                                        <div class="photo-badge">
                                            <i class="bi bi-person-workspace"></i>
                                        </div>
                                    </div>
                                    
                                    <h5 class="mb-1 fw-bold"><?= htmlspecialchars($data_profil['nama_dosen']); ?></h5>
                                    <p class="text-muted mb-1">
                                        <i class="bi bi-award me-1"></i>
                                        <?= htmlspecialchars($data_profil['jabatan_akademik'] ?? 'Dosen'); ?>
                                    </p>
                                    <p class="text-muted mb-3">
                                        <small>Dosen Pembimbing Akademik</small>
                                    </p>
                                    
                                    <form action="upload_foto_dosen.php" 
                                          method="POST" 
                                          enctype="multipart/form-data" 
                                          class="upload-form"
                                          id="uploadPhotoFormDosen">
                                        <input type="hidden" name="id_dosen" value="<?= htmlspecialchars($data_profil['id_dosen']); ?>">
                                        <div class="mb-3">
                                            <input type="file" 
                                                   name="photo" 
                                                   accept="image/*" 
                                                   class="form-control form-control-sm" 
                                                   required>
                                            <div class="form-text">JPG, PNG, max 2MB</div>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-outline-secondary" id="btnUploadDosen">
                                                <i class="bi bi-cloud-upload me-2"></i>Ganti Foto
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Info Section Dosen -->
                        <div class="col-lg-8">
                            <div class="info-section">
                                <div class="info-header">
                                    <h5><i class="bi bi-info-circle-fill me-2"></i>Biodata Dosen</h5>
                                    <span class="badge bg-success rounded-pill">
                                        <i class="bi bi-shield-check me-1"></i>
                                        <?= htmlspecialchars($data_profil['status_dosen'] ?? 'Aktif'); ?>
                                    </span>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <th><i class="bi bi-card-text me-2"></i>ID Dosen</th>
                                                <td>: <?= htmlspecialchars($data_profil['nidn_dosen'] ?? 'Belum diisi'); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-card-text me-2"></i>NIP</th>
                                                <td>: <?= htmlspecialchars($data_profil['nip'] ?? 'Belum diisi'); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-person-fill me-2"></i>Nama Lengkap</th>
                                                <td>: <?= htmlspecialchars($data_profil['nama_dosen']); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-calendar-event me-2"></i>Tempat/Tgl Lahir</th>
                                                <td>: <?= htmlspecialchars(($data_profil['tempat_lahir'] ?? 'Belum diisi') . ', ' . (!empty($data_profil['tgl_lahir']) ? date('d F Y', strtotime($data_profil['tgl_lahir'])) : '-')); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-gender-ambiguous me-2"></i>Jenis Kelamin</th>
                                                <td>: <?= htmlspecialchars($data_profil['jenis_kelamin'] ?? 'Belum diisi'); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-envelope-fill me-2"></i>Email</th>
                                                <td>: <?= htmlspecialchars($data_profil['email_dosen'] ?? 'Belum diisi'); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-telephone-fill me-2"></i>Nomor Telepon</th>
                                                <td>: <?= htmlspecialchars($data_profil['telp_dosen'] ?? 'Belum diisi'); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-geo-alt-fill me-2"></i>Alamat</th>
                                                <td>: <?= htmlspecialchars($data_profil['alamat'] ?? 'Belum diisi'); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-mortarboard-fill me-2"></i>Pendidikan Terakhir</th>
                                                <td>: 
                                                    <?php 
                                                    $pendidikan = $data_profil['pendidikan_terakhir'] ?? 'Belum diisi';
                                                    $badge_class = 'bg-primary';
                                                    if ($pendidikan == 'S3' || $pendidikan == 'Profesor') {
                                                        $badge_class = 'bg-success';
                                                    } elseif ($pendidikan == 'S2') {
                                                        $badge_class = 'bg-info';
                                                    }
                                                    echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars($pendidikan) . '</span>';
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-award-fill me-2" style="color: #f59e0b;"></i>Jabatan Akademik</th>
                                                <td>: 
                                                    <?php 
                                                    $jabatan = $data_profil['jabatan_akademik'] ?? 'Belum diisi';
                                                    $badge_color = 'bg-warning text-dark';
                                                    if ($jabatan == 'Profesor') {
                                                        $badge_color = 'bg-danger';
                                                    } elseif ($jabatan == 'Lektor Kepala') {
                                                        $badge_color = 'bg-success';
                                                    } elseif ($jabatan == 'Lektor') {
                                                        $badge_color = 'bg-info';
                                                    }
                                                    echo '<span class="badge ' . $badge_color . '">' . htmlspecialchars($jabatan) . '</span>';
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-lightbulb-fill me-2" style="color: #fbbf24;"></i>Bidang Keahlian</th>
                                                <td>: <?= htmlspecialchars($data_profil['bidang_keahlian'] ?? 'Belum diisi'); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-exclamation-triangle"></i>
                        <h6>Data Profil Tidak Ditemukan</h6>
                        <p>Tidak dapat memuat data profil. Silakan hubungi administrator.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle photo upload form (Mahasiswa)
    const uploadForm = document.getElementById('uploadPhotoForm');
    if (uploadForm) {
        const photoInput = document.getElementById('photoInput');
        const btnUpload = document.getElementById('btnUpload');
        
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('⚠️ Ukuran file maksimal 2MB!');
                    this.value = '';
                    return;
                }
                
                if (!file.type.match('image.*')) {
                    alert('⚠️ File harus berupa gambar!');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const profilePhoto = document.querySelector('.profile-photo');
                    if (profilePhoto) {
                        profilePhoto.style.opacity = '0.7';
                        setTimeout(() => {
                            profilePhoto.src = e.target.result;
                            profilePhoto.style.opacity = '1';
                        }, 300);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
        
        uploadForm.addEventListener('submit', function() {
            btnUpload.classList.add('btn-loading');
            btnUpload.disabled = true;
            btnUpload.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Mengunggah...';
        });
    }
    
    // Handle photo upload form (Dosen)
    const uploadFormDosen = document.getElementById('uploadPhotoFormDosen');
    if (uploadFormDosen) {
        const btnUploadDosen = document.getElementById('btnUploadDosen');
        
        uploadFormDosen.querySelector('input[type="file"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('⚠️ Ukuran file maksimal 2MB!');
                    this.value = '';
                    return;
                }
                
                if (!file.type.match('image.*')) {
                    alert('⚠️ File harus berupa gambar!');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const profilePhoto = document.querySelector('.profile-photo');
                    if (profilePhoto) {
                        profilePhoto.style.opacity = '0.7';
                        setTimeout(() => {
                            profilePhoto.src = e.target.result;
                            profilePhoto.style.opacity = '1';
                        }, 300);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
        
        uploadFormDosen.addEventListener('submit', function() {
            btnUploadDosen.classList.add('btn-loading');
            btnUploadDosen.disabled = true;
            btnUploadDosen.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Mengunggah...';
        });
    }
});
</script>

<?php 
// Tutup koneksi
$conn->close();
require 'templates/footer.php'; 
?>  