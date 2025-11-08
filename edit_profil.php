<?php
$page_title = 'Edit Profil';

// Include konfigurasi database (otomatis XAMPP atau InfinityFree)
require_once 'config.php';
require 'templates/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$data_profil = [];

// $conn sudah siap dari config.php (otomatis XAMPP atau InfinityFree)

// Ambil data profil yang ada untuk ditampilkan di form
if ($user_role == 'dosen') {
    $stmt = $conn->prepare("SELECT * FROM dosen WHERE id_dosen = ?");
    $stmt->bind_param("i", $user_id);
} else { // Mahasiswa
    $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
    $stmt->bind_param("s", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $data_profil = $result->fetch_assoc();
}
$stmt->close();

// Ambil daftar dokumen prestasi mahasiswa (jika mahasiswa)
$dokumen_prestasi = [];
if ($user_role == 'mahasiswa') {
    $stmt_prestasi = $conn->prepare("SELECT * FROM dokumen_prestasi WHERE nim = ? ORDER BY tanggal_upload DESC");
    $stmt_prestasi->bind_param("s", $user_id);
    $stmt_prestasi->execute();
    $result_prestasi = $stmt_prestasi->get_result();
    while ($row = $result_prestasi->fetch_assoc()) {
        $dokumen_prestasi[] = $row;
    }
    $stmt_prestasi->close();
}

// Notifikasi
$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<style>
    /* ========== IMPROVED EDIT PROFIL CSS ========== */
    :root {
        --campus-green: #049D6F;
        --smart-blue: #0d6efd;
        --danger-red: #dc3545;
        --warning-orange: #fd7e14;
        --light-bg: #f0f2f5;
        --card-shadow: 0 2px 12px rgba(0,0,0,0.08);
        --card-shadow-hover: 0 8px 24px rgba(0,0,0,0.12);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --card-radius: 1rem;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* ========== PAGE HEADER STYLE ========== */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding: 0;
    }

    .page-header h2 {
        margin: 0;
        color: #2d3748;
        font-weight: 800;
        font-size: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-header h2 i {
        color: var(--campus-green);
    }

    /* ========== BACK BUTTON STYLE ========== */
    .back-button {
        background: white;
        color: var(--campus-green);
        border: 2px solid var(--campus-green);
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        transition: var(--transition);
        box-shadow: var(--card-shadow);
    }

    .back-button:hover {
        background: var(--campus-green);
        color: white;
        transform: translateX(-5px);
        box-shadow: var(--card-shadow-hover);
    }

    .back-button i {
        font-size: 1.2rem;
        transition: var(--transition);
    }

    .back-button:hover i {
        transform: translateX(-3px);
    }

    /* ========== ALERT IMPROVEMENTS ========== */
    .alert {
        border-radius: 0.75rem;
        border: none;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        animation: slideInDown 0.4s ease-out;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .alert-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    /* ========== CARD IMPROVEMENTS ========== */
    .card {
        border: none;
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        margin-bottom: 1.5rem;
    }

    .card:hover {
        box-shadow: var(--card-shadow-hover);
    }

    .card-header {
        background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%);
        color: white;
        border-radius: var(--card-radius) var(--card-radius) 0 0 !important;
        padding: 1.5rem;
        border: none;
    }

    .card-header h4 {
        margin: 0;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-body {
        padding: 2rem;
        background: white;
    }

    /* ========== FORM IMPROVEMENTS ========== */
    .form-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-control, .form-select, textarea.form-control {
        border: 2px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        transition: var(--transition);
        font-size: 0.95rem;
    }

    .form-control:focus, .form-select:focus, textarea.form-control:focus {
        border-color: var(--campus-green);
        box-shadow: 0 0 0 0.2rem rgba(4, 157, 111, 0.15);
    }

    .form-control:read-only {
        background-color: #f7fafc;
        cursor: not-allowed;
    }

    /* ========== HOBI TAG STYLE ========== */
    .hobi-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .hobi-tag {
        background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%);
        color: white;
        padding: 0.4rem 0.9rem;
        border-radius: 1.5rem;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: var(--transition);
    }

    .hobi-tag:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(4, 157, 111, 0.3);
    }

    .hobi-suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.75rem;
    }

    .hobi-suggestion-btn {
        background: #f0f2f5;
        border: 2px solid #e2e8f0;
        color: #4a5568;
        padding: 0.4rem 0.9rem;
        border-radius: 1.5rem;
        font-size: 0.85rem;
        cursor: pointer;
        transition: var(--transition);
    }

    .hobi-suggestion-btn:hover {
        background: var(--campus-green);
        color: white;
        border-color: var(--campus-green);
    }

    /* ========== BUTTON IMPROVEMENTS ========== */
    .btn {
        border-radius: 0.5rem;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: var(--transition);
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%);
        box-shadow: 0 2px 8px rgba(4, 157, 111, 0.3);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #037a59 0%, #026146 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(4, 157, 111, 0.4);
    }

    .btn-secondary {
        background: #6c757d;
        box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .btn-success:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    /* ========== PRESTASI SECTION ========== */
    .prestasi-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border-radius: var(--card-radius) var(--card-radius) 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .prestasi-header h5 {
        margin: 0;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .dokumen-item {
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 1.25rem;
        margin-bottom: 1rem;
        transition: var(--transition);
        background: white;
    }

    .dokumen-item:hover {
        border-color: var(--campus-green);
        box-shadow: 0 4px 12px rgba(4, 157, 111, 0.15);
        transform: translateX(5px);
    }

    .dokumen-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .dokumen-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%);
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .dokumen-details h6 {
        margin: 0;
        font-weight: 600;
        color: #2d3748;
    }

    .dokumen-meta {
        font-size: 0.85rem;
        color: #718096;
        margin-top: 0.25rem;
    }

    .dokumen-actions {
        display: flex;
        gap: 0.5rem;
    }

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

    /* ========== FILE UPLOAD ZONE ========== */
    .upload-zone {
        border: 3px dashed #cbd5e0;
        border-radius: 0.75rem;
        padding: 2rem;
        text-align: center;
        transition: var(--transition);
        background: #f7fafc;
        cursor: pointer;
    }

    .upload-zone:hover {
        border-color: var(--campus-green);
        background: rgba(4, 157, 111, 0.05);
    }

    .upload-zone.drag-over {
        border-color: var(--campus-green);
        background: rgba(4, 157, 111, 0.1);
    }

    .upload-zone i {
        font-size: 3rem;
        color: var(--campus-green);
        margin-bottom: 1rem;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .page-header h2 {
            font-size: 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .dokumen-item {
            padding: 1rem;
        }

        .dokumen-info {
            flex-direction: column;
            align-items: flex-start;
        }

        .dokumen-actions {
            width: 100%;
            margin-top: 1rem;
        }

        .dokumen-actions .btn {
            flex: 1;
        }

        .btn {
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
        }

        .back-button {
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
        }
    }
</style>

<div class="container my-4">
    <!-- PAGE HEADER WITH BACK BUTTON -->
    <div class="page-header">
        <h2>
            <i class="bi bi-pencil-square"></i>
            Edit Profil
        </h2>
        <a href="profil.php" class="back-button">
            <i class="bi bi-arrow-left-circle"></i>
            Kembali
        </a>
    </div>

    <!-- NOTIFIKASI -->
    <?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- FORM EDIT PROFIL -->
        <div class="col-lg-<?= ($user_role == 'mahasiswa') ? '7' : '8' ?> mx-auto">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4><i class="bi bi-person-badge"></i> Biodata <?= ucfirst($user_role); ?></h4>
                </div>
                <div class="card-body">
                    <form action="update_profil.php" method="POST">
                        
                        <?php if ($user_role == 'dosen'): ?>
                        <!-- FORM DOSEN LENGKAP -->
                        <div class="mb-3">
                            <label for="nama" class="form-label">
                                <i class="bi bi-person-fill text-primary"></i> Nama Lengkap
                            </label>
                            <input type="text" class="form-control" id="nama" name="nama_dosen" 
                                   value="<?= htmlspecialchars($data_profil['nama_dosen'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nidn" class="form-label">
                                    <i class="bi bi-card-text text-success"></i> ID Dosen
                                </label>
                                <input type="text" class="form-control" id="nidn" 
                                       value="<?= htmlspecialchars($data_profil['nidn_dosen'] ?? ''); ?>" readonly>
                                <small class="text-muted">ID Dosen tidak dapat diubah.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nip" class="form-label">
                                    <i class="bi bi-card-text text-success"></i> NIP
                                </label>
                                <input type="text" class="form-control" id="nip" name="nip"
                                       value="<?= htmlspecialchars($data_profil['nip'] ?? ''); ?>"
                                       placeholder="Contoh: 198501012010121001">
                            </div>
                        </div>
                        
                        <!-- TEMPAT DAN TANGGAL LAHIR DOSEN -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tempat_lahir" class="form-label">
                                    <i class="bi bi-geo-alt text-info"></i> Tempat Lahir
                                </label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" 
                                       value="<?= htmlspecialchars($data_profil['tempat_lahir'] ?? ''); ?>" 
                                       placeholder="Contoh: Palopo">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tgl_lahir" class="form-label">
                                    <i class="bi bi-calendar-event text-info"></i> Tanggal Lahir
                                </label>
                                <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir" 
                                       value="<?= htmlspecialchars($data_profil['tgl_lahir'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- JENIS KELAMIN DOSEN -->
                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">
                                <i class="bi bi-gender-ambiguous" style="color: #6f42c1;"></i> Jenis Kelamin
                            </label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" <?= (($data_profil['jenis_kelamin'] ?? '') == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?= (($data_profil['jenis_kelamin'] ?? '') == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope-fill text-danger"></i> Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email_dosen" 
                                   value="<?= htmlspecialchars($data_profil['email_dosen'] ?? ''); ?>"
                                   placeholder="contoh@email.com">
                        </div>
                        
                        <div class="mb-3">
                            <label for="telp" class="form-label">
                                <i class="bi bi-telephone-fill text-warning"></i> Nomor Telepon
                            </label>
                            <input type="text" class="form-control" id="telp" name="telp_dosen" 
                                   value="<?= htmlspecialchars($data_profil['telp_dosen'] ?? ''); ?>"
                                   placeholder="Contoh: 081234567890">
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamat" class="form-label">
                                <i class="bi bi-geo-alt-fill text-info"></i> Alamat
                            </label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($data_profil['alamat'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- PENDIDIKAN DAN KEAHLIAN -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pendidikan_terakhir" class="form-label">
                                    <i class="bi bi-mortarboard-fill" style="color: #0d6efd;"></i> Pendidikan Terakhir
                                </label>
                                <select class="form-select" id="pendidikan_terakhir" name="pendidikan_terakhir">
                                    <option value="">Pilih Pendidikan</option>
                                    <option value="S2" <?= (($data_profil['pendidikan_terakhir'] ?? '') == 'S2') ? 'selected' : ''; ?>>S2 (Magister)</option>
                                    <option value="S3" <?= (($data_profil['pendidikan_terakhir'] ?? '') == 'S3') ? 'selected' : ''; ?>>S3 (Doktor)</option>
                                    <option value="Profesor" <?= (($data_profil['pendidikan_terakhir'] ?? '') == 'Profesor') ? 'selected' : ''; ?>>Profesor</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="jabatan_akademik" class="form-label">
                                    <i class="bi bi-award-fill" style="color: #f59e0b;"></i> Jabatan Akademik
                                </label>
                                <select class="form-select" id="jabatan_akademik" name="jabatan_akademik">
                                    <option value="">Pilih Jabatan</option>
                                    <option value="Asisten Ahli" <?= (($data_profil['jabatan_akademik'] ?? '') == 'Asisten Ahli') ? 'selected' : ''; ?>>Asisten Ahli</option>
                                    <option value="Lektor" <?= (($data_profil['jabatan_akademik'] ?? '') == 'Lektor') ? 'selected' : ''; ?>>Lektor</option>
                                    <option value="Lektor Kepala" <?= (($data_profil['jabatan_akademik'] ?? '') == 'Lektor Kepala') ? 'selected' : ''; ?>>Lektor Kepala</option>
                                    <option value="Profesor" <?= (($data_profil['jabatan_akademik'] ?? '') == 'Profesor') ? 'selected' : ''; ?>>Profesor</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bidang_keahlian" class="form-label">
                                <i class="bi bi-lightbulb-fill" style="color: #fbbf24;"></i> Bidang Keahlian
                            </label>
                            <input type="text" class="form-control" id="bidang_keahlian" name="bidang_keahlian" 
                                   value="<?= htmlspecialchars($data_profil['bidang_keahlian'] ?? ''); ?>"
                                   placeholder="Contoh: Hukum Islam, Ekonomi Syariah">
                        </div>

                        <?php elseif ($user_role == 'mahasiswa'): ?>
                        <!-- FORM MAHASISWA -->
                        <div class="mb-3">
                            <label for="nama" class="form-label">
                                <i class="bi bi-person-fill text-primary"></i> Nama Lengkap
                            </label>
                            <input type="text" class="form-control" id="nama" name="nama_mahasiswa" 
                                   value="<?= htmlspecialchars($data_profil['nama_mahasiswa'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="nim" class="form-label">
                                <i class="bi bi-credit-card-2-front text-success"></i> NIM
                            </label>
                            <input type="text" class="form-control" id="nim" 
                                   value="<?= htmlspecialchars($data_profil['nim'] ?? ''); ?>" readonly>
                            <small class="text-muted">NIM tidak dapat diubah.</small>
                        </div>
                        
                        <!-- TEMPAT DAN TANGGAL LAHIR -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tempat_lahir" class="form-label">
                                    <i class="bi bi-geo-alt text-info"></i> Tempat Lahir
                                </label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" 
                                       value="<?= htmlspecialchars($data_profil['tempat_lahir'] ?? ''); ?>" 
                                       placeholder="Contoh: Palopo">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tgl_lahir" class="form-label">
                                    <i class="bi bi-calendar-event text-info"></i> Tanggal Lahir
                                </label>
                                <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir" 
                                       value="<?= htmlspecialchars($data_profil['tgl_lahir'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <!-- JENIS KELAMIN -->
                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">
                                <i class="bi bi-gender-ambiguous" style="color: #6f42c1;"></i> Jenis Kelamin
                            </label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" <?= (($data_profil['jenis_kelamin'] ?? '') == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?= (($data_profil['jenis_kelamin'] ?? '') == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope-fill text-danger"></i> Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($data_profil['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="telp" class="form-label">
                                <i class="bi bi-telephone-fill text-warning"></i> Nomor Telepon
                            </label>
                            <input type="text" class="form-control" id="telp" name="telp" 
                                   value="<?= htmlspecialchars($data_profil['telp'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">
                                <i class="bi bi-geo-alt-fill text-info"></i> Alamat
                            </label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($data_profil['alamat'] ?? ''); ?></textarea>
                        </div>
                        
                        <!-- FIELD HOBI -->
                        <div class="mb-3">
                            <label for="hobi" class="form-label">
                                <i class="bi bi-heart-fill" style="color: #f59e0b;"></i> Hobi
                            </label>
                            <textarea class="form-control" id="hobi" name="hobi" rows="3" 
                                      placeholder="Contoh: Membaca, Olahraga, Menulis, Traveling"><?= htmlspecialchars($data_profil['hobi'] ?? ''); ?></textarea>
                            <small class="text-muted">Pisahkan dengan koma (,) untuk multiple hobi</small>
                            
                            <!-- Preview Hobi Tags -->
                            <div class="hobi-tags" id="hobiPreview"></div>
                            
                            <!-- Saran Hobi -->
                            <div class="mt-3">
                                <small class="text-muted fw-bold">💡 Saran Hobi (Klik untuk menambah):</small>
                                <div class="hobi-suggestions">
                                    <button type="button" class="hobi-suggestion-btn" onclick="addHobi('Membaca')">📚 Membaca</button>
                                    <button type="button" class="hobi-suggestion-btn" onclick="addHobi('Olahraga')">⚽ Olahraga</button>
                                    <button type="button" class="hobi-suggestion-btn" onclick="addHobi('Menulis')">✍️ Menulis</button>
                                    <button type="button" class="hobi-suggestion-btn" onclick="addHobi('Musik')">🎵 Musik</button>
                                    <button type="button" class="hobi-suggestion-btn" onclick="addHobi('Fotografi')">📷 Fotografi</button>
                                    <button type="button" class="hobi-suggestion-btn" onclick="addHobi('Traveling')">✈️ Traveling</button>
                                    <button type="button" class="hobi-suggestion-btn" onclick="addHobi('Memasak')">🍳 Memasak</button>
                                    <button type="button" class="hobi-suggestion-btn" onclick="addHobi('Gaming')">🎮 Gaming</button>
                                    <button type="button" class="hobi-suggestion-btn" onclick="addHobi('Seni')">🎨 Seni</button>
                                    <button type="button" class="hobi-suggestion-btn" onclick="addHobi('Coding')">💻 Coding</button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="profil.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SECTION UPLOAD DOKUMEN PRESTASI (HANYA MAHASISWA) -->
            <?php if ($user_role == 'mahasiswa'): ?>
            <div class="card shadow-sm">
                <div class="prestasi-header">
                    <h5><i class="bi bi-trophy-fill"></i> Dokumen Prestasi</h5>
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="bi bi-plus-circle"></i> Upload Baru
                    </button>
                </div>
                <div class="card-body">
                    <?php if (count($dokumen_prestasi) > 0): ?>
                        <?php foreach ($dokumen_prestasi as $dok): ?>
                        <div class="dokumen-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="dokumen-info">
                                    <div class="dokumen-icon">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </div>
                                    <div class="dokumen-details">
                                        <h6><?= htmlspecialchars($dok['nama_prestasi']); ?></h6>
                                        <div class="dokumen-meta">
                                            <i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($dok['tanggal_upload'])); ?>
                                            <span class="mx-2">•</span>
                                            <i class="bi bi-file-text"></i> <?= htmlspecialchars($dok['jenis_prestasi']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="dokumen-actions">
                                    <a href="assets/uploads/prestasi/<?= htmlspecialchars($dok['file_dokumen']); ?>" 
                                       target="_blank" class="btn btn-sm btn-success">
                                        <i class="bi bi-eye"></i> Lihat
                                    </a>
                                    <a href="hapus_prestasi.php?id=<?= $dok['id_prestasi']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Yakin ingin menghapus dokumen ini?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-folder-x"></i>
                            <h6>Belum Ada Dokumen Prestasi</h6>
                            <p>Klik tombol "Upload Baru" untuk menambahkan dokumen prestasi Anda.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL UPLOAD PRESTASI (HANYA MAHASISWA) -->
<?php if ($user_role == 'mahasiswa'): ?>
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="bi bi-cloud-upload"></i> Upload Dokumen Prestasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="upload_prestasi.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama_prestasi" class="form-label">
                            <i class="bi bi-award"></i> Nama Prestasi
                        </label>
                        <input type="text" class="form-control" id="nama_prestasi" name="nama_prestasi" 
                               placeholder="Contoh: Juara 1 Lomba Karya Tulis Ilmiah" required>
                    </div>
                    <div class="mb-3">
                        <label for="jenis_prestasi" class="form-label">
                            <i class="bi bi-tag"></i> Jenis Prestasi
                        </label>
                        <select class="form-select" id="jenis_prestasi" name="jenis_prestasi" required>
                            <option value="">Pilih Jenis Prestasi</option>
                            <option value="Akademik">Akademik</option>
                            <option value="Non-Akademik">Non-Akademik</option>
                            <option value="Organisasi">Organisasi</option>
                            <option value="Sertifikasi">Sertifikasi</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="file_dokumen" class="form-label">
                            <i class="bi bi-file-earmark-arrow-up"></i> File Dokumen
                        </label>
                        <div class="upload-zone" onclick="document.getElementById('file_dokumen').click()">
                            <i class="bi bi-cloud-upload"></i>
                            <h6>Klik untuk memilih file</h6>
                            <p class="text-muted mb-0">PDF, JPG, PNG (Max 5MB)</p>
                        </div>
                        <input type="file" class="form-control d-none" id="file_dokumen" name="file_dokumen" 
                               accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted d-block mt-2" id="fileName"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// ========== HOBI FUNCTIONALITY ==========
function updateHobiPreview() {
    const hobiTextarea = document.getElementById('hobi');
    const hobiPreview = document.getElementById('hobiPreview');
    
    if (!hobiTextarea || !hobiPreview) return;
    
    const hobiText = hobiTextarea.value.trim();
    
    if (hobiText === '') {
        hobiPreview.innerHTML = '';
        return;
    }
    
    const hobiList = hobiText.split(',').map(h => h.trim()).filter(h => h !== '');
    
    let html = '';
    hobiList.forEach(hobi => {
        html += `<span class="hobi-tag">
            <i class="bi bi-heart-fill"></i> ${hobi}
        </span>`;
    });
    
    hobiPreview.innerHTML = html;
}

function addHobi(newHobi) {
    const hobiTextarea = document.getElementById('hobi');
    const currentHobi = hobiTextarea.value.trim();
    
    // Check jika hobi sudah ada
    const hobiList = currentHobi.split(',').map(h => h.trim()).filter(h => h !== '');
    
    if (hobiList.includes(newHobi)) {
        // Hobi sudah ada, tampilkan notifikasi
        const btn = event.target;
        btn.style.background = '#f59e0b';
        btn.style.color = 'white';
        btn.style.borderColor = '#f59e0b';
        setTimeout(() => {
            btn.style.background = '';
            btn.style.color = '';
            btn.style.borderColor = '';
        }, 300);
        return;
    }
    
    // Tambah hobi baru
    if (currentHobi === '') {
        hobiTextarea.value = newHobi;
    } else {
        hobiTextarea.value = currentHobi + ', ' + newHobi;
    }
    
    updateHobiPreview();
    
    // Animasi tombol
    const btn = event.target;
    btn.style.background = 'var(--campus-green)';
    btn.style.color = 'white';
    btn.style.borderColor = 'var(--campus-green)';
    btn.style.transform = 'scale(0.95)';
    setTimeout(() => {
        btn.style.transform = 'scale(1)';
    }, 200);
}

// Handle file input change
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file_dokumen');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const fileNameDisplay = document.getElementById('fileName');
            if (fileName) {
                fileNameDisplay.textContent = '📄 File dipilih: ' + fileName;
                fileNameDisplay.style.color = 'var(--campus-green)';
                fileNameDisplay.style.fontWeight = '600';
            }
        });
    }
    
    // Update hobi preview saat halaman load
    const hobiTextarea = document.getElementById('hobi');
    if (hobiTextarea) {
        updateHobiPreview();
        
        // Update preview saat mengetik
        hobiTextarea.addEventListener('input', updateHobiPreview);
    }
});
</script>

<?php 
// Tutup koneksi
$conn->close();
require 'templates/footer.php'; 
?>