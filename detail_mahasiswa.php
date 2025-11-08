<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen' || !isset($_GET['nim'])) {
    header("Location: dashboard_dosen.php");
    exit();
}

$nim_mahasiswa = $_GET['nim'];
$id_dosen_login = $_SESSION['user_id'];
$pesan_sukses = '';
$pesan_error = '';

// ==========================================================
// === KONEKSI DATABASE (Otomatis XAMPP atau InfinityFree) ===
// ==========================================================
require_once 'config.php';

// [MODIFIKASI] Pindahkan SEMUA proses POST ke ATAS
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // === MODIFIKASI: PROSES FORM CATATAN DOSEN + SIMPAN KE LOGBOOK ===
    if (isset($_POST['submit_catatan_dosen'])) {
        $catatan = $_POST['catatan_dosen'];
        
        $conn->begin_transaction();
        
        try {
            // 1. Simpan/Update ke tabel 'catatan_dosen'
            $stmt_catatan = $conn->prepare("INSERT INTO catatan_dosen (nim, id_dosen, catatan) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE catatan = VALUES(catatan), id_dosen = VALUES(id_dosen)");
            $stmt_catatan->bind_param("sis", $nim_mahasiswa, $id_dosen_login, $catatan);
            $stmt_catatan->execute();

            // 2. Simpan juga ke 'logbook' sebagai riwayat
            $tanggal_log = date('Y-m-d');
            $topik_log = "Catatan Dosen";
            $tindak_lanjut_log = "";

            $stmt_logbook = $conn->prepare("INSERT INTO logbook (nim_mahasiswa, id_dosen, pengisi, status_baca, tanggal_bimbingan, topik_bimbingan, isi_bimbingan, tindak_lanjut) VALUES (?, ?, 'Dosen', 'Belum Dibaca', ?, ?, ?, ?)");
            $stmt_logbook->bind_param("sissss", $nim_mahasiswa, $id_dosen_login, $tanggal_log, $topik_log, $catatan, $tindak_lanjut_log);
            $stmt_logbook->execute();

            // Commit jika berhasil
            $conn->commit();
            
            // Redirect ke halaman sukses
            header("Location: detail_mahasiswa.php?nim=" . urlencode($nim_mahasiswa) . "&update=catatan_ok");
            exit();

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            $pesan_error = "Gagal menyimpan catatan. Error: " . $exception->getMessage();
        }
    }
    
    // Proses form asli Anda
    elseif (isset($_POST['submit_logbook']) && !isset($_POST['pencapaian'])) {
        $tanggal = $_POST['tanggal_bimbingan']; $topik = $_POST['topik_bimbingan'];
        $isi = $_POST['isi_bimbingan']; $tindak_lanjut = $_POST['tindak_lanjut'];
        $stmt_insert = $conn->prepare("INSERT INTO logbook (nim_mahasiswa, id_dosen, pengisi, status_baca, tanggal_bimbingan, topik_bimbingan, isi_bimbingan, tindak_lanjut) VALUES (?, ?, 'Dosen', 'Belum Dibaca', ?, ?, ?, ?)");
        $stmt_insert->bind_param("sissss", $nim_mahasiswa, $id_dosen_login, $tanggal, $topik, $isi, $tindak_lanjut);
        if ($stmt_insert->execute()) { 
            $pesan_sukses = "Catatan bimbingan berhasil disimpan!"; 
            if ($topik == 'Peringatan Akademik Terkait Nilai' && $conn->query("SHOW TABLES LIKE 'nilai_bermasalah'")->num_rows > 0) { 
                $conn->query("UPDATE nilai_bermasalah SET status_perbaikan = 'Sudah' WHERE nim_mahasiswa = '{$nim_mahasiswa}'"); 
            } 
        }
    } 
    elseif (isset($_POST['submit_evaluasi']) && !isset($_POST['pencapaian'])) {
        $periode = $_POST['periode_evaluasi']; $skor_evaluasi = $_POST['skor'];
        $stmt_eval = $conn->prepare("INSERT INTO evaluasi_softskill (nim_mahasiswa, id_dosen, periode_evaluasi, kategori, skor) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE skor = VALUES(skor)");
        foreach ($skor_evaluasi as $kategori => $skor) { $stmt_eval->bind_param("sissi", $nim_mahasiswa, $id_dosen_login, $periode, $kategori, $skor); $stmt_eval->execute(); }
        $pesan_sukses = "Evaluasi soft skill berhasil disimpan!";
    } 
    elseif (isset($_POST['submit_nilai_bermasalah']) && !isset($_POST['pencapaian'])) {
        $stmt_delete = $conn->prepare("DELETE FROM nilai_bermasalah WHERE nim_mahasiswa = ?"); $stmt_delete->bind_param("s", $nim_mahasiswa); $stmt_delete->execute();
        if (isset($_POST['nama_mk'])) {
            $stmt_insert = $conn->prepare("INSERT INTO nilai_bermasalah (nim_mahasiswa, nama_mk, nilai_huruf, semester_diambil) VALUES (?, ?, ?, ?)");
            for ($i = 0; $i < count($_POST['nama_mk']); $i++) { if (!empty($_POST['nama_mk'][$i])) { $stmt_insert->bind_param("sssi", $nim_mahasiswa, $_POST['nama_mk'][$i], $_POST['nilai_huruf'][$i], $_POST['semester_diambil'][$i]); $stmt_insert->execute(); } }
        }
        $pesan_sukses = "Laporan nilai bermasalah berhasil diperbarui.";
    }
} // Akhir dari if ($_SERVER['REQUEST_METHOD'] == 'POST')


// Pindahkan Cek notifikasi GET ke atas juga
if (isset($_GET['update']) && $_GET['update'] == 'sukses') {
    $pesan_sukses = "Pencapaian berhasil diperbarui!";
}
elseif (isset($_GET['update']) && $_GET['update'] == 'nilai_ok') {
    $pesan_sukses = "Catatan nilai bermasalah telah berhasil dihapus!";
}
elseif (isset($_GET['update']) && $_GET['update'] == 'catatan_ok') {
    $pesan_sukses = "Catatan khusus untuk mahasiswa berhasil disimpan!";
}
elseif (isset($_GET['update']) && strpos($_GET['update'], 'error') !== false) { 
    $pesan_error = "Terjadi kesalahan saat memproses permintaan.";
}

// Cek pesan dari session (untuk tanggapan konsultasi)
if (isset($_SESSION['pesan_sukses'])) {
    $pesan_sukses = $_SESSION['pesan_sukses'];
    unset($_SESSION['pesan_sukses']);
}
if (isset($_SESSION['pesan_error'])) {
    $pesan_error = $_SESSION['pesan_error'];
    unset($_SESSION['pesan_error']);
}


// Saat halaman ini dibuka, tandai notifikasi sebagai "Dibaca"
$conn->query("UPDATE logbook SET status_baca = 'Dibaca' WHERE nim_mahasiswa = '{$nim_mahasiswa}' AND id_dosen = {$id_dosen_login} AND pengisi = 'Mahasiswa'");

// ==========================================================
// --- BAGIAN 2: PANGGIL TEMPLATE (HTML MULAI DI SINI) ---
// ==========================================================

// Tentukan judul halaman SEKARANG, TEPAT SEBELUM memanggil header
$page_title = 'Detail Mahasiswa';
require 'templates/header.php';

// ==========================================================
// --- BAGIAN 3: AMBIL DATA UNTUK DITAMPILKAN ---
// ==========================================================

// === AMBIL SEMUA DATA YANG DIPERLUKAN ===
$stmt_mhs = $conn->prepare("SELECT m.*, p.nama_prodi, d.nama_dosen FROM mahasiswa m JOIN program_studi p ON m.id_prodi = p.id_prodi JOIN dosen d ON m.id_dosen_pa = d.id_dosen WHERE m.nim = ? AND m.id_dosen_pa = ?");
$stmt_mhs->bind_param("si", $nim_mahasiswa, $id_dosen_login);
$stmt_mhs->execute();
$result_mhs = $stmt_mhs->get_result();
if ($result_mhs->num_rows === 0) { 
    echo '<div class="container-fluid my-4"><div class="alert alert-danger">Data mahasiswa tidak ditemukan atau Anda tidak memiliki hak akses.</div></div>';
    require 'templates/footer.php';
    exit(); 
}
$mahasiswa = $result_mhs->fetch_assoc();

// Asumsi path foto
$foto_mahasiswa = $mahasiswa['foto_mahasiswa'] ?? 'assets/img/default_avatar.png';

// Ambil data catatan dosen
$catatan_dosen = '';
if ($conn->query("SHOW TABLES LIKE 'catatan_dosen'")->num_rows > 0) {
    $stmt_get_catatan = $conn->prepare("SELECT catatan FROM catatan_dosen WHERE nim = ?");
    $stmt_get_catatan->bind_param("s", $nim_mahasiswa);
    $stmt_get_catatan->execute();
    $result_catatan = $stmt_get_catatan->get_result();
    if ($row = $result_catatan->fetch_assoc()) {
        $catatan_dosen = $row['catatan'];
    }
    if (isset($stmt_get_catatan)) $stmt_get_catatan->close();
}

// =================================================================
// --- [KODE BARU & DIPERBAIKI] Ambil data evaluasi dosen ---
// =================================================================
$evaluasi_dosen_data = null; // Akan menampung 1 baris data evaluasi terbaru
$nama_tabel_eval_dosen = 'evaluasi_dosen'; 

if($conn->query("SHOW TABLES LIKE '$nama_tabel_eval_dosen'")->num_rows > 0) {
    
    // Kolom dari gambar Anda: periode_evaluasi, skor_komunikasi, skor_membantu, skor_solusi, saran_kritik
    // PERBAIKAN: Menghapus kolom 'skor' yang tidak ada di database
    $sql_eval = "SELECT periode_evaluasi, skor_komunikasi, skor_membantu, skor_solusi, saran_kritik 
                 FROM $nama_tabel_eval_dosen 
                 WHERE nim_mahasiswa = ? AND id_dosen = ? 
                 ORDER BY periode_evaluasi DESC, tanggal_submit DESC 
                 LIMIT 1"; // Mengambil 1 data evaluasi terbaru
                 
    $stmt_eval_dosen = $conn->prepare($sql_eval);
    $stmt_eval_dosen->bind_param("si", $nim_mahasiswa, $id_dosen_login);
    $stmt_eval_dosen->execute();
    $result_eval = $stmt_eval_dosen->get_result();

    if ($result_eval->num_rows > 0) {
        $evaluasi_dosen_data = $result_eval->fetch_assoc();
        
        // [FITUR BARU] Hitung rata-rata skor secara manual
        $total_skor = $evaluasi_dosen_data['skor_komunikasi'] + $evaluasi_dosen_data['skor_membantu'] + $evaluasi_dosen_data['skor_solusi'];
        $rata_rata_skor = $total_skor / 3;
        
        // Tambahkan skor rata-rata ke array untuk GAMPANG digunakan di HTML
        $evaluasi_dosen_data['skor_rata_rata'] = $rata_rata_skor;
    }
    if (isset($stmt_eval_dosen)) $stmt_eval_dosen->close();
}
// ==========================================================
// --- [AKHIR KODE BARU] ---
// ==========================================================

// =================================================================
// --- [KODE BARU] Ambil data konsultasi judul ---
// =================================================================
$result_konsultasi_judul = null;
if($conn->query("SHOW TABLES LIKE 'konsultasi_judul'")->num_rows > 0) {
    $stmt_konsul = $conn->prepare("SELECT * FROM konsultasi_judul WHERE nim = ? ORDER BY tanggal_pengajuan DESC");
    $stmt_konsul->bind_param("s", $nim_mahasiswa);
    $stmt_konsul->execute();
    $result_konsultasi_judul = $stmt_konsul->get_result();
}
$jumlah_konsultasi_judul = $result_konsultasi_judul ? $result_konsultasi_judul->num_rows : 0;
// ==========================================================


$is_nonaktif = ($mahasiswa['status'] != 'Aktif');
$krs_belum_disetujui = (isset($mahasiswa['krs_disetujui']) && $mahasiswa['krs_disetujui'] != 1); // Asumsi 1 = Disetujui

// ... (sisa pengambilan data tidak diubah) ...
$result_log = $conn->query("SELECT * FROM logbook WHERE nim_mahasiswa = '{$nim_mahasiswa}' ORDER BY tanggal_bimbingan DESC, created_at DESC");
$daftar_pencapaian_valid = ['Konsultasi Judul', 'Seminar Proposal', 'Ujian Komperehensif', 'Seminar Hasil', 'Ujian Skripsi (Yudisium)', 'Publikasi Jurnal'];
$result_pencapaian = $conn->query("SELECT nama_pencapaian, status, tanggal_selesai FROM pencapaian WHERE nim_mahasiswa = '{$nim_mahasiswa}'");
$status_pencapaian = [];
while($row = $result_pencapaian->fetch_assoc()) { $status_pencapaian[$row['nama_pencapaian']] = $row; }
$result_mk = $conn->query("SELECT nama_mk FROM mata_kuliah ORDER BY nama_mk ASC");
$daftar_matakuliah = [];
while ($row = $result_mk->fetch_assoc()) { $daftar_matakuliah[] = $row['nama_mk']; }
$kategori_softskill = ['Disiplin & Komitmen', 'Partisipasi & Keaktifan', 'Etika & Sopan Santun', 'Kepemimpinan & Kerjasama'];
$current_year = date('Y'); $current_month = date('n');
$periode_sekarang = $current_year . ' ' . (($current_month >= 2 && $current_month <= 7) ? 'Genap' : 'Ganjil');
function formatBytes($bytes, $precision = 2) { $units = ['B', 'KB', 'MB', 'GB', 'TB']; $bytes = max($bytes, 0); $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); $pow = min($pow, count($units) - 1); $bytes /= (1 << (10 * $pow)); return round($bytes, $precision) . ' ' . $units[$pow]; }

// Cek apakah tabel ada sebelum query
$result_dokumen = null;
if($conn->query("SHOW TABLES LIKE 'dokumen'")->num_rows > 0) {
    $stmt_dokumen = $conn->prepare("SELECT * FROM dokumen WHERE nim_mahasiswa = ? ORDER BY tanggal_unggah DESC");
    $stmt_dokumen->bind_param("s", $nim_mahasiswa);
    $stmt_dokumen->execute();
    $result_dokumen = $stmt_dokumen->get_result();
}
$jumlah_dokumen = $result_dokumen ? $result_dokumen->num_rows : 0;

$result_chart_data = null;
if($conn->query("SHOW TABLES LIKE 'riwayat_akademik'")->num_rows > 0) {
    $stmt_chart = $conn->prepare("SELECT semester, ip_semester FROM riwayat_akademik WHERE nim_mahasiswa = ? ORDER BY semester ASC");
    $stmt_chart->bind_param("s", $nim_mahasiswa);
    $stmt_chart->execute();
    $result_chart_data = $stmt_chart->get_result()->fetch_all(MYSQLI_ASSOC);
}
$chart_labels = json_encode($result_chart_data ? array_column($result_chart_data, 'semester') : []);
$chart_data = json_encode($result_chart_data ? array_column($result_chart_data, 'ip_semester') : []);

$result_nilai_bermasalah = null;
if($conn->query("SHOW TABLES LIKE 'nilai_bermasalah'")->num_rows > 0) {
    $result_nilai_bermasalah = $conn->query("SELECT * FROM nilai_bermasalah WHERE nim_mahasiswa = '{$nim_mahasiswa}' ORDER BY semester_diambil ASC");
}
$jumlah_notif_nilai = $result_nilai_bermasalah ? $result_nilai_bermasalah->num_rows : 0;
?>

<style>
    /* ... (CSS ASLI ANDA TIDAK SAYA UBAH SAMA SEKALI) ... */
    :root { --campus-green: #049D6F; --smart-blue: #0d6efd; --light-gray: #f8f9fa; --gray: #e9ecef; --dark-gray: #495057; --card-shadow: 0 10px 30px rgba(0,0,0,0.08); --card-radius: 1rem; }
    body { background-color: var(--light-gray); }
    .card { border: none; border-radius: var(--card-radius); box-shadow: var(--card-shadow); transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 12px 35px rgba(0,0,0,0.1); }
    .card-header { background-color: #fff; border-bottom: 1px solid var(--gray); border-top-left-radius: var(--card-radius); border-top-right-radius: var(--card-radius); padding: 1.25rem 1.5rem; }
    .card-header h5 { margin: 0; font-weight: 700; color: var(--campus-green); }
    .nav-pills .nav-link { color: var(--dark-gray); font-weight: 600; }
    .nav-pills .nav-link.active { background-color: var(--campus-green); color: white; box-shadow: 0 4px 15px rgba(4, 157, 111, 0.4); }
    .profile-header-card { background: linear-gradient(135deg, var(--campus-green), #037a59); color: white; }
    .profile-avatar { width: 100px; height: 100px; border-radius: 50%; border: 4px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.2); object-fit: cover; }
    .profile-header-card h2 { font-weight: 700; margin: 0; }
    .profile-header-card .breadcrumb-item { color: rgba(255,255,255,0.8); }
    .profile-header-card .breadcrumb-item.active { color: white; font-weight: 600; }
    .stat-card { text-align: center; padding: 1.5rem 1rem; }
    .stat-card-icon { font-size: 2.5rem; color: var(--campus-green); margin-bottom: 0.5rem; display: block; }
    .stat-card h3 { font-size: 2rem; font-weight: 700; color: var(--dark-gray); margin: 0; }
    .stat-card p { color: #6c757d; font-weight: 500; margin: 0; }
    .bimbingan-timeline { list-style: none; padding: 0; position: relative; }
    .bimbingan-timeline::before { content: ''; position: absolute; top: 10px; bottom: 10px; left: 20px; width: 3px; background: var(--gray); border-radius: 2px; }
    .bimbingan-item { position: relative; padding-left: 50px; padding-bottom: 2rem; }
    .bimbingan-item:last-child { padding-bottom: 0; }
    .bimbingan-icon { position: absolute; left: 0; top: 0; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: bold; color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 1; }
    .bimbingan-icon.bg-dosen { background-color: var(--smart-blue); }
    .bimbingan-icon.bg-mahasiswa { background-color: var(--campus-green); }
    .bimbingan-icon.bg-peringatan { background-color: #ffc107; color: #333; }
    .bimbingan-content { background: white; border: 1px solid var(--gray); border-radius: 0.75rem; padding: 1rem; }
    .bimbingan-content h6 { font-weight: 700; color: var(--dark-gray); }
    .bimbingan-content p { margin-bottom: 0.5rem; }
    .achievement-stepper { list-style-type: none; padding-left: 1.5rem; position: relative; }
    .achievement-stepper::before { content: ''; position: absolute; left: 26px; top: 10px; bottom: 10px; width: 2px; background-color: #e9ecef; z-index: 0; }
    .achievement-step { position: relative; padding-bottom: 1.5rem; }
    .achievement-step:last-child { padding-bottom: 0; }
    .achievement-step-label { padding-left: 2.5rem; min-height: 24px; display: block; margin-bottom: 0; }
    .achievement-step-label::before { content: "\f28b"; font-family: "bootstrap-icons"; font-weight: 900; position: absolute; left: 0; top: 0; font-size: 1.5rem; line-height: 1; color: #adb5bd; background-color: white; border-radius: 50%; z-index: 1; }
    .achievement-step .step-title { font-weight: 600; color: #6c757d; line-height: 1.2; }
    .achievement-step .step-date { font-size: 0.85rem; color: #6c757d; line-height: 1; }
    .achievement-step.is-done .achievement-step-label::before { content: "\f28a"; color: var(--campus-green); }
    .achievement-step.is-done .step-title { color: #212529; }
    .achievement-step.is-done .step-date { color: var(--campus-green); font-weight: 500; }
    .form-switch .form-check-input { width: 2em; margin-left: -2.5em; background-color: #fff; border: 1px solid var(--gray); background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%280, 0, 0, 0.25%29'/%3e%3c/svg%3e"); }
    .form-switch .form-check-input:checked { background-position: right center; background-color: var(--campus-green); border-color: var(--campus-green); background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%28255, 255, 255, 1.0%29'/%3e%3c/svg%3e"); }
    .form-switch .form-check-label { font-weight: 600; color: var(--dark-gray); }
    .date-container { display: none; padding-left: 2.5em; }
</style>

<div class="container-fluid my-4">

<div class="d-flex justify-content-between align-items-center mb-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="dashboard_dosen.php" class="text-decoration-none" style="color: var(--campus-green);">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail Mahasiswa</li>
        </ol>
    </nav>
    
    <!-- Tombol Cetak (Dropdown) - DITAMBAHKAN CETAK KONSULTASI JUDUL -->
    <div class="btn-group">
        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: var(--campus-green); border-color: var(--campus-green);">
            <i class="bi bi-printer me-2"></i>Cetak Dokumen
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item" href="cetak_laporan_lengkap.php?nim=<?= $mahasiswa['nim']; ?>" target="_blank">
                    <i class="bi bi-file-earmark-text me-2"></i>Cetak Laporan Lengkap
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="cetak_konsultasi_judul.php?nim=<?= $mahasiswa['nim']; ?>" target="_blank">
                    <i class="bi bi-chat-square-text me-2"></i>Cetak Konsultasi Judul
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="cetak_logbook.php?nim=<?= $mahasiswa['nim']; ?>" target="_blank">
                    <i class="bi bi-journal-text me-2"></i>Cetak Logbook Bimbingan
                </a>
            </li>
        </ul>
    </div>
</div>

    <?php if ($pesan_sukses): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><i class="bi bi-check-circle-fill me-2"></i><?= $pesan_sukses; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
    <?php if ($pesan_error): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $pesan_error; ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

    <?php if ($is_nonaktif && $krs_belum_disetujui) : ?>
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-octagon-fill fs-4 me-3"></i>
            <div>
                <strong class="d-block">PERINGATAN KRITIS!</strong>
                Mahasiswa ini memiliki beberapa masalah yang memerlukan perhatian segera:
                <ul class="mb-0 mt-2" style="padding-left: 20px;">
                    <li>Status mahasiswa saat ini: <strong><?= htmlspecialchars($mahasiswa['status']); ?></strong>.</li>
                    <li>Kartu Rencana Studi (KRS) <strong>belum disetujui</strong>.</li>
                </ul>
            </div>
        </div>
    <?php elseif ($is_nonaktif) : ?>
        <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-person-x-fill fs-4 me-3"></i>
            <div><strong>Peringatan Status!</strong> Status mahasiswa ini adalah <strong><?= htmlspecialchars($mahasiswa['status']); ?></strong>. Mohon segera hubungi mahasiswa yang bersangkutan.</div>
        </div>
    <?php elseif ($krs_belum_disetujui) : ?>
        <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-journal-text fs-4 me-3"></i>
            <div><strong>Perhatian!</strong> Kartu Rencana Studi (KRS) mahasiswa ini untuk semester berjalan <strong>belum disetujui</strong>.</div>
        </div>
    <?php endif; ?>
    
    <div class="card shadow-sm mb-4 profile-header-card">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="<?= $foto_mahasiswa; ?>" alt="Foto Mahasiswa" class="profile-avatar">
                </div>
                <div class="col">
                    <h2 class="text-white"><?= htmlspecialchars($mahasiswa['nama_mahasiswa']); ?></h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="background-color: transparent;">
                            <li class="breadcrumb-item"><?= htmlspecialchars($mahasiswa['nim']); ?></li>
                            <li class="breadcrumb-item"><?= htmlspecialchars($mahasiswa['nama_prodi']); ?></li>
                            <li class="breadcrumb-item active" aria-current="page">Angkatan <?= htmlspecialchars($mahasiswa['angkatan']); ?></li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-4 text-md-end">
                    <small class="text-white-50">Dosen Pembimbing Akademik</small>
                    <h6 class="text-white fw-bold mb-0"><?= htmlspecialchars($mahasiswa['nama_dosen']); ?></h6>
                    </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="stat-card">
                    <i class="bi bi-graph-up stat-card-icon"></i>
                    <h3><?= number_format($mahasiswa['ipk'], 2); ?></h3>
                    <p>IPK Kumulatif</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="stat-card">
                    <i class="bi bi-stack stat-card-icon"></i>
                    <h3><?= htmlspecialchars($mahasiswa['total_sks'] ?? '0'); ?></h3>
                    <p>Total SKS</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="stat-card">
                    <i class="bi bi-person-check stat-card-icon" style="color: <?= ($mahasiswa['status'] == 'Aktif') ? 'var(--campus-green)' : '#dc3545'; ?>;"></i>
                    <h3 style="color: <?= ($mahasiswa['status'] == 'Aktif') ? 'var(--campus-green)' : '#dc3545'; ?>;"><?= htmlspecialchars($mahasiswa['status']); ?></h3>
                    <p>Status</p>
                </div>
            </div>
        </div>
    </div>


    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-chat-left-text-fill me-2"></i>Riwayat Bimbingan (<?= $result_log->num_rows; ?>)</h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <?php if ($result_log->num_rows > 0): ?>
                        <ul class="bimbingan-timeline">
                        <?php mysqli_data_seek($result_log, 0); while($log = $result_log->fetch_assoc()):
                            $is_dosen = ($log['pengisi'] == 'Dosen');
                            $is_peringatan = ($log['topik_bimbingan'] == 'Peringatan Akademik Terkait Nilai');
                            $icon_class = $is_dosen ? 'bg-dosen' : 'bg-mahasiswa';
                            $icon = $is_dosen ? 'D' : 'M';
                            
                            $is_catatan_dosen = ($log['topik_bimbingan'] == 'Catatan Dosen');
                            if ($is_peringatan) { 
                                $icon_class = 'bg-peringatan'; 
                                $icon = '<i class="bi bi-exclamation-lg"></i>'; 
                            } elseif ($is_catatan_dosen) {
                                $icon_class = 'bg-dosen';
                                $icon = '<i class="bi bi-sticky-fill"></i>';
                            }
                        ?>
                            <li class="bimbingan-item">
                                <div class="bimbingan-icon <?= $icon_class ?>"><?= $icon ?></div>
                                <div class="bimbingan-content">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0"><?= htmlspecialchars($log['topik_bimbingan']); ?></h6>
                                        <small class="text-muted fw-bold"><?= date('d M Y', strtotime($log['tanggal_bimbingan'])); ?></small>
                                    </div>
                                    <p class="mb-1"><?= nl2br(htmlspecialchars($log['isi_bimbingan'])); ?></p>
                                    <?php if (!empty($log['tindak_lanjut'])): ?>
                                        <hr class="my-2">
                                        <small class="text-muted"><b>Tindak Lanjut:</b> <?= nl2br(htmlspecialchars($log['tindak_lanjut'])); ?></small>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-center text-muted p-3">Belum ada riwayat bimbingan.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">
                    <ul class="nav nav-pills card-header-pills" id="aksiTab" role="tablist">
                        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-catatan-dosen">Catatan Dosen</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-konsultasi-judul">Konsultasi Judul <span class="badge bg-info ms-1"><?= $jumlah_konsultasi_judul ?></span></button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-lapor-nilai">Lapor Nilai</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-penilaian">Penilaian</button></li>
                        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-peringatan">Peringatan <span class="badge bg-danger ms-1"><?= $jumlah_notif_nilai ?></span></button></li>
                    </ul>
                </div>
                <div class="card-body">

                    <?php if ($jumlah_notif_nilai > 0): ?>
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                        <div>
                            Mahasiswa ini memiliki <strong><?= $jumlah_notif_nilai ?> nilai bermasalah</strong>. Klik tombol "Peringatan" di bawah atau <a href="#" class="alert-link" data-bs-toggle="pill" data-bs-target="#tab-peringatan">klik di sini</a>.
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="tab-content p-2">
                        <div class="tab-pane fade show active" id="tab-catatan-dosen">
                            <h6><i class="bi bi-sticky-fill me-2"></i>Catatan Khusus Untuk Mahasiswa</h6>
                            <p class="text-muted small">Catatan ini akan dapat dilihat oleh mahasiswa di dasbor mereka dan akan tersimpan di riwayat bimbingan.</p>
                            <form method="POST" action="detail_mahasiswa.php?nim=<?= urlencode($mahasiswa['nim']); ?>">
                                <div class="mb-3"><textarea class="form-control" name="catatan_dosen" rows="5" placeholder="Tuliskan catatan Anda di sini..."><?= htmlspecialchars($catatan_dosen); ?></textarea></div>
                                <div class="d-grid"><button type="submit" name="submit_catatan_dosen" class="btn btn-primary" style="background-color: var(--campus-green); border-color: var(--campus-green);"><i class="bi bi-save me-2"></i>Simpan Catatan</button></div>
                            </form>
                        </div>
                        
                        <!-- TAB KONSULTASI JUDUL BARU -->
                        <div class="tab-pane fade" id="tab-konsultasi-judul">
                            <h6><i class="bi bi-chat-square-text me-2"></i>Riwayat Konsultasi Judul</h6>
                            <p class="text-muted small">Mahasiswa telah mengajukan konsultasi judul. Anda dapat memberikan tanggapan di bawah.</p>
                            
                            <?php if ($jumlah_konsultasi_judul > 0): ?>
                                <?php mysqli_data_seek($result_konsultasi_judul, 0); while($konsul = $result_konsultasi_judul->fetch_assoc()): ?>
                                <div class="card mb-3" style="border-left: 4px solid <?= $konsul['status'] == 'Disetujui' ? '#10b981' : ($konsul['status'] == 'Ditolak' ? '#ef4444' : '#fbbf24'); ?>;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($konsul['judul_usulan']); ?></h6>
                                            <span class="badge bg-<?= $konsul['status'] == 'Disetujui' ? 'success' : ($konsul['status'] == 'Ditolak' ? 'danger' : ($konsul['status'] == 'Revisi' ? 'info' : 'warning')) ?>">
                                                <?= $konsul['status']; ?>
                                            </span>
                                        </div>
                                        
                                        <?php if (!empty($konsul['deskripsi'])): ?>
                                        <div class="mb-3">
                                            <strong>Deskripsi:</strong>
                                            <p class="mb-0 mt-1 text-muted"><?= nl2br(htmlspecialchars($konsul['deskripsi'])); ?></p>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <small class="text-muted d-block mb-3">
                                            <i class="bi bi-calendar3"></i> Diajukan: <?= date('d F Y, H:i', strtotime($konsul['tanggal_pengajuan'])); ?> WIB
                                        </small>
                                        
                                        <!-- Form Tanggapan Dosen -->
                                        <?php if ($konsul['status'] == 'Menunggu' || empty($konsul['catatan_dosen'])): ?>
                                        <hr>
                                        <form action="tanggapi_konsultasi.php" method="POST" class="mt-3">
                                            <input type="hidden" name="id_konsultasi" value="<?= $konsul['id_konsultasi']; ?>">
                                            <input type="hidden" name="nim" value="<?= $nim_mahasiswa; ?>">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold"><i class="bi bi-chat-left-quote me-2"></i>Tanggapan Anda:</label>
                                                <textarea class="form-control" name="catatan_dosen" rows="4" placeholder="Berikan tanggapan Anda terhadap judul yang diajukan..." required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold"><i class="bi bi-check2-square me-2"></i>Status:</label>
                                                <select class="form-select" name="status" required>
                                                    <option value="">-- Pilih Status --</option>
                                                    <option value="Disetujui">✅ Disetujui</option>
                                                    <option value="Revisi">🔄 Perlu Revisi</option>
                                                    <option value="Ditolak">❌ Ditolak</option>
                                                </select>
                                            </div>
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary" style="background-color: var(--campus-green); border-color: var(--campus-green);">
                                                    <i class="bi bi-send me-2"></i>Kirim Tanggapan
                                                </button>
                                            </div>
                                        </form>
                                        <?php else: ?>
                                        <div class="alert alert-info mb-0">
                                            <strong><i class="bi bi-chat-left-quote me-2"></i>Tanggapan Anda:</strong>
                                            <p class="mb-2 mt-2"><?= nl2br(htmlspecialchars($konsul['catatan_dosen'])); ?></p>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i> <?= date('d F Y, H:i', strtotime($konsul['tanggal_respon'])); ?> WIB
                                            </small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #cbd5e0;"></i>
                                    <p class="text-muted mt-2 mb-0">Mahasiswa ini belum mengajukan konsultasi judul</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="tab-pane fade" id="tab-lapor-nilai">
                            <h6><i class="bi bi-journal-x me-2"></i>Lapor Nilai Bermasalah (C/D/E)</h6>
                            <p class="text-muted small">Laporan ini akan menggantikan semua laporan sebelumnya.</p>
                            <form method="POST" action="detail_mahasiswa.php?nim=<?= urlencode($mahasiswa['nim']); ?>"><div id="laporan-container"><div class="row g-2 align-items-center laporan-baris mb-2"><div class="col-md-5"><input type="text" class="form-control" name="nama_mk[]" list="mkList" placeholder="Nama Mata Kuliah"></div><div class="col-md-2"><select class="form-select" name="nilai_huruf[]"><option value="C">C</option><option value="D">D</option><option value="E">E</option></select></div><div class="col-md-3"><input type="number" class="form-control" name="semester_diambil[]" placeholder="Semester" min="1" max="14"></div><div class="col-md-2"><button type="button" class="btn btn-danger btn-sm w-100 btn-hapus-baris">Hapus</button></div></div></div><datalist id="mkList"><?php foreach($daftar_matakuliah as $mk): ?><option value="<?= htmlspecialchars($mk); ?>"><?php endforeach; ?></datalist><div class="d-flex gap-2 mt-3"><button type="button" id="btn-tambah-baris" class="btn btn-secondary"><i class="bi bi-plus-circle me-1"></i>Tambah Baris</button><button type="submit" name="submit_nilai_bermasalah" class="btn btn-primary" style="background-color: var(--campus-green); border-color: var(--campus-green);"><i class="bi bi-save me-1"></i>Simpan Laporan</button></div></form>
                        </div>
                        
                        <div class="tab-pane fade" id="tab-penilaian">
                            <h6><i class="bi bi-clipboard-check me-2"></i>Form Penilaian Soft Skill</h6>
                            <p class="text-muted small">Periode: <strong><?= $periode_sekarang; ?></strong>. Beri skor 1-5.</p>
                            <form method="POST" action="detail_mahasiswa.php?nim=<?= urlencode($mahasiswa['nim']); ?>"><input type="hidden" name="periode_evaluasi" value="<?= $periode_sekarang; ?>"><?php foreach($kategori_softskill as $kategori): ?><div class="mb-3"><label class="form-label"><?= htmlspecialchars($kategori); ?></label><select class="form-select" name="skor[<?= htmlspecialchars($kategori); ?>]" required><option value="">Pilih Skor...</option><?php for($i=1; $i<=5; $i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?></select></div><?php endforeach; ?><div class="d-grid"><button type="submit" name="submit_evaluasi" class="btn btn-primary" style="background-color: var(--campus-green); border-color: var(--campus-green);"><i class="bi bi-save me-2"></i>Simpan Evaluasi</button></div></form>
                        </div>
                        
                        <div class="tab-pane fade" id="tab-peringatan">
                            <h6><i class="bi bi-exclamation-octagon-fill me-2 text-danger"></i>Daftar Nilai Bermasalah</h6>
                            <?php if ($jumlah_notif_nilai > 0): ?>
                                <p class="small text-muted">Pastikan Anda telah memverifikasi bukti perbaikan nilai (misalnya melalui dokumen yang diunggah) sebelum menghapus catatan ini.</p>
                                <ul class="list-group mb-3">
                                    <?php mysqli_data_seek($result_nilai_bermasalah, 0); while($nilai = $result_nilai_bermasalah->fetch_assoc()): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center nilai-item" data-mk="<?= htmlspecialchars($nilai['nama_mk']); ?>" data-nilai="<?= htmlspecialchars($nilai['nilai_huruf']); ?>">
                                            <?= htmlspecialchars($nilai['nama_mk']); ?> (Semester <?= htmlspecialchars($nilai['semester_diambil']); ?>)
                                            <span class="badge bg-danger rounded-pill"><?= htmlspecialchars($nilai['nilai_huruf']); ?></span>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                                <div class="d-flex justify-content-between">
                                    <button id="kirimPeringatanMassal" class="btn btn-warning"><i class="bi bi-send me-2"></i>Kirim Peringatan Ulang</button>

                                    <form action="hapus_nilai_bermasalah.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA catatan nilai bermasalah untuk mahasiswa ini?');">
                                        <input type="hidden" name="nim_mahasiswa" value="<?= htmlspecialchars($nim_mahasiswa); ?>">
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="bi bi-check2-circle me-2"></i>Tandai Sudah Diperbaiki
                                        </button>
                                    </form>

                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted mb-0"><i class="bi bi-check2-circle me-2 text-success"></i>Tidak ada laporan nilai bermasalah. Bagus!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4"><div class="card-header"><h5><i class="bi bi-bar-chart-line-fill me-2"></i>Grafik IP per Semester</h5></div><div class="card-body"><canvas id="progressChart"></canvas><p id="chartPlaceholder" class="text-center text-muted" style="display: none;">Data riwayat akademik belum tersedia.</p></div></div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5><i class="bi bi-person-star-fill me-2"></i>Evaluasi oleh Mahasiswa</h5>
                </div>
                <div class="card-body">
                    <?php if ($evaluasi_dosen_data): // Cek jika data (dari BAGIAN 3) ditemukan ?>
                        
                        <h6 class="card-subtitle mb-2 text-muted">Periode Evaluasi: <strong><?= htmlspecialchars($evaluasi_dosen_data['periode_evaluasi']); ?></strong></h6>
                        
                        <ul class="list-group list-group-flush mt-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                Komunikasi
                                <span class="badge bg-primary rounded-pill fs-6">
                                    <?= htmlspecialchars($evaluasi_dosen_data['skor_komunikasi']); ?> / 5
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                Ketersediaan (Membantu)
                                <span class="badge bg-primary rounded-pill fs-6">
                                    <?= htmlspecialchars($evaluasi_dosen_data['skor_membantu']); ?> / 5
                                </span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                Pemberian Solusi
                                <span class="badge bg-primary rounded-pill fs-6">
                                    <?= htmlspecialchars($evaluasi_dosen_data['skor_solusi']); ?> / 5
                                </span>
                            </li>
                            
                            <?php if (isset($evaluasi_dosen_data['skor_rata_rata'])): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <strong>Skor Rata-rata</strong>
                                <span class="badge bg-success rounded-pill fs-6">
                                    <strong><?= number_format($evaluasi_dosen_data['skor_rata_rata'], 2); ?> / 5</strong>
                                </span>
                            </li>
                            <?php endif; ?>
                        </ul>
                        
                        <?php if (!empty($evaluasi_dosen_data['saran_kritik'])): ?>
                            <hr>
                            <strong>Saran & Masukan:</strong>
                            <p class="text-muted fst-italic mb-0 mt-1">
                                "<?= nl2br(htmlspecialchars($evaluasi_dosen_data['saran_kritik'])); ?>"
                            </p>
                        <?php endif; ?>

                    <?php else: ?>
                        <p class="text-center text-muted p-3 mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Mahasiswa ini belum memberikan evaluasi untuk Anda.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card shadow-sm mb-4"><div class="card-header"><h5><i class="bi bi-folder-fill me-2"></i>Dokumen (<?= $jumlah_dokumen; ?>)</h5></div><div class="list-group list-group-flush"><?php if ($result_dokumen && $result_dokumen->num_rows > 0): while($dokumen = $result_dokumen->fetch_assoc()): ?><a href="<?= htmlspecialchars($dokumen['path_file']); ?>" class="list-group-item list-group-item-action" download><div class="d-flex justify-content-between align-items-start"><div><strong class="d-block"><?= htmlspecialchars($dokumen['judul_dokumen']); ?></strong><small class="text-muted"><?= formatBytes($dokumen['ukuran_file']); ?> | <?= date('d M Y', strtotime($dokumen['tanggal_unggah'])); ?></small></div><i class="bi bi-download fs-5 text-primary"></i></div></a><?php endwhile; else: ?><div class="list-group-item text-center text-muted">Belum ada dokumen terunggah.</div><?php endif; ?></div></div>

            <div class="card shadow-sm">
                <div class="card-header"><h5><i class="bi bi-trophy-fill me-2"></i>Pencapaian</h5></div>
                <div class="card-body">
                    <ul class="achievement-stepper">
                        <?php foreach ($daftar_pencapaian_valid as $item):
                            $data_pencapaian = $status_pencapaian[$item] ?? null;
                            $is_checked = ($data_pencapaian && $data_pencapaian['status'] == 'Selesai');
                            $tanggal_selesai_display = ($is_checked && !empty($data_pencapaian['tanggal_selesai']))
                                                       ? date('d M Y', strtotime($data_pencapaian['tanggal_selesai']))
                                                       : 'Belum Selesai';
                        ?>
                        <li class="achievement-step <?= $is_checked ? 'is-done' : '' ?>">
                            <span class="achievement-step-label">
                                <div class="step-title"><?= htmlspecialchars($item); ?></div>
                                <div class="step-date"><?= $tanggal_selesai_display; ?></div>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <hr>
                    <div class="d-grid">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalUpdateSemuaPencapaian">
                            <i class="bi bi-pencil-square me-2"></i>Update
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalUpdateSemuaPencapaian" tabindex="-1" aria-labelledby="modalUpdateSemuaPencapaianLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: var(--card-radius);">
            <form action="update_pencapaian.php" method="POST">
                <div class="modal-header" style="background-color: var(--campus-green); color: white; border-top-left-radius: var(--card-radius); border-top-right-radius: var(--card-radius);">
                    <h5 class="modal-title" id="modalUpdateSemuaPencapaianLabel"><i class="bi bi-check-circle-fill me-2"></i>Update Kemajuan Studi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="nim_mahasiswa" value="<?= htmlspecialchars($nim_mahasiswa); ?>">
                    <?php foreach ($daftar_pencapaian_valid as $item):
                        $data_pencapaian = $status_pencapaian[$item] ?? null;
                        $is_checked = ($data_pencapaian && $data_pencapaian['status'] == 'Selesai');
                        $tanggal_selesai_raw = ($is_checked && !empty($data_pencapaian['tanggal_selesai'])) ? $data_pencapaian['tanggal_selesai'] : '';
                        $checkbox_id = 'modal_check_' . str_replace([' ', '(', ')'], '_', $item);
                    ?>
                    <div class="mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="pencapaian[<?= htmlspecialchars($item); ?>]" value="Selesai" id="<?= $checkbox_id ?>" <?= $is_checked ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $checkbox_id ?>"><?= htmlspecialchars($item); ?></label>
                        </div>
                        <div class="date-container mt-2" style="display: <?= $is_checked ? 'block' : 'none' ?>;">
                            <label for="tanggal_<?= $checkbox_id ?>" class="form-label small text-muted">Tanggal Selesai</label>
                            <input type="date" class="form-control form-control-sm" id="tanggal_<?= $checkbox_id ?>" name="tanggal_pencapaian[<?= htmlspecialchars($item); ?>]" value="<?= $tanggal_selesai_raw ?>">
                        </div>
                    </div>
                    <hr class="my-3">
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer" style="border-top: none;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background-color: var(--campus-green); border-color: var(--campus-green);"><i class="bi bi-save me-2"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="logbookModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: var(--card-radius);">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-send-fill me-2"></i>Kirim Peringatan ke Logbook</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="detail_mahasiswa.php?nim=<?= urlencode($nim_mahasiswa); ?>" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="tanggal_bimbingan" value="<?= date('Y-m-d'); ?>">
                    <div class="mb-3">
                        <label class="form-label">Topik Utama</label>
                        <input type="text" class="form-control" id="logbook-topik" name="topik_bimbingan" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Detail Peringatan (Otomatis)</label>
                        <textarea class="form-control" id="logbook-isi" name="isi_bimbingan" rows="6" readonly></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tindak Lanjut (Opsional)</label>
                        <textarea class="form-control" name="tindak_lanjut" rows="2" placeholder="Contoh: Segera hubungi saya untuk diskusi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="submit_logbook" class="btn btn-warning">Kirim Peringatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // 1. Logika Chart
    const chartLabels = <?= $chart_labels; ?>;
    const chartData = <?= $chart_data; ?>;
    const canvas = document.getElementById('progressChart');
    const placeholder = document.getElementById('chartPlaceholder');
    if (canvas && chartLabels && chartLabels.length > 0) {
        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: chartLabels.map(l => 'Smt ' + l),
                datasets: [{
                    label: 'IP Semester',
                    data: chartData,
                    fill: true,
                    backgroundColor: 'rgba(4, 157, 111, 0.1)',
                    borderColor: 'var(--campus-green)',
                    tension: 0.3,
                    pointBackgroundColor: 'var(--campus-green)',
                    pointRadius: 4
                }]
            },
            options: {
                scales: { y: { beginAtZero: true, max: 4.0 } },
                plugins: { legend: { display: false } }
            }
        });
    } else if(placeholder) {
        if(canvas) canvas.style.display = 'none';
        placeholder.style.display = 'block';
    }

    // 2. Logika Tambah/Hapus Baris Lapor Nilai
    const container = document.getElementById('laporan-container');
    const tombolTambah = document.getElementById('btn-tambah-baris');
    if (container && tombolTambah) {
        const updateTombolHapus = () => {
            const semuaBaris = container.querySelectorAll('.laporan-baris');
            semuaBaris.forEach(baris => {
                const tombolHapus = baris.querySelector('.btn-hapus-baris');
                if (tombolHapus) {
                   tombolHapus.style.display = (semuaBaris.length > 1) ? 'inline-block' : 'none';
                }
            });
        };
        tombolTambah.addEventListener('click', function() {
            const barisPertama = container.querySelector('.laporan-baris');
            if(barisPertama) {
                const barisBaru = barisPertama.cloneNode(true);
                barisBaru.querySelectorAll('input, select').forEach(input => input.value = '');
                container.appendChild(barisBaru);
                updateTombolHapus();
            }
        });
        container.addEventListener('click', function(e) {
            const tombolHapus = e.target.closest('.btn-hapus-baris');
            if (tombolHapus) {
                const baris = tombolHapus.closest('.laporan-baris');
                 if (baris && container.querySelectorAll('.laporan-baris').length > 1) {
                    baris.remove();
                    updateTombolHapus();
                 }
            }
        });
        updateTombolHapus();
    }

    // 3. Logika Modal Peringatan
    const logbookModalElement = document.getElementById('logbookModal');
    if (logbookModalElement) {
        const logbookModal = new bootstrap.Modal(logbookModalElement);
        const logbookTopik = document.getElementById('logbook-topik');
        const logbookIsi = document.getElementById('logbook-isi');
        const tombolPeringatan = document.getElementById('kirimPeringatanMassal');
        if(tombolPeringatan && logbookTopik && logbookIsi) {
            tombolPeringatan.addEventListener('click', function(e) {
                e.preventDefault();
                const nilaiItems = document.querySelectorAll('.nilai-item');
                if(nilaiItems.length === 0) return;
                let pesan = 'Berdasarkan laporan, terdapat beberapa nilai yang perlu mendapat perhatian khusus:\n\n';
                nilaiItems.forEach(item => {
                    pesan += '- ' + item.dataset.mk + ' (Nilai: ' + item.dataset.nilai + ')\n';
                });
                pesan += '\nMohon segera diskusikan rencana perbaikan untuk mata kuliah di atas.';
                logbookTopik.value = 'Peringatan Akademik Terkait Nilai';
                logbookIsi.value = pesan;
                logbookModal.show();
            });
        }
    }


    // 4. Logika Modal Pencapaian
    const modalPencapaian = document.getElementById('modalUpdateSemuaPencapaian');
    if (modalPencapaian) {
        modalPencapaian.addEventListener('change', function(event) {
            if (event.target.classList.contains('form-check-input') && event.target.type === 'checkbox') {
                const toggle = event.target;
                const dateContainer = toggle.closest('.mb-2').querySelector('.date-container');
                const dateInput = dateContainer ? dateContainer.querySelector('input[type="date"]') : null;
                if(dateContainer && dateInput) {
                    if (toggle.checked) {
                        dateContainer.style.display = 'block';
                        if (dateInput.value === '') {
                             const today = new Date();
                             const yyyy = today.getFullYear();
                             const mm = String(today.getMonth() + 1).padStart(2, '0');
                             const dd = String(today.getDate()).padStart(2, '0');
                             dateInput.value = `${yyyy}-${mm}-${dd}`;
                        }
                    } else {
                        dateContainer.style.display = 'none';
                        dateInput.value = '';
                    }
                }
            }
        });
    }

});
</script>

<?php
// ==========================================================
// --- BAGIAN 5: TUTUP KONEKSI & PANGGIL FOOTER ---
// ==========================================================

// Tutup semua statement yang mungkin dibuka
if (isset($stmt_mhs) && $stmt_mhs instanceof mysqli_stmt) $stmt_mhs->close();
if (isset($stmt_dokumen) && $stmt_dokumen instanceof mysqli_stmt) $stmt_dokumen->close();
if (isset($stmt_chart) && $stmt_chart instanceof mysqli_stmt) $stmt_chart->close();
if (isset($stmt_konsul) && $stmt_konsul instanceof mysqli_stmt) $stmt_konsul->close();

$conn->close();
require 'templates/footer.php';
?>