<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Tentukan judul halaman
$page_title = 'Dashboard Dosen';

// Include config (otomatis deteksi XAMPP atau InfinityFree)
require_once 'config.php';
require 'templates/header.php';

// Keamanan
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$id_dosen_login = $_SESSION['user_id'];


// === KUMPULKAN SEMUA DATA UNTUK DASHBOARD ===
$total_mahasiswa_result = $conn->query("SELECT COUNT(nim) as total FROM mahasiswa WHERE id_dosen_pa = {$id_dosen_login}");
$total_mahasiswa = $total_mahasiswa_result->fetch_assoc()['total'];

$perlu_perhatian_result = $conn->query("SELECT COUNT(nim) as total FROM mahasiswa WHERE id_dosen_pa = {$id_dosen_login} AND (ipk < 2.75 OR status = 'Non-Aktif')");
$perlu_perhatian = $perlu_perhatian_result->fetch_assoc()['total'];

// [MODIFIKASI] Gabungkan notifikasi logbook + konsultasi judul
$notif_logbook_result = $conn->query("SELECT COUNT(DISTINCT nim_mahasiswa) as total FROM logbook WHERE id_dosen = {$id_dosen_login} AND pengisi = 'Mahasiswa' AND status_baca = 'Belum Dibaca'");
$notif_logbook = $notif_logbook_result->fetch_assoc()['total'];

// [KODE BARU] Hitung konsultasi judul yang belum ditanggapi
$notif_konsultasi_judul = 0;
if($conn->query("SHOW TABLES LIKE 'konsultasi_judul'")->num_rows > 0) {
    $notif_konsultasi_result = $conn->query("SELECT COUNT(*) as total FROM konsultasi_judul kj JOIN mahasiswa m ON kj.nim = m.nim WHERE m.id_dosen_pa = {$id_dosen_login} AND kj.status = 'Menunggu'");
    $notif_konsultasi_judul = $notif_konsultasi_result->fetch_assoc()['total'];
}

// [KODE BARU] Total notifikasi = logbook + konsultasi judul
$total_notifikasi = $notif_logbook + $notif_konsultasi_judul;

$mahasiswa_aktif_result = $conn->query("SELECT COUNT(nim) as total FROM mahasiswa WHERE id_dosen_pa = {$id_dosen_login} AND status = 'Aktif'");
$mahasiswa_aktif = $mahasiswa_aktif_result->fetch_assoc()['total'];

// Data untuk Daftar Mahasiswa Bimbingan (dengan filter dan search)
$params = [];
$types = "";

$sql_main = "SELECT nim, nama_mahasiswa, ipk, status, angkatan, krs_disetujui FROM mahasiswa WHERE id_dosen_pa = ?";
$params[] = $id_dosen_login;
$types .= "i";

if (!empty($_GET['search'])) {
    $search_term = "%" . $_GET['search'] . "%";
    $sql_main .= " AND (nama_mahasiswa LIKE ? OR nim LIKE ?)";
    array_push($params, $search_term, $search_term);
    $types .= "ss";
}
if (!empty($_GET['angkatan'])) {
    $sql_main .= " AND angkatan = ?";
    $params[] = $_GET['angkatan'];
    $types .= "i";
}
$sql_main .= " ORDER BY nama_mahasiswa ASC";

$stmt_main = $conn->prepare($sql_main);
if ($stmt_main) {
    if (count($params) > 1) {
        $stmt_main->bind_param($types, ...$params);
    } else {
        $stmt_main->bind_param($types, $id_dosen_login);
    }
    $stmt_main->execute();
    $result_mahasiswa = $stmt_main->get_result();
}

$result_angkatan = $conn->query("SELECT DISTINCT angkatan FROM mahasiswa WHERE id_dosen_pa = {$id_dosen_login} ORDER BY angkatan DESC");
$result_sidebar_logbook = $conn->query("SELECT m.nim, m.nama_mahasiswa FROM logbook l JOIN mahasiswa m ON l.nim_mahasiswa = m.nim WHERE l.id_dosen = {$id_dosen_login} AND l.pengisi = 'Mahasiswa' AND l.status_baca = 'Belum Dibaca' GROUP BY m.nim ORDER BY MAX(l.created_at) DESC LIMIT 5");

// [KODE BARU] Ambil data konsultasi judul yang menunggu
$result_sidebar_konsultasi = null;
if($conn->query("SHOW TABLES LIKE 'konsultasi_judul'")->num_rows > 0) {
    $result_sidebar_konsultasi = $conn->query("SELECT kj.nim, m.nama_mahasiswa, kj.judul_usulan, kj.tanggal_pengajuan FROM konsultasi_judul kj JOIN mahasiswa m ON kj.nim = m.nim WHERE m.id_dosen_pa = {$id_dosen_login} AND kj.status = 'Menunggu' ORDER BY kj.tanggal_pengajuan DESC LIMIT 5");
}

$result_sidebar_bermasalah = $conn->query("SELECT nim, nama_mahasiswa, ipk FROM mahasiswa WHERE id_dosen_pa = {$id_dosen_login} AND (ipk < 2.75 OR status = 'Non-Aktif') ORDER BY ipk ASC LIMIT 5");
?>

<style>
    /* ========== IMPROVED STYLES ========== */
    :root {
        --campus-green: #049D6F;
        --smart-blue: #0d6efd;
        --danger-red: #dc3545;
        --warning-orange: #fd7e14;
        --success-green: #198754;
        --purple: #6f42c1;
        --light-bg: #f0f2f5;
        --card-shadow: 0 2px 12px rgba(0,0,0,0.08);
        --card-shadow-hover: 0 8px 24px rgba(0,0,0,0.12);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body { 
        background-color: var(--light-bg);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    /* ========== SUMMARY CARDS - IMPROVED ========== */
    .summary-card { 
        border-radius: 1rem;
        color: white;
        padding: 1.75rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: var(--transition);
        box-shadow: var(--card-shadow);
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .summary-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.1);
        opacity: 0;
        transition: var(--transition);
    }
    
    .summary-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--card-shadow-hover);
    }
    
    .summary-card:hover::before {
        opacity: 1;
    }
    
    .summary-card .icon {
        position: relative;
        z-index: 1;
    }
    
    .summary-card .icon i { 
        font-size: 3rem;
        opacity: 0.3;
        transition: var(--transition);
    }
    
    .summary-card:hover .icon i {
        opacity: 0.5;
        transform: scale(1.1);
    }
    
    .summary-card .data {
        position: relative;
        z-index: 1;
    }
    
    .summary-card .data h3 { 
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        line-height: 1;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .summary-card .data p { 
        margin: 0.5rem 0 0 0;
        opacity: 0.95;
        font-size: 0.95rem;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    /* Gradient backgrounds */
    .bg-gradient-blue { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .bg-gradient-red { 
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .bg-gradient-purple { 
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .bg-gradient-green { 
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    /* ========== CARD IMPROVEMENTS ========== */
    .card {
        border: none;
        border-radius: 1rem;
        box-shadow: var(--card-shadow);
        transition: var(--transition);
    }
    
    .card:hover {
        box-shadow: var(--card-shadow-hover);
    }
    
    .card-header {
        background-color: white;
        border-bottom: 2px solid #f0f2f5;
        padding: 1.25rem 1.5rem;
        border-top-left-radius: 1rem !important;
        border-top-right-radius: 1rem !important;
    }
    
    .card-header h5, .card-header h6 {
        margin: 0;
        font-weight: 700;
        color: #2d3748;
    }

    /* ========== SEARCH FORM IMPROVEMENTS ========== */
    .search-container {
        position: relative;
    }
    
    .search-container .form-control {
        padding-right: 2.5rem;
        border-radius: 0.5rem;
        border: 2px solid #e2e8f0;
        transition: var(--transition);
    }
    
    .search-container .form-control:focus {
        border-color: var(--campus-green);
        box-shadow: 0 0 0 0.2rem rgba(4, 157, 111, 0.15);
    }
    
    .search-container .clear-search {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #cbd5e0;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        z-index: 10;
        transition: var(--transition);
    }
    
    .search-container .clear-search:hover {
        color: var(--danger-red);
    }
    
    .form-select {
        border-radius: 0.5rem;
        border: 2px solid #e2e8f0;
        transition: var(--transition);
    }
    
    .form-select:focus {
        border-color: var(--campus-green);
        box-shadow: 0 0 0 0.2rem rgba(4, 157, 111, 0.15);
    }

    /* ========== TABLE IMPROVEMENTS ========== */
    .table-responsive {
        border-radius: 0.5rem;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead th {
        background-color: #f8f9fa;
        color: #4a5568;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        padding: 1rem 0.75rem;
        white-space: nowrap;
    }
    
    .table tbody tr {
        transition: var(--transition);
    }
    
    .table tbody tr:hover {
        background-color: #f7fafc;
        transform: scale(1.005);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f2f5;
    }

    /* ========== BADGE IMPROVEMENTS ========== */
    .badge {
        padding: 0.5rem 0.85rem;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.3px;
        border-radius: 0.5rem;
    }
    
    .badge.bg-success {
        background-color: #10b981 !important;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }
    
    .badge.bg-warning {
        background-color: #f59e0b !important;
        color: white !important;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
    }
    
    .badge.bg-danger {
        background-color: #ef4444 !important;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    .badge.bg-info {
        background-color: #3b82f6 !important;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    /* ========== BUTTON IMPROVEMENTS (3 TOMBOL HORIZONTAL) ========== */
    .btn {
        border-radius: 0.5rem;
        font-weight: 600;
        padding: 0.5rem 0.75rem;
        transition: var(--transition);
        border: none;
        font-size: 0.85rem;
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
    
    .btn-outline-primary {
        color: var(--campus-green);
        border: 2px solid var(--campus-green);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--campus-green);
        border-color: var(--campus-green);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(4, 157, 111, 0.3);
    }
    
    /* Tombol Warning & Danger */
    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
    }
    
    .btn-warning:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
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
    
    /* Button Group untuk aksi - Horizontal Layout */
    .action-buttons {
        display: flex;
        gap: 0.35rem;
        flex-wrap: nowrap;
        justify-content: center;
    }
    
    .action-buttons .btn {
        padding: 0.45rem 0.65rem;
        white-space: nowrap;
    }
    
    .action-buttons .btn i {
        margin-right: 0.25rem;
    }

    /* ========== SIDEBAR WIDGET IMPROVEMENTS ========== */
    .sidebar-widget {
        transition: var(--transition);
    }
    
    .sidebar-widget:hover {
        transform: translateY(-4px);
    }
    
    .sidebar-widget .list-group-item {
        border: none;
        border-bottom: 1px solid #f0f2f5;
        padding: 1rem 1.25rem;
        transition: var(--transition);
    }
    
    .sidebar-widget .list-group-item:last-child {
        border-bottom: none;
    }
    
    .sidebar-widget .list-group-item-action:hover {
        background-color: #f7fafc;
        transform: translateX(8px);
        padding-left: 1.5rem;
    }
    
    .sidebar-widget .badge {
        font-size: 0.7rem;
        padding: 0.35rem 0.65rem;
    }

    /* [KODE BARU] Style untuk item konsultasi judul */
    .konsultasi-item {
        border-left: 3px solid #3b82f6 !important;
    }

    .konsultasi-item .judul-usulan {
        font-size: 0.85rem;
        color: #4b5563;
        margin-top: 0.25rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* ========== EMPTY STATE ========== */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #cbd5e0;
        margin-bottom: 1rem;
    }
    
    .empty-state h6 {
        color: #718096;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: #a0aec0;
        font-size: 0.9rem;
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
        width: 14px;
        height: 14px;
        top: 50%;
        right: 8px;
        margin-top: -7px;
        border: 2px solid currentColor;
        border-radius: 50%;
        border-right-color: transparent;
        animation: spinner-border 0.75s linear infinite;
    }

    @keyframes spinner-border {
        to { transform: rotate(360deg); }
    }

    /* ========== RESPONSIVE IMPROVEMENTS ========== */
    @media (max-width: 1200px) {
        /* Hide text on medium screens, show icon only */
        .action-buttons .btn span.btn-text {
            display: none;
        }
        
        .action-buttons .btn {
            padding: 0.45rem 0.55rem;
        }
    }

    @media (max-width: 768px) {
        .summary-card {
            padding: 1.25rem 1rem;
        }
        
        .summary-card .data h3 {
            font-size: 2rem;
        }
        
        .summary-card .icon i {
            font-size: 2.25rem;
        }
        
        .table {
            font-size: 0.85rem;
        }
        
        .table thead th,
        .table tbody td {
            padding: 0.75rem 0.5rem;
        }
        
        .action-buttons {
            gap: 0.25rem;
        }
        
        .action-buttons .btn {
            padding: 0.4rem 0.5rem;
            font-size: 0.8rem;
        }
        
        /* Make table scroll better on mobile */
        .table-responsive {
            -webkit-overflow-scrolling: touch;
        }
        
        /* Stack form elements on mobile */
        .form-control,
        .form-select,
        .btn {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        .summary-card .data h3 {
            font-size: 1.75rem;
        }
        
        .summary-card .data p {
            font-size: 0.85rem;
        }
        
        .action-buttons .btn {
            padding: 0.35rem 0.45rem;
            font-size: 0.75rem;
        }
    }
</style>

<div class="container-fluid my-4">
    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="summary-card bg-gradient-blue shadow-sm">
                <div class="data">
                    <h3><?= $total_mahasiswa; ?></h3>
                    <p>Total Mahasiswa</p>
                </div>
                <div class="icon">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="summary-card bg-gradient-red shadow-sm">
                <div class="data">
                    <h3><?= $perlu_perhatian; ?></h3>
                    <p>Perlu Perhatian</p>
                </div>
                <div class="icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="summary-card bg-gradient-purple shadow-sm">
                <div class="data">
                    <h3><?= $total_notifikasi; ?></h3>
                    <p>Notifikasi</p>
                </div>
                <div class="icon">
                    <i class="bi bi-bell-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="summary-card bg-gradient-green shadow-sm">
                <div class="data">
                    <h3><?= $mahasiswa_aktif; ?></h3>
                    <p>Mahasiswa Aktif</p>
                </div>
                <div class="icon">
                    <i class="bi bi-person-check-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-people-fill me-2" style="color: var(--campus-green);"></i>Daftar Mahasiswa Bimbingan</h5>
                </div>
                <div class="card-body">
                    <!-- Search & Filter Form -->
                    <form action="dashboard_dosen.php" method="GET" class="row g-3 mb-4" id="searchForm">
                        <div class="col-md-5">
                            <div class="search-container">
                                <input type="text" 
                                       name="search" 
                                       id="searchInput"
                                       class="form-control" 
                                       placeholder="🔍 Cari nama atau NIM..." 
                                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                <?php if (!empty($_GET['search'])): ?>
                                <button type="button" class="clear-search" onclick="clearSearch()">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="angkatan" class="form-select">
                                <option value="">📅 Semua Angkatan</option>
                                <?php 
                                mysqli_data_seek($result_angkatan, 0);
                                while($angkatan = $result_angkatan->fetch_assoc()): 
                                ?>
                                <option value="<?= $angkatan['angkatan'] ?>" 
                                        <?= (($_GET['angkatan'] ?? '') == $angkatan['angkatan']) ? 'selected' : '' ?>>
                                    Angkatan <?= $angkatan['angkatan'] ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100" id="searchBtn">
                                <i class="bi bi-search me-1"></i> Cari
                            </button>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>NIM</th>
                                    <th class="text-center">IPK</th>
                                    <th class="text-center">Status Mhs</th>
                                    <th class="text-center">Status KRS</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($result_mahasiswa) && $result_mahasiswa->num_rows > 0): 
                                    while($mhs = $result_mahasiswa->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong class="d-block"><?= htmlspecialchars($mhs['nama_mahasiswa']); ?></strong>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar3 me-1"></i>Angkatan <?= $mhs['angkatan']; ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td><span class="text-monospace"><?= htmlspecialchars($mhs['nim']); ?></span></td>
                                    <td class="text-center">
                                        <span class="badge <?= ($mhs['ipk'] >= 3.0) ? 'bg-success' : (($mhs['ipk'] >= 2.75) ? 'bg-warning' : 'bg-danger'); ?>">
                                            <?= number_format($mhs['ipk'], 2); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= ($mhs['status'] == 'Aktif') ? 'bg-success' : 'bg-danger'; ?>">
                                            <?= htmlspecialchars($mhs['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= ($mhs['krs_disetujui'] == 1) ? 'bg-success' : 'bg-warning'; ?>">
                                            <?= ($mhs['krs_disetujui'] == 1) ? '✓ Disetujui' : '⏳ Belum'; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <!-- 3 TOMBOL HORIZONTAL - LEBIH CLEAN -->
                                        <div class="action-buttons">
                                            <!-- Tombol Detail -->
                                            <a href="detail_mahasiswa.php?nim=<?= $mhs['nim']; ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Lihat Detail Mahasiswa">
                                                <i class="bi bi-eye"></i>
                                                <span class="btn-text">Detail</span>
                                            </a>
                                            
                                            <!-- Tombol KRS (Setujui/Batalkan) -->
                                            <?php if ($mhs['krs_disetujui'] == 0): ?>
                                            <a href="update_status.php?nim=<?= $mhs['nim']; ?>&action=setujui_krs" 
                                               class="btn btn-sm btn-primary"
                                               onclick="return confirm('Setujui KRS untuk <?= htmlspecialchars($mhs['nama_mahasiswa']); ?>?')"
                                               title="Setujui KRS">
                                                <i class="bi bi-check-circle"></i>
                                                <span class="btn-text">KRS</span>
                                            </a>
                                            <?php else: ?>
                                            <a href="update_status.php?nim=<?= $mhs['nim']; ?>&action=tolak_krs" 
                                               class="btn btn-sm btn-warning"
                                               onclick="return confirm('Batalkan persetujuan KRS untuk <?= htmlspecialchars($mhs['nama_mahasiswa']); ?>?')"
                                               title="Batalkan Persetujuan KRS">
                                                <i class="bi bi-x-circle"></i>
                                                <span class="btn-text">Batalkan</span>
                                            </a>
                                            <?php endif; ?>
                                            
                                            <!-- Tombol Status (Aktif/Non-Aktif) -->
                                            <?php if ($mhs['status'] == 'Aktif'): ?>
                                            <a href="update_status.php?nim=<?= $mhs['nim']; ?>&action=set_status_nonaktif" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Non-aktifkan mahasiswa <?= htmlspecialchars($mhs['nama_mahasiswa']); ?>?')"
                                               title="Non-Aktifkan Mahasiswa">
                                                <i class="bi bi-person-x"></i>
                                                <span class="btn-text">Non-Aktif</span>
                                            </a>
                                            <?php else: ?>
                                            <a href="update_status.php?nim=<?= $mhs['nim']; ?>&action=set_status_aktif" 
                                               class="btn btn-sm btn-primary"
                                               onclick="return confirm('Aktifkan kembali mahasiswa <?= htmlspecialchars($mhs['nama_mahasiswa']); ?>?')"
                                               title="Aktifkan Mahasiswa">
                                                <i class="bi bi-person-check"></i>
                                                <span class="btn-text">Aktifkan</span>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center p-0">
                                        <div class="empty-state">
                                            <i class="bi bi-inbox"></i>
                                            <h6>Tidak Ada Data</h6>
                                            <p>Tidak ada mahasiswa yang sesuai dengan kriteria pencarian.</p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- [MODIFIKASI] Widget Notifikasi (Logbook + Konsultasi Judul) -->
            <div class="card shadow-sm mb-4 sidebar-widget">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-bell-fill me-2" style="color: var(--campus-green);"></i>
                        Notifikasi
                        <?php if ($total_notifikasi > 0): ?>
                        <span class="badge bg-danger rounded-pill float-end"><?= $total_notifikasi ?></span>
                        <?php endif; ?>
                    </h6>
                </div>
                <ul class="list-group list-group-flush">
                    <?php 
                    $has_notification = false;
                    
                    // Tampilkan Konsultasi Judul yang Menunggu
                    if ($result_sidebar_konsultasi && $result_sidebar_konsultasi->num_rows > 0): 
                        $has_notification = true;
                        while($konsul = $result_sidebar_konsultasi->fetch_assoc()): 
                    ?>
                    <a href="detail_mahasiswa.php?nim=<?= $konsul['nim'] ?>" 
                       class="list-group-item list-group-item-action konsultasi-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-chat-square-text me-2" style="color: #3b82f6;"></i>
                                    <strong><?= htmlspecialchars($konsul['nama_mahasiswa']); ?></strong>
                                </div>
                                <div class="judul-usulan">
                                    <?= htmlspecialchars($konsul['judul_usulan']); ?>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> <?= date('d M Y', strtotime($konsul['tanggal_pengajuan'])); ?>
                                </small>
                            </div>
                            <span class="badge bg-info ms-2">Konsultasi</span>
                        </div>
                    </a>
                    <?php 
                        endwhile;
                    endif;
                    
                    // Tampilkan Logbook yang Belum Dibaca
                    if ($result_sidebar_logbook->num_rows > 0): 
                        $has_notification = true;
                        mysqli_data_seek($result_sidebar_logbook, 0);
                        while($log = $result_sidebar_logbook->fetch_assoc()): 
                    ?>
                    <a href="detail_mahasiswa.php?nim=<?= $log['nim'] ?>" 
                       class="list-group-item list-group-item-action">
                        <span>
                            <i class="bi bi-journal-text me-2 text-muted"></i>
                            <?= htmlspecialchars($log['nama_mahasiswa']); ?>
                        </span>
                        <span class="badge bg-warning float-end">Logbook</span>
                    </a>
                    <?php 
                        endwhile;
                    endif;
                    
                    // Jika tidak ada notifikasi sama sekali
                    if (!$has_notification): 
                    ?>
                    <li class="list-group-item text-center">
                        <div class="empty-state py-3">
                            <i class="bi bi-check-circle" style="font-size: 2.5rem;"></i>
                            <p class="mb-0 small">Tidak ada notifikasi baru</p>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Mahasiswa Bermasalah Widget -->
            <div class="card shadow-sm sidebar-widget">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-2" style="color: var(--danger-red);"></i>
                        Mahasiswa Bermasalah
                        <?php if ($perlu_perhatian > 0): ?>
                        <span class="badge bg-danger rounded-pill float-end"><?= $perlu_perhatian ?></span>
                        <?php endif; ?>
                    </h6>
                </div>
                <ul class="list-group list-group-flush">
                    <?php if ($result_sidebar_bermasalah->num_rows > 0): 
                        while($prob = $result_sidebar_bermasalah->fetch_assoc()): 
                    ?>
                    <a href="detail_mahasiswa.php?nim=<?= $prob['nim'] ?>" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>
                            <i class="bi bi-person-fill me-2 text-muted"></i>
                            <?= htmlspecialchars($prob['nama_mahasiswa']); ?>
                        </span>
                        <span class="badge bg-danger rounded-pill">IPK <?= number_format($prob['ipk'], 2) ?></span>
                    </a>
                    <?php endwhile; else: ?>
                    <li class="list-group-item text-center">
                        <div class="empty-state py-3">
                            <i class="bi bi-emoji-smile" style="font-size: 2.5rem;"></i>
                            <p class="mb-0 small">Semua mahasiswa dalam kondisi baik</p>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- Widget Prestasi Mahasiswa (Tambahkan di dashboard_dosen.php) -->
<div class="card shadow-sm sidebar-widget mt-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="bi bi-trophy-fill me-2" style="color: #f59e0b;"></i>
            Prestasi Mahasiswa Terbaru
        </h6>
    </div>
    <ul class="list-group list-group-flush">
        <?php 
        // Query prestasi terbaru
        $result_prestasi = $conn->query("
            SELECT dp.*, m.nama_mahasiswa 
            FROM dokumen_prestasi dp 
            JOIN mahasiswa m ON dp.nim = m.nim 
            WHERE m.id_dosen_pa = {$id_dosen_login} 
            ORDER BY dp.tanggal_upload DESC 
            LIMIT 5
        ");
        
        if ($result_prestasi->num_rows > 0): 
            while($prestasi = $result_prestasi->fetch_assoc()): 
        ?>
        <a href="assets/uploads/prestasi/<?= htmlspecialchars($prestasi['file_dokumen']); ?>" 
           target="_blank"
           class="list-group-item list-group-item-action">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <i class="bi bi-trophy me-2 text-warning"></i>
                    <strong><?= htmlspecialchars($prestasi['nama_mahasiswa']); ?></strong>
                    <div class="small text-muted mt-1">
                        <?= htmlspecialchars($prestasi['nama_prestasi']); ?>
                    </div>
                    <div class="small text-muted">
                        <i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($prestasi['tanggal_upload'])); ?>
                    </div>
                </div>
                <span class="badge bg-warning text-dark"><?= htmlspecialchars($prestasi['jenis_prestasi']); ?></span>
            </div>
        </a>
        <?php endwhile; else: ?>
        <li class="list-group-item text-center">
            <div class="empty-state py-3">
                <i class="bi bi-trophy" style="font-size: 2.5rem;"></i>
                <p class="mb-0 small">Belum ada prestasi yang diupload</p>
            </div>
        </li>
        <?php endif; ?>
    </ul>
</div>

<script>
// Clear search functionality
function clearSearch() {
    document.getElementById('searchInput').value = '';
    document.getElementById('searchForm').submit();
}

// Loading state for search button
document.getElementById('searchForm').addEventListener('submit', function() {
    const btn = document.getElementById('searchBtn');
    btn.classList.add('btn-loading');
    btn.disabled = true;
});

// Auto-submit on angkatan change
document.querySelector('select[name="angkatan"]').addEventListener('change', function() {
    document.getElementById('searchForm').submit();
});
</script>

<?php 
if (isset($stmt_main)) $stmt_main->close();
$conn->close();
require 'templates/footer.php'; 
?>