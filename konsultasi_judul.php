<?php
$page_title = 'Konsultasi Judul';
require_once 'config.php';
require 'templates/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'mahasiswa') {
    header("Location: login.php");
    exit();
}

$nim = $_SESSION['user_id'];

// Ambil data konsultasi judul mahasiswa
$stmt = $conn->prepare("
    SELECT k.*, d.nama_dosen 
    FROM konsultasi_judul k
    LEFT JOIN dosen d ON k.id_dosen = d.id_dosen
    WHERE k.nim = ?
    ORDER BY k.tanggal_pengajuan DESC
");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
$konsultasi_list = [];
while ($row = $result->fetch_assoc()) {
    $konsultasi_list[] = $row;
}
$stmt->close();

// Notifikasi
$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<style>
    :root {
        --campus-green: #049D6F;
        --card-shadow: 0 2px 12px rgba(0,0,0,0.08);
        --card-shadow-hover: 0 8px 24px rgba(0,0,0,0.12);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --card-radius: 1rem;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
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

    .konsultasi-item {
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: var(--transition);
        background: white;
    }

    .konsultasi-item:hover {
        border-color: var(--campus-green);
        box-shadow: 0 4px 12px rgba(4, 157, 111, 0.15);
        transform: translateY(-3px);
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-menunggu { background: #fef3c7; color: #92400e; }
    .status-disetujui { background: #d1fae5; color: #065f46; }
    .status-ditolak { background: #fee2e2; color: #991b1b; }
    .status-revisi { background: #dbeafe; color: #1e40af; }

    .alert {
        border-radius: 0.75rem;
        border: none;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        animation: slideInDown 0.4s ease-out;
    }

    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .alert-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

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

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state i {
        font-size: 5rem;
        color: #cbd5e0;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .page-header h2 {
            font-size: 1.5rem;
        }
    }
</style>

<div class="container my-4">
    <!-- PAGE HEADER -->
    <div class="page-header">
        <h2>
            <i class="bi bi-chat-square-text"></i>
            Konsultasi Judul
        </h2>
        <a href="dashboard_mahasiswa.php" class="back-button">
            <i class="bi bi-arrow-left-circle"></i>
            Kembali
        </a>
    </div>

    <!-- NOTIFIKASI -->
    <?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= htmlspecialchars($success_message); ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars($error_message); ?>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <!-- FORM PENGAJUAN -->
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%); color: white; padding: 1.5rem;">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Ajukan Judul Baru</h5>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <form action="submit_konsultasi_judul.php" method="POST">
                        <div class="mb-3">
                            <label for="judul_usulan" class="form-label fw-bold">
                                <i class="bi bi-pencil-square text-primary me-2"></i>Judul Usulan
                            </label>
                            <input type="text" class="form-control" id="judul_usulan" name="judul_usulan" 
                                   placeholder="Contoh: Sistem Informasi Akademik Berbasis Web" required>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label fw-bold">
                                <i class="bi bi-file-text text-success me-2"></i>Deskripsi/Latar Belakang
                            </label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" 
                                      placeholder="Jelaskan latar belakang dan tujuan penelitian Anda..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Ajukan Judul
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- RIWAYAT KONSULTASI -->
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem;">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Konsultasi</h5>
                </div>
                <div class="card-body" style="padding: 2rem;">
                    <?php if (count($konsultasi_list) > 0): ?>
                        <?php foreach ($konsultasi_list as $konsultasi): ?>
                        <div class="konsultasi-item">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h5 class="mb-2 fw-bold"><?= htmlspecialchars($konsultasi['judul_usulan']); ?></h5>
                                    <p class="text-muted mb-2">
                                        <i class="bi bi-calendar3"></i> 
                                        <?= date('d F Y, H:i', strtotime($konsultasi['tanggal_pengajuan'])); ?> WIB
                                    </p>
                                </div>
                                <span class="status-badge status-<?= strtolower($konsultasi['status']); ?>">
                                    <?= $konsultasi['status']; ?>
                                </span>
                            </div>

                            <?php if (!empty($konsultasi['deskripsi'])): ?>
                            <div class="mb-3">
                                <strong>Deskripsi:</strong>
                                <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($konsultasi['deskripsi'])); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($konsultasi['catatan_dosen'])): ?>
                            <div class="alert alert-info mb-0">
                                <strong><i class="bi bi-chat-left-quote"></i> Tanggapan Dosen PA:</strong>
                                <p class="mb-1 mt-2"><?= nl2br(htmlspecialchars($konsultasi['catatan_dosen'])); ?></p>
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($konsultasi['nama_dosen'] ?? 'Dosen PA'); ?> - 
                                    <?= date('d F Y, H:i', strtotime($konsultasi['tanggal_respon'])); ?> WIB
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <h5 class="text-muted">Belum Ada Riwayat Konsultasi</h5>
                            <p class="text-muted">Ajukan judul penelitian Anda di form di atas.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$conn->close();
require 'templates/footer.php'; 
?>