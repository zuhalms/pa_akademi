<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Tentukan judul halaman sebelum memanggil header
$page_title = 'Dashboard Mahasiswa';

// Include config (otomatis XAMPP atau InfinityFree)
require_once 'config.php';
require 'templates/header.php';

// Keamanan: Pastikan yang mengakses adalah mahasiswa
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa') {
    header("Location: login.php");
    exit();
}

// CATATAN: $conn sudah dibuat di header.php, langsung pakai saja
// JANGAN BUAT KONEKSI LAGI DI SINI!

$nim_mahasiswa_login = $_SESSION['user_id'];
$pesan_sukses_logbook = '';
$pesan_sukses_evaluasi = '';

$current_year = date('Y');
$current_month = date('n');
$periode_sekarang = $current_year . ' ' . (($current_month >= 2 && $current_month <= 7) ? 'Genap' : 'Ganjil');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit_logbook_mahasiswa'])) {
        $id_dosen = $_POST['id_dosen']; 
        $tanggal = $_POST['tanggal_bimbingan'];
        $topik = $_POST['topik_bimbingan']; 
        $isi = $_POST['isi_bimbingan'];
        $stmt_insert = $conn->prepare("INSERT INTO logbook (nim_mahasiswa, id_dosen, pengisi, tanggal_bimbingan, topik_bimbingan, isi_bimbingan) VALUES (?, ?, 'Mahasiswa', ?, ?, ?)");
        $stmt_insert->bind_param("sisss", $nim_mahasiswa_login, $id_dosen, $tanggal, $topik, $isi);
        if ($stmt_insert->execute()) { 
            $pesan_sukses_logbook = "Catatan bimbingan Anda berhasil disimpan!"; 
        }
        $stmt_insert->close();
    } elseif (isset($_POST['submit_evaluasi_dosen'])) {
        $id_dosen = $_POST['id_dosen']; 
        $skor_komunikasi = $_POST['skor_komunikasi'];
        $skor_membantu = $_POST['skor_membantu']; 
        $skor_solusi = $_POST['skor_solusi'];
        $saran_kritik = $_POST['saran_kritik'];
        $stmt_insert = $conn->prepare("INSERT INTO evaluasi_dosen (nim_mahasiswa, id_dosen, periode_evaluasi, skor_komunikasi, skor_membantu, skor_solusi, saran_kritik) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sisiiis", $nim_mahasiswa_login, $id_dosen, $periode_sekarang, $skor_komunikasi, $skor_membantu, $skor_solusi, $saran_kritik);
        if ($stmt_insert->execute()) { 
            $pesan_sukses_evaluasi = "Terima kasih! Evaluasi Anda telah berhasil dikirim."; 
        }
        $stmt_insert->close();
    }
}

// 1. Ambil Notifikasi
$notif_krs_result = $conn->query("SELECT krs_disetujui FROM mahasiswa WHERE nim = '$nim_mahasiswa_login' AND krs_disetujui = TRUE AND krs_notif_dilihat = FALSE");
$ada_notif_krs = $notif_krs_result->num_rows > 0;
$notif_logbook_result = $conn->query("SELECT COUNT(id_log) as jumlah FROM logbook WHERE nim_mahasiswa = '$nim_mahasiswa_login' AND pengisi = 'Dosen' AND status_baca = 'Belum Dibaca'");
$notif_logbook = $notif_logbook_result->fetch_assoc();
$jumlah_notif_logbook = $notif_logbook['jumlah'];

// 2. Ambil semua data mahasiswa & dosen PA
$stmt_mhs = $conn->prepare("SELECT m.*, d.id_dosen, d.nama_dosen FROM mahasiswa m JOIN dosen d ON m.id_dosen_pa = d.id_dosen WHERE m.nim = ?");
$stmt_mhs->bind_param("s", $nim_mahasiswa_login); 
$stmt_mhs->execute();
$mahasiswa = $stmt_mhs->get_result()->fetch_assoc(); 
$stmt_mhs->close();

$foto_path = 'assets/uploads/default-profile.png';
if (!empty($mahasiswa['foto_mahasiswa']) && file_exists('assets/uploads/' . $mahasiswa['foto_mahasiswa'])) {
    $foto_path = 'assets/uploads/' . $mahasiswa['foto_mahasiswa'];
}

// 3. Ambil data untuk Kartu Statistik
$result_ips = $conn->query("SELECT ip_semester FROM riwayat_akademik WHERE nim_mahasiswa = '{$nim_mahasiswa_login}' ORDER BY semester DESC LIMIT 1");
$ips_terakhir = ($result_ips->num_rows > 0) ? $result_ips->fetch_assoc()['ip_semester'] : 0;
$result_log_count = $conn->query("SELECT COUNT(id_log) as total FROM logbook WHERE nim_mahasiswa = '{$nim_mahasiswa_login}'");
$logbook_count = $result_log_count->fetch_assoc()['total'];
$result_doc_count = $conn->query("SELECT COUNT(id_dokumen) as total FROM dokumen WHERE nim_mahasiswa = '{$nim_mahasiswa_login}'");
$dokumen_count = $result_doc_count->fetch_assoc()['total'];

// Ambil data konsultasi judul
$result_konsultasi_judul = $conn->query("SELECT COUNT(*) as total FROM konsultasi_judul WHERE nim = '{$nim_mahasiswa_login}'");
$konsultasi_judul_count = ($result_konsultasi_judul->num_rows > 0) ? $result_konsultasi_judul->fetch_assoc()['total'] : 0;

// Sisa pengambilan data
$stmt_check = $conn->prepare("SELECT id_evaluasi_dosen FROM evaluasi_dosen WHERE nim_mahasiswa = ? AND periode_evaluasi = ?");
$stmt_check->bind_param("ss", $nim_mahasiswa_login, $periode_sekarang); 
$stmt_check->execute();
$sudah_mengisi_evaluasi = $stmt_check->get_result()->num_rows > 0; 
$stmt_check->close();
$result_log = $conn->query("SELECT * FROM logbook WHERE nim_mahasiswa = '$nim_mahasiswa_login' ORDER BY tanggal_bimbingan DESC, created_at DESC");
$result_chart_data = $conn->query("SELECT semester, ip_semester FROM riwayat_akademik WHERE nim_mahasiswa = '$nim_mahasiswa_login' ORDER BY semester ASC")->fetch_all(MYSQLI_ASSOC);
$result_eval_softskill = $conn->query("SELECT * FROM evaluasi_softskill WHERE nim_mahasiswa = '$nim_mahasiswa_login' ORDER BY periode_evaluasi DESC, kategori ASC");
$evaluasi_per_periode = []; 
while($row = $result_eval_softskill->fetch_assoc()) { 
    $evaluasi_per_periode[$row['periode_evaluasi']][] = $row; 
}
$chart_labels = json_encode(array_column($result_chart_data, 'semester'));
$chart_data = json_encode(array_column($result_chart_data, 'ip_semester'));

function formatBytes($bytes, $precision = 2) { 
    $units = ['B', 'KB', 'MB', 'GB', 'TB']; 
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
    $bytes /= (1 << (10 * $pow)); 
    return round($bytes, $precision) . ' ' . $units[$pow]; 
}

$result_dokumen = $conn->query("SELECT * FROM dokumen WHERE nim_mahasiswa = '$nim_mahasiswa_login' ORDER BY tanggal_unggah DESC");
$daftar_pencapaian = ['Konsultasi Judul', 'Seminar Proposal', 'Ujian Komperehensif', 'Seminar Hasil', 'Ujian Skripsi (Yudisium)', 'Publikasi Jurnal'];
$stmt_pencapaian = $conn->prepare("SELECT nama_pencapaian, status, tanggal_selesai FROM pencapaian WHERE nim_mahasiswa = ?");
$stmt_pencapaian->bind_param("s", $nim_mahasiswa_login); 
$stmt_pencapaian->execute();
$result_pencapaian = $stmt_pencapaian->get_result();
$status_pencapaian = []; 
$jumlah_selesai = 0;
while($row = $result_pencapaian->fetch_assoc()) {
    $status_pencapaian[$row['nama_pencapaian']] = $row;
    if ($row['status'] == 'Selesai') { 
        $jumlah_selesai++; 
    }
}
$total_pencapaian = count($daftar_pencapaian);
$persentase_kemajuan = ($total_pencapaian > 0) ? round(($jumlah_selesai / $total_pencapaian) * 100) : 0;

if ($ada_notif_krs) { 
    $conn->query("UPDATE mahasiswa SET krs_notif_dilihat = TRUE WHERE nim = '$nim_mahasiswa_login'"); 
}
if ($jumlah_notif_logbook > 0) { 
    $conn->query("UPDATE logbook SET status_baca = 'Dibaca' WHERE nim_mahasiswa = '$nim_mahasiswa_login' AND pengisi = 'Dosen'"); 
}
?>

<style>
    /* ========== IMPROVED CSS DASHBOARD MAHASISWA ========== */
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
    
    /* ========== PROFILE BANNER IMPROVEMENTS ========== */
    .profile-banner {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        border: none;
        position: relative;
        overflow: hidden;
        transition: var(--transition);
    }
    
    .profile-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(4, 157, 111, 0.05) 0%, transparent 70%);
        animation: pulse 15s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1) rotate(0deg); }
        50% { transform: scale(1.1) rotate(180deg); }
    }
    
    .profile-banner:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-shadow-hover);
    }
    
    .profile-banner h2 {
        position: relative;
        z-index: 1;
        color: var(--campus-green);
        text-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .profile-banner .ipk-display {
        text-align: right;
        position: relative;
        z-index: 1;
    }
    
    .profile-banner .ipk-display h1 {
        font-size: 4rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--campus-green), #037a59);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1;
        text-shadow: 0 4px 8px rgba(4, 157, 111, 0.2);
        filter: drop-shadow(0 4px 8px rgba(4, 157, 111, 0.2));
        animation: fadeInScale 0.8s ease;
    }
    
    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    /* ========== STAT CARDS IMPROVEMENTS ========== */
    .stat-card {
        color: white;
        padding: 2rem 1.5rem;
        border-radius: var(--card-radius);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        transition: var(--transition);
        opacity: 0;
    }
    
    .stat-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }
    
    .stat-card:hover::before {
        opacity: 1;
        transform: rotate(45deg);
    }
    
    .stat-card .icon {
        font-size: 3rem;
        opacity: 0.9;
        margin-bottom: 1rem;
        transition: var(--transition);
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
        color: white;
    }
    
    .stat-card:hover .icon {
        transform: scale(1.2) rotate(10deg);
        opacity: 1;
    }
    
    .stat-card .data h1 {
        font-size: 3rem;
        font-weight: 800;
        margin: 0;
        line-height: 1;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        color: white;
    }
    
    .stat-card .data p {
        margin: 0.75rem 0 0 0;
        text-transform: uppercase;
        font-size: 0.85rem;
        font-weight: 700;
        opacity: 0.95;
        letter-spacing: 1px;
        color: white;
    }
    
    /* Gradient backgrounds with enhanced colors */
    .bg-card-purple { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .bg-card-green { 
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
    .bg-card-blue { 
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .bg-card-orange { 
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    
    /* ========== STAT CARD KONSULTASI JUDUL - HIJAU ========== */
    .stat-card-konsultasi {
        background: linear-gradient(135deg, #049D6F 0%, #037a59 100%) !important;
    }
    
    .stat-card-konsultasi .icon {
        color: white !important;
        opacity: 0.9 !important;
    }
    
    .stat-card-konsultasi .data p,
    .stat-card-konsultasi .data h1 {
        color: white !important;
        opacity: 1 !important;
        text-shadow: 0 2px 10px rgba(0,0,0,0.3) !important;
    }
    
    /* ========== CARD IMPROVEMENTS ========== */
    .card {
        border: none;
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        overflow: hidden;
    }
    
    .card:hover {
        transform: translateY(-4px);
        box-shadow: var(--card-shadow-hover);
    }
    
    .card-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-bottom: 2px solid var(--gray);
        padding: 1.5rem;
    }
    
    .card-header h5, .card-header h6 {
        margin: 0;
        font-weight: 700;
        color: var(--campus-green);
    }
    
    /* ========== NAV TABS IMPROVEMENTS ========== */
    .nav-tabs {
        border-bottom: none;
        gap: 0.5rem;
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: var(--dark-gray);
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        transition: var(--transition);
        background-color: transparent;
    }
    
    .nav-tabs .nav-link:hover {
        background-color: rgba(4, 157, 111, 0.1);
        transform: translateY(-2px);
    }
    
    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(4, 157, 111, 0.3);
    }
    
    /* ========== LOGBOOK TIMELINE IMPROVEMENTS ========== */
    .tab-pane > div[style*="border-left"] {
        transition: var(--transition);
        position: relative;
    }
    
    .tab-pane > div[style*="border-left"]::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: inherit;
        filter: brightness(1.2);
        opacity: 0;
        transition: var(--transition);
    }
    
    .tab-pane > div[style*="border-left"]:hover {
        transform: translateX(8px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .tab-pane > div[style*="border-left"]:hover::before {
        opacity: 1;
    }
    
    /* ========== PROGRESS BAR IMPROVEMENTS ========== */
    .progress {
        height: 28px;
        border-radius: 1rem;
        background-color: #e9ecef;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .progress-bar {
        background: linear-gradient(90deg, var(--campus-green), #43e97b);
        border-radius: 1rem;
        transition: width 1s ease;
        box-shadow: 0 2px 8px rgba(4, 157, 111, 0.4);
        animation: progressFill 2s ease;
    }
    
    @keyframes progressFill {
        from { width: 0%; }
    }
    
    .progress-bar strong {
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        font-size: 0.9rem;
    }
    
    /* ========== LIST GROUP IMPROVEMENTS ========== */
    .list-group-item {
        border: none;
        border-bottom: 1px solid #f0f2f5;
        padding: 1rem 0.75rem;
        transition: var(--transition);
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
        padding-left: 1rem;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    /* Checkmark animation for completed achievements */
    .list-group-item.text-success span:first-child {
        display: inline-block;
        animation: bounceIn 0.5s ease;
    }
    
    @keyframes bounceIn {
        0% { transform: scale(0); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    
    /* ========== FORM IMPROVEMENTS ========== */
    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 2px solid var(--gray);
        transition: var(--transition);
        padding: 0.75rem 1rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--campus-green);
        box-shadow: 0 0 0 0.25rem rgba(4, 157, 111, 0.15);
        transform: translateY(-2px);
    }
    
    /* ========== BUTTON IMPROVEMENTS ========== */
    .btn {
        border-radius: 0.5rem;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
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
    
    .btn-primary {
        background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%);
        box-shadow: 0 4px 15px rgba(4, 157, 111, 0.3);
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #037a59 0%, #026146 100%);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(4, 157, 111, 0.4);
    }
    
    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
    }
    
    .btn-info:hover {
        background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(23, 162, 184, 0.4);
    }
    
    .btn-outline-danger {
        border: 2px solid #dc3545;
        color: #dc3545;
    }
    
    .btn-outline-danger:hover {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        border-color: transparent;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }
    
    .btn-outline-primary {
        border: 2px solid var(--smart-blue);
        color: var(--smart-blue);
    }
    
    .btn-outline-primary:hover {
        background: linear-gradient(135deg, var(--smart-blue) 0%, #0a58ca 100%);
        color: white;
        border-color: transparent;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }
    
    /* ========== ALERT IMPROVEMENTS ========== */
    .alert {
        border-radius: 0.75rem;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-left: 4px solid;
        animation: slideDown 0.5s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        color: #0c5460;
        border-left-color: #17a2b8;
    }
    
    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left-color: #10b981;
    }
    
    /* ========== ACCORDION IMPROVEMENTS ========== */
    .accordion-item {
        border: none;
        margin-bottom: 1rem;
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }
    
    .accordion-button {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        color: var(--campus-green);
        font-weight: 700;
        padding: 1.25rem 1.5rem;
        transition: var(--transition);
    }
    
    .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(4, 157, 111, 0.3);
    }
    
    .accordion-button:hover {
        background: linear-gradient(135deg, rgba(4, 157, 111, 0.1) 0%, rgba(4, 157, 111, 0.05) 100%);
    }
    
    .accordion-button:not(.collapsed):hover {
        background: linear-gradient(135deg, #037a59 0%, #026146 100%);
    }
    
    .accordion-button::after {
        transition: var(--transition);
    }
    
    .accordion-button:not(.collapsed)::after {
        transform: rotate(180deg);
    }
    
    .accordion-body {
        padding: 1.5rem;
        background-color: #ffffff;
    }
    
    /* ========== BADGE IMPROVEMENTS ========== */
    .badge {
        padding: 0.5rem 0.85rem;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .badge.bg-primary {
        background: linear-gradient(135deg, var(--smart-blue), #0a58ca) !important;
    }
    
    .badge.bg-light {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef) !important;
        color: var(--dark-gray) !important;
    }
    
    /* ========== SCROLLBAR STYLING ========== */
    ::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    
    ::-webkit-scrollbar-track {
        background: var(--light-gray);
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, var(--campus-green), #037a59);
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #037a59, #026146);
    }
    
    /* ========== RESPONSIVE IMPROVEMENTS ========== */
    @media (max-width: 992px) {
        .profile-banner .ipk-display h1 {
            font-size: 3rem;
        }
        
        .stat-card .data h1 {
            font-size: 2.5rem;
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
        }
    }
    
    @media (max-width: 768px) {
        body {
            background: var(--light-gray);
        }
        
        .profile-banner .ipk-display {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .profile-banner .ipk-display h1 {
            font-size: 2.5rem;
        }
        
        .stat-card {
            padding: 1.5rem 1rem;
        }
        
        .stat-card .data h1 {
            font-size: 2rem;
        }
        
        .stat-card .icon {
            font-size: 2rem;
        }
        
        .nav-tabs .nav-link {
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
        }
    }
    
    @media (max-width: 576px) {
        .profile-banner .ipk-display h1 {
            font-size: 2rem;
        }
        
        .stat-card .data h1 {
            font-size: 1.75rem;
        }
        
        .stat-card .data p {
            font-size: 0.75rem;
        }
        
        .btn {
            padding: 0.65rem 1.25rem;
            font-size: 0.9rem;
        }
    }
</style>

<div class="container my-4">

    <?php if ($ada_notif_krs || $jumlah_notif_logbook > 0): ?>
    <div class="alert alert-info mb-4">
        <h5 class="alert-heading">🔔 Pemberitahuan Baru</h5>
        <ul class="mb-0">
            <?php if ($ada_notif_krs): ?><li><strong>KRS Anda telah disetujui</strong> oleh Dosen PA.</li><?php endif; ?>
            <?php if ($jumlah_notif_logbook > 0): ?><li>Dosen PA Anda telah menambahkan <strong><?= $jumlah_notif_logbook; ?> catatan bimbingan baru</strong>.</li><?php endif; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="profile-banner p-4 shadow-sm mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($mahasiswa['nama_mahasiswa']); ?></h2>
                <p class="text-muted mb-1">NIM: <?= htmlspecialchars($mahasiswa['nim']); ?> | Angkatan: <?= htmlspecialchars($mahasiswa['angkatan']); ?></p>
                <p class="text-muted mb-0">Dosen PA: <?= htmlspecialchars($mahasiswa['nama_dosen']); ?></p>
            </div>
            <div class="col-md-4 ipk-display mt-3 mt-md-0">
                <p class="text-muted mb-1">Indeks Prestasi Kumulatif (IPK)</p>
                <h1><?= number_format($mahasiswa['ipk'], 2); ?></h1>
                <p class="text-muted mb-0">Total SKS: <?= htmlspecialchars($mahasiswa['total_sks'] ?? 'N/A'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- STAT CARDS dengan Konsultasi Judul Hijau -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card shadow-sm bg-card-green">
                <i class="bi bi-graph-up icon"></i>
                <div class="data"><p>IPS Terakhir</p><h1><?= number_format($ips_terakhir, 2); ?></h1></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm bg-card-blue">
                <i class="bi bi-journal-check icon"></i>
                <div class="data"><p>Logbook</p><h1><?= $logbook_count; ?></h1></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card shadow-sm bg-card-orange">
                <i class="bi bi-file-earmark-text icon"></i>
                <div class="data"><p>Dokumen</p><h1><?= $dokumen_count; ?></h1></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-card-konsultasi shadow-sm">
                <i class="bi bi-chat-square-text icon"></i>
                <div class="data"><p>Konsultasi Judul</p><h1><?= $konsultasi_judul_count; ?></h1></div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <ul class="nav nav-tabs card-header-tabs" id="logbookTab" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#riwayat"><i class="bi bi-list-ul me-1"></i>Riwayat</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tambah"><i class="bi bi-pencil-square me-1"></i>Tambah Catatan</button></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="logbookTabContent">
                        <div class="tab-pane fade show active" id="riwayat" role="tabpanel" style="max-height: 450px; overflow-y: auto; padding: 5px;">
                            <?php if ($result_log->num_rows > 0): mysqli_data_seek($result_log, 0); while($log = $result_log->fetch_assoc()): $is_dosen = ($log['pengisi'] == 'Dosen'); ?>
                            <div class="p-3 mb-2 rounded bg-white shadow-sm" style="border-left: 4px solid <?= $is_dosen ? '#0d6efd' : '#198754'; ?>;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 <?= $is_dosen ? 'text-primary' : 'text-success'; ?>"><?= htmlspecialchars($log['topik_bimbingan']); ?></h6>
                                    <?php if ($log['pengisi'] == 'Mahasiswa'): ?><a href="hapus_riwayat.php?id=<?= $log['id_log']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus catatan ini?');"><i class="bi bi-trash"></i></a><?php endif; ?>
                                </div>
                                <small class="text-muted"><?= date('d F Y', strtotime($log['tanggal_bimbingan'])); ?></small>
                                <small class="badge bg-light text-dark ms-2">Dicatat oleh: <?= $log['pengisi']; ?></small>
                                <hr class="my-1"><p><?= nl2br(htmlspecialchars($log['isi_bimbingan'])); ?></p>
                            </div>
                            <?php endwhile; else: ?><p class="text-center text-muted">Belum ada riwayat bimbingan.</p><?php endif; ?>
                        </div>
                        <div class="tab-pane fade" id="tambah" role="tabpanel">
                            <?php if (!empty($pesan_sukses_logbook)): ?><div class="alert alert-success"><?= $pesan_sukses_logbook; ?></div><?php endif; ?>
                            <form method="POST" action="dashboard_mahasiswa.php">
                                <input type="hidden" name="id_dosen" value="<?= $mahasiswa['id_dosen']; ?>">
                                <div class="mb-3"><label class="form-label">Tanggal Bimbingan</label><input type="date" class="form-control" name="tanggal_bimbingan" value="<?= date('Y-m-d'); ?>" required></div>
                                <div class="mb-3"><label class="form-label">Topik Utama</label><input type="text" class="form-control" name="topik_bimbingan" placeholder="Contoh: Diskusi Judul Skripsi" required></div>
                                <div class="mb-3"><label class="form-label">Catatan/Hasil Diskusi</label><textarea class="form-control" name="isi_bimbingan" rows="4" placeholder="Tuliskan poin-poin penting..." required></textarea></div>
                                <div class="d-grid"><button type="submit" name="submit_logbook_mahasiswa" class="btn btn-primary">Simpan Catatan</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- CARD KONSULTASI JUDUL BARU - HEADER HIJAU -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #049D6F 0%, #037a59 100%); color: white; border-bottom: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" style="color: white;"><i class="bi bi-chat-square-text me-2"></i>Konsultasi Judul</h5>
                        <a href="konsultasi_judul.php" class="btn btn-light btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>Ajukan Judul
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    // Ambil konsultasi judul terbaru
                    $stmt_judul = $conn->prepare("
                        SELECT * FROM konsultasi_judul 
                        WHERE nim = ? 
                        ORDER BY tanggal_pengajuan DESC 
                        LIMIT 3
                    ");
                    $stmt_judul->bind_param("s", $nim_mahasiswa_login);
                    $stmt_judul->execute();
                    $result_judul = $stmt_judul->get_result();
                    ?>

                    <?php if ($result_judul->num_rows > 0): ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php while ($judul = $result_judul->fetch_assoc()): ?>
                            <div class="p-3 mb-2 rounded bg-white shadow-sm" style="border-left: 4px solid <?= $judul['status'] == 'Disetujui' ? '#10b981' : ($judul['status'] == 'Ditolak' ? '#ef4444' : '#fbbf24'); ?>;">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0"><?= htmlspecialchars(substr($judul['judul_usulan'], 0, 50)); ?><?= strlen($judul['judul_usulan']) > 50 ? '...' : ''; ?></h6>
                                    <span class="badge bg-<?= $judul['status'] == 'Disetujui' ? 'success' : ($judul['status'] == 'Ditolak' ? 'danger' : ($judul['status'] == 'Revisi' ? 'info' : 'warning')) ?>">
                                        <?= $judul['status']; ?>
                                    </span>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3"></i> <?= date('d M Y', strtotime($judul['tanggal_pengajuan'])); ?>
                                </small>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="konsultasi_judul.php" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> Lihat Semua Konsultasi
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-lightbulb" style="font-size: 3rem; color: #cbd5e0;"></i>
                            <p class="text-muted mt-2">Belum ada konsultasi judul</p>
                            <a href="konsultasi_judul.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-circle"></i> Ajukan Judul Sekarang
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php $stmt_judul->close(); ?>
                </div>
            </div>
            
            <div class="card shadow-sm mt-4">
                <div class="card-header"><h5 class="mb-0"><i class="bi bi-folder-plus me-2"></i>Unggah & Lihat Dokumen</h5></div>
                <div class="card-body">
                    <?php if(isset($_SESSION['upload_message'])): ?>
                    <div class="alert alert-<?= $_SESSION['upload_status'] == 'success' ? 'success' : 'danger'; ?>"><?= $_SESSION['upload_message']; ?></div>
                    <?php unset($_SESSION['upload_message']); unset($_SESSION['upload_status']); endif; ?>
                    <form action="upload_dokumen.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3"><label for="judul_dokumen" class="form-label">Judul Dokumen</label><input type="text" class="form-control" id="judul_dokumen" name="judul_dokumen" placeholder="Judul dokumen yang diupload..." required></div>
                        <div class="mb-3"><label for="file_dokumen" class="form-label">Pilih File</label><input class="form-control" type="file" id="file_dokumen" name="file_dokumen" required><div class="form-text">Max 5MB (PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX)</div></div>
                        <div class="d-grid"><button type="submit" class="btn btn-primary">Unggah File</button></div>
                    </form>
                    <hr>
                    <h6 class="mb-3">Dokumen Terunggah:</h6>
                    <div style="max-height: 200px; overflow-y: auto;">
                        <?php if ($result_dokumen->num_rows > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php while($dokumen = $result_dokumen->fetch_assoc()): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div><strong class="d-block"><?= htmlspecialchars($dokumen['judul_dokumen']); ?></strong><small class="text-muted"><?= formatBytes($dokumen['ukuran_file']); ?></small></div>
                                <a href="<?= htmlspecialchars($dokumen['path_file']); ?>" class="btn btn-outline-primary btn-sm" download><i class="bi bi-download"></i></a>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                        <?php else: ?><p class="text-center text-muted small">Belum ada dokumen.</p><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-flag-fill me-2"></i>Kemajuan Pencapaian</h5>
                    <a href="cetak_pencapaian.php" class="btn btn-info btn-sm text-white" target="_blank"><i class="bi bi-printer-fill me-1"></i>Cetak</a>
                </div>
                <div class="card-body">
                    <div class="progress" role="progressbar" style="height: 20px;"><div class="progress-bar bg-success" style="width: <?= $persentase_kemajuan; ?>%" role="progressbar"><strong><?= $persentase_kemajuan; ?>%</strong></div></div>
                    <ul class="list-group list-group-flush mt-3">
                        <?php foreach ($daftar_pencapaian as $item): 
                            $is_selesai = isset($status_pencapaian[$item]) && $status_pencapaian[$item]['status'] == 'Selesai';
                            $tanggal = $is_selesai && !empty($status_pencapaian[$item]['tanggal_selesai']) ? date('d M Y', strtotime($status_pencapaian[$item]['tanggal_selesai'])) : '';
                        ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center ps-0 <?= $is_selesai ? 'text-success' : 'text-muted'; ?>">
                            <span><span class="fw-bold"><?= $is_selesai ? '✔' : '⚪'; ?></span> <?= htmlspecialchars($item); ?></span>
                            <?php if ($is_selesai): ?><small><?= $tanggal; ?></small><?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-graph-up-arrow me-2"></i>Grafik Perkembangan Studi</h6>
                    <canvas id="progressChart"></canvas>
                    <p id="chartPlaceholder" class="text-center text-muted" style="display: none;">Data riwayat akademik belum tersedia.</p>
                </div>
            </div>
            <div class="accordion" id="accordionEvaluasi">
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSatu"><i class="bi bi-award-fill me-2"></i>Evaluasi Soft Skill</button></h2>
                    <div id="collapseSatu" class="accordion-collapse collapse" data-bs-parent="#accordionEvaluasi">
                        <div class="accordion-body" style="max-height: 300px; overflow-y: auto;">
                               <?php if (!empty($evaluasi_per_periode)): foreach($evaluasi_per_periode as $periode => $evaluasi): ?>
                                <div class="mb-3"><strong>Periode: <?= htmlspecialchars($periode); ?></strong><ul class="list-group list-group-flush mt-2">
                                    <?php foreach($evaluasi as $item): ?><li class="list-group-item d-flex justify-content-between"><?= htmlspecialchars($item['kategori']); ?><span class="badge bg-primary rounded-pill">Skor: <?= $item['skor']; ?></span></li><?php endforeach; ?>
                                </ul></div>
                            <?php endforeach; else: ?><p class="text-center text-muted">Belum ada hasil evaluasi.</p><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDua"><i class="bi bi-person-check-fill me-2"></i>Evaluasi Dosen PA</button></h2>
                    <div id="collapseDua" class="accordion-collapse collapse" data-bs-parent="#accordionEvaluasi">
                        <div class="accordion-body">
                            <p class="text-muted small">Evaluasi untuk: <strong><?= htmlspecialchars($mahasiswa['nama_dosen']); ?></strong> (Periode: <strong><?= $periode_sekarang; ?></strong>)</p>
                            <?php if ($sudah_mengisi_evaluasi || !empty($pesan_sukses_evaluasi)): ?>
                                <div class="alert alert-success text-center">Terima kasih! Evaluasi Anda telah dikirim.</div>
                            <?php else: ?>
                                <form method="POST" action="dashboard_mahasiswa.php">
                                    <input type="hidden" name="id_dosen" value="<?= $mahasiswa['id_dosen']; ?>">
                                    <div class="mb-3"><label class="form-label">Kemudahan Dosen dihubungi?</label><select class="form-select" name="skor_komunikasi" required><option value="">Pilih 1-5</option><option value="1">1 (Buruk)</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5 (Sangat Baik)</option></select></div>
                                    <div class="mb-3"><label class="form-label">Seberapa membantu bimbingan?</label><select class="form-select" name="skor_membantu" required><option value="">Pilih 1-5</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select></div>
                                    <div class="mb-3"><label class="form-label">Kejelasan arahan/solusi?</label><select class="form-select" name="skor_solusi" required><option value="">Pilih 1-5</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option></select></div>
                                    <div class="mb-3"><label class="form-label">Saran/Kritik (Opsional)</label><textarea name="saran_kritik" class="form-control" rows="2"></textarea></div>
                                    <div class="d-grid"><button type="submit" name="submit_evaluasi_dosen" class="btn btn-primary">Kirim Evaluasi</button></div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartLabels = <?= $chart_labels; ?>;
    const chartData = <?= $chart_data; ?>;
    const canvas = document.getElementById('progressChart');
    const placeholder = document.getElementById('chartPlaceholder');
    
    if (chartLabels && chartLabels.length > 0) {
        canvas.style.display = 'block';
        placeholder.style.display = 'none';
        
        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(4, 157, 111, 0.8)');
        gradient.addColorStop(0.5, 'rgba(4, 157, 111, 0.4)');
        gradient.addColorStop(1, 'rgba(4, 157, 111, 0.05)');
        
        const borderGradient = ctx.createLinearGradient(0, 0, canvas.width, 0);
        borderGradient.addColorStop(0, '#049D6F');
        borderGradient.addColorStop(0.5, '#43e97b');
        borderGradient.addColorStop(1, '#38f9d7');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartLabels.map(l => 'Semester ' + l),
                datasets: [{
                    label: 'Indeks Prestasi (IP)',
                    data: chartData,
                    fill: true,
                    backgroundColor: gradient,
                    borderColor: borderGradient,
                    borderWidth: 4,
                    tension: 0.4,
                    pointBackgroundColor: '#049D6F',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 3,
                    pointRadius: 6,
                    pointHoverRadius: 10,
                    pointHoverBackgroundColor: '#43e97b',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 4,
                    pointStyle: 'circle'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#495057',
                            font: {
                                size: 14,
                                weight: 'bold',
                                family: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto'
                            },
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'rectRounded'
                        }
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: 'rgba(0, 0, 0, 0.85)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        padding: 15,
                        cornerRadius: 10,
                        displayColors: true,
                        borderColor: '#049D6F',
                        borderWidth: 2,
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y.toFixed(2);
                                
                                let performance = '';
                                if (context.parsed.y >= 3.5) {
                                    performance = ' 🌟 Sangat Baik!';
                                } else if (context.parsed.y >= 3.0) {
                                    performance = ' 👍 Baik';
                                } else if (context.parsed.y >= 2.75) {
                                    performance = ' ⚠️ Cukup';
                                } else {
                                    performance = ' ⚠️ Perlu Peningkatan';
                                }
                                
                                return label + performance;
                            },
                            afterLabel: function(context) {
                                const dataIndex = context.dataIndex;
                                if (dataIndex > 0) {
                                    const currentIP = context.parsed.y;
                                    const previousIP = context.dataset.data[dataIndex - 1];
                                    const diff = (currentIP - previousIP).toFixed(2);
                                    
                                    if (diff > 0) {
                                        return '📈 Naik: +' + diff;
                                    } else if (diff < 0) {
                                        return '📉 Turun: ' + diff;
                                    } else {
                                        return '➡️ Stabil';
                                    }
                                }
                                return '';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 4.0,
                        min: 0,
                        ticks: {
                            stepSize: 0.5,
                            callback: function(value) {
                                return value.toFixed(1);
                            },
                            color: '#6c757d',
                            font: {
                                size: 12,
                                weight: '600'
                            },
                            padding: 10
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)',
                            drawBorder: false,
                            lineWidth: 1
                        },
                        title: {
                            display: true,
                            text: 'Indeks Prestasi (IP)',
                            color: '#049D6F',
                            font: {
                                size: 13,
                                weight: 'bold'
                            },
                            padding: 10
                        }
                    },
                    x: {
                        ticks: {
                            color: '#6c757d',
                            font: {
                                size: 12,
                                weight: '600'
                            },
                            padding: 10
                        },
                          grid: {
                            display: false,
                            drawBorder: false
                        },
                        title: {
                            display: true,
                            text: 'Semester',
                            color: '#049D6F',
                            font: {
                                size: 13,
                                weight: 'bold'
                            },
                            padding: 10
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
        
        canvas.style.animation = 'fadeIn 1s ease';
        
    } else {
        canvas.style.display = 'none';
        placeholder.style.display = 'block';
    }
});
</script>
<?php
// Tutup koneksi di akhir file
$conn->close();
require 'templates/footer.php';
?>