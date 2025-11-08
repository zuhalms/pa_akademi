<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Keamanan: Pastikan yang mengakses adalah dosen yang login dan ada NIM
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen' || !isset($_GET['nim'])) {
    exit('Akses ditolak. Silakan login terlebih dahulu.');
}

// Validasi FPDF library
if (!file_exists('fpdf/fpdf.php')) {
    die('Error: Library FPDF tidak ditemukan di fpdf/fpdf.php');
}
require_once 'fpdf/fpdf.php';

// Include konfigurasi database (otomatis XAMPP atau InfinityFree)
require_once 'config.php';

// Validasi input
$nim = trim($_GET['nim']);
$id_dosen = $_SESSION['user_id'];

if (empty($nim) || !is_numeric($id_dosen)) {
    die('Parameter tidak valid');
}

// ==========================================================
// === AMBIL SEMUA DATA YANG DIPERLUKAN ===
// ==========================================================

// 1. Data Mahasiswa Lengkap dan Dosen PA (dengan prepared statement)
$mhs_stmt = $conn->prepare("
    SELECT m.*, p.nama_prodi, d.nama_dosen, d.nidn_dosen
    FROM mahasiswa m
    JOIN dosen d ON m.id_dosen_pa = d.id_dosen
    JOIN program_studi p ON m.id_prodi = p.id_prodi
    WHERE m.nim = ? AND m.id_dosen_pa = ?
");

if (!$mhs_stmt) {
    die('Error prepare mahasiswa: ' . $conn->error);
}

$mhs_stmt->bind_param("si", $nim, $id_dosen);
$mhs_stmt->execute();
$mhs_data = $mhs_stmt->get_result()->fetch_assoc();
$mhs_stmt->close();

if (!$mhs_data) {
    die('Data mahasiswa tidak ditemukan atau Anda tidak memiliki akses.');
}

// 2. Logbook peringatan nilai yang paling BARU (dengan prepared statement)
$logbook_stmt = $conn->prepare("
    SELECT * 
    FROM logbook 
    WHERE nim_mahasiswa = ? AND topik_bimbingan = 'Peringatan Akademik Terkait Nilai' 
    ORDER BY tanggal_bimbingan DESC, created_at DESC 
    LIMIT 1
");
$logbook_stmt->bind_param("s", $nim);
$logbook_stmt->execute();
$logbook_result = $logbook_stmt->get_result();
$logbook_stmt->close();

// 3. Data Kemajuan Studi (Milestones)
$daftar_pencapaian_valid = [
    'Konsultasi Judul',
    'Seminar Proposal', 
    'Ujian Komperehensif', 
    'Seminar Hasil', 
    'Ujian Skripsi (Yudisium)', 
    'Publikasi Jurnal'
];

$stmt_pencapaian = $conn->prepare("
    SELECT nama_pencapaian, status, tanggal_selesai 
    FROM pencapaian 
    WHERE nim_mahasiswa = ?
");
$stmt_pencapaian->bind_param("s", $nim);
$stmt_pencapaian->execute();
$result_pencapaian = $stmt_pencapaian->get_result();

$status_pencapaian = [];
while ($row = $result_pencapaian->fetch_assoc()) {
    $status_pencapaian[$row['nama_pencapaian']] = $row;
}
$stmt_pencapaian->close();

// 4. Data nilai bermasalah yang AKTIF (dengan prepared statement)
$nilai_kritis_result = null;
if ($conn->query("SHOW TABLES LIKE 'nilai_bermasalah'")->num_rows > 0) {
    $stmt_nilai = $conn->prepare("
        SELECT nama_mk, semester_diambil, nilai_huruf 
        FROM nilai_bermasalah 
        WHERE nim_mahasiswa = ? 
        ORDER BY semester_diambil ASC
    ");
    $stmt_nilai->bind_param("s", $nim);
    $stmt_nilai->execute();
    $nilai_kritis_result = $stmt_nilai->get_result();
    $stmt_nilai->close();
}

// 5. Data Riwayat Akademik (dengan prepared statement)
$riwayat_data = [];
if ($conn->query("SHOW TABLES LIKE 'riwayat_akademik'")->num_rows > 0) {
    $riwayat_stmt = $conn->prepare("
        SELECT semester, ip_semester, sks_semester 
        FROM riwayat_akademik 
        WHERE nim_mahasiswa = ? 
        ORDER BY semester ASC
    ");
    $riwayat_stmt->bind_param("s", $nim);
    $riwayat_stmt->execute();
    $riwayat_result = $riwayat_stmt->get_result();
    while ($row = $riwayat_result->fetch_assoc()) {
        $riwayat_data[] = $row;
    }
    $riwayat_stmt->close();
}

// 6. Data Evaluasi Soft Skill Terakhir (dengan prepared statement)
$evaluasi_data = [];
$periode_evaluasi_terakhir = '';

if ($conn->query("SHOW TABLES LIKE 'evaluasi_softskill'")->num_rows > 0) {
    $periode_stmt = $conn->prepare("
        SELECT periode_evaluasi 
        FROM evaluasi_softskill 
        WHERE nim_mahasiswa = ? 
        ORDER BY periode_evaluasi DESC 
        LIMIT 1
    ");
    $periode_stmt->bind_param("s", $nim);
    $periode_stmt->execute();
    $periode_result = $periode_stmt->get_result();
    if ($periode_row = $periode_result->fetch_assoc()) {
        $periode_evaluasi_terakhir = htmlspecialchars($periode_row['periode_evaluasi']);
    }
    $periode_stmt->close();

    if (!empty($periode_evaluasi_terakhir)) {
        $eval_stmt = $conn->prepare("
            SELECT kategori, skor 
            FROM evaluasi_softskill 
            WHERE nim_mahasiswa = ? AND periode_evaluasi = ? 
            ORDER BY FIELD(kategori, 'Disiplin & Komitmen', 'Partisipasi & Keaktifan', 'Etika & Sopan Santun', 'Kepemimpinan & Kerjasama')
        ");
        $eval_stmt->bind_param("ss", $nim, $periode_evaluasi_terakhir);
        $eval_stmt->execute();
        $eval_result = $eval_stmt->get_result();
        while ($row = $eval_result->fetch_assoc()) {
            $evaluasi_data[$row['kategori']] = $row;
        }
        $eval_stmt->close();
    }
}

$kategori_softskill_ordered = [
    'Disiplin & Komitmen', 
    'Partisipasi & Keaktifan', 
    'Etika & Sopan Santun', 
    'Kepemimpinan & Kerjasama'
];

// ==========================================================
// === Class PDF yang Diperbaiki ===
// ==========================================================

class PDF_Enhanced extends FPDF {
    var $widths;
    var $aligns;
    var $campusGreen = [4, 157, 111];

    function SetWidths($w) { 
        $this->widths = $w; 
    }
    
    function SetAligns($a) { 
        $this->aligns = $a; 
    }

    function Header() {
        $logoPath = 'assets/logo_uin.png';
        $pageWidth = $this->GetPageWidth();
        $margin = 15;
        $contentWidth = $pageWidth - (2 * $margin);

        $logoX = $margin;
        $logoY = 8;
        $logoWidth = 25;
        $logoHeight = 25;
        
        if (file_exists($logoPath)) {
            $this->Image($logoPath, $logoX, $logoY, $logoWidth);
        }

        $textX = $logoX + $logoWidth + 5;
        $textWidth = $contentWidth - $logoWidth - 5;
        $textStartY = $logoY + 2;

        $this->SetY($textStartY);
        $this->SetX($textX);
        $this->SetFont('Times', 'B', 11);
        $this->Cell($textWidth, 5, 'KEMENTERIAN AGAMA REPUBLIK INDONESIA', 0, 1, 'C');

        $this->SetX($textX);
        $this->SetFont('Times', 'B', 14);
        $this->Cell($textWidth, 6, 'UNIVERSITAS ISLAM NEGERI PALOPO', 0, 1, 'C');

        $this->SetX($textX);
        $this->SetFont('Times', 'B', 12);
        $this->Cell($textWidth, 6, 'FAKULTAS SYARIAH DAN HUKUM', 0, 1, 'C');

        $this->SetX($textX);
        $this->SetFont('Times', '', 9);
        $this->MultiCell($textWidth, 4, 'Jalan Agatis II, Balandai, Kecamatan Bara, Kota Palopo, Sulawesi Selatan 91914', 0, 'C');

        $this->SetX($textX);
        $this->SetFont('Times', '', 9);
        $this->Cell($textWidth, 4, 'Telp: +62821-xxxxxxx | Email: kontak@uinpalopo.ac.id | Website: www.uinpalopo.ac.id', 0, 1, 'C');

        $yAfterText = $this->GetY();
        $yAfterLogo = $logoY + $logoHeight;
        $lineY = max($yAfterText, $yAfterLogo) + 2;

        $this->SetY($lineY);
        $this->SetLineWidth(0.5);
        $this->SetDrawColor(0);
        $this->Line($margin, $this->GetY(), $pageWidth - $margin, $this->GetY());
        $this->Ln(0.5);
        $this->SetLineWidth(0.2);
        $this->Line($margin, $this->GetY(), $pageWidth - $margin, $this->GetY());

        $this->SetY($this->GetY() + 8);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(150);
        $this->Cell(0, 5, 'Dokumen ini dihasilkan secara otomatis oleh SMART-BA | ' . date('d M Y, H:i'), 0, 0, 'L');
        $this->Cell(0, 5, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    function SectionTitle($title, $iconSymbol = '') {
        $this->Ln(8);
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor($this->campusGreen[0], $this->campusGreen[1], $this->campusGreen[2]);
        $this->SetTextColor(255);
        $displayTitle = $iconSymbol ? $iconSymbol . ' ' . $title : $title;
        $this->Cell(0, 10, $displayTitle, 0, 1, 'L', true);
        $this->Ln(4);
        $this->SetTextColor(0);
    }

    function DataRow($label, $value) {
        $this->SetFont('Arial', '', 10);
        $this->Cell(45, 6, $label, 0, 0, 'L');
        $this->Cell(5, 6, ':', 0, 0, 'C');
        $this->SetFont('Arial', 'B', 10);
        $this->MultiCell(0, 6, htmlspecialchars($value), 0, 'L');
        $this->SetFont('Arial', '', 10);
    }

    function Row($data, $is_header = false, $is_warning = false, $lineHeight = 6) {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = $lineHeight * $nb;
        $this->CheckPageBreak($h);

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $x = $this->GetX();
            $y = $this->GetY();
            if ($is_header) $this->SetFillColor(230, 230, 230);
            elseif ($is_warning) $this->SetFillColor(255, 243, 205);
            else $this->SetFillColor(255);
            $this->Rect($x, $y, $w, $h, 'DF');
            $this->MultiCell($w, $lineHeight, $data[$i], 0, $a, false);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h) {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += ($cw[$c] ?? $cw['?']);
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) $i++;
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }
}

// === Fungsi Helper ===
function getKeteranganIP($ip) {
    if ($ip >= 3.51) return 'Sangat Memuaskan';
    if ($ip >= 3.01) return 'Memuaskan';
    if ($ip >= 2.76) return 'Cukup';
    return 'Kurang';
}

// ==========================================================
// === MULAI MEMBUAT PDF ===
// ==========================================================

$pdf = new PDF_Enhanced('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);
$pdf->SetMargins(15, 15, 15);

// === JUDUL UTAMA ===
$pdf->SetY(45);
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor($pdf->campusGreen[0], $pdf->campusGreen[1], $pdf->campusGreen[2]);
$pdf->Cell(0, 10, 'LAPORAN AKADEMIK MAHASISWA', 0, 1, 'C');
$pdf->SetTextColor(0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, 'Periode: ' . date('Y'), 0, 1, 'C');
$pdf->Ln(5);

// === BAGIAN I: DATA DIRI MAHASISWA ===
$pdf->SectionTitle('PROFIL MAHASISWA');

$pdf->DataRow('Nama Lengkap', $mhs_data['nama_mahasiswa']);
$pdf->DataRow('NIM', $nim);
$pdf->DataRow('Program Studi', $mhs_data['nama_prodi']);
$pdf->DataRow('Status Akademik', $mhs_data['status']);
$pdf->DataRow('IPK', number_format($mhs_data['ipk'], 2));
$pdf->DataRow('Total SKS', $mhs_data['total_sks'] ?? '0');
$pdf->Ln(2);
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(45, 6, 'Dosen P.A.', 0, 0, 'L');
$pdf->Cell(5, 6, ':', 0, 0, 'C');
$pdf->Cell(0, 6, htmlspecialchars($mhs_data['nama_dosen']), 0, 1, 'L');
$pdf->Ln(5);

// === BAGIAN II: RIWAYAT AKADEMIK ===
$pdf->SectionTitle('RIWAYAT AKADEMIK PER SEMESTER');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(0);
$pdf->Cell(25, 8, 'Semester', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'IP Semester', 1, 0, 'C', true);
$pdf->Cell(35, 8, 'SKS Semester', 1, 0, 'C', true);
$pdf->Cell(85, 8, 'Keterangan', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$total_sks_keseluruhan = 0;
$total_bobot_kali_sks = 0;

if (!empty($riwayat_data)) {
    foreach ($riwayat_data as $data) {
        $ip = floatval($data['ip_semester']);
        $sks = intval($data['sks_semester']);
        $keterangan = getKeteranganIP($ip);
        $pdf->Cell(25, 7, $data['semester'], 1, 0, 'C');
        $pdf->Cell(35, 7, number_format($ip, 2), 1, 0, 'C');
        $pdf->Cell(35, 7, $sks, 1, 0, 'C');
        $pdf->Cell(85, 7, $keterangan, 1, 1, 'L');
        $total_sks_keseluruhan += $sks;
        $total_bobot_kali_sks += ($ip * $sks);
    }
} else {
    $pdf->Cell(180, 10, 'Data riwayat akademik belum diinput.', 1, 1, 'C');
}

if (!empty($riwayat_data)) {
    $ipk_keseluruhan = ($total_sks_keseluruhan > 0) ? ($total_bobot_kali_sks / $total_sks_keseluruhan) : 0;
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(95, 8, 'IPK Keseluruhan (Berdasarkan Riwayat):', 1, 0, 'R', true);
    $pdf->Cell(85, 8, number_format($ipk_keseluruhan, 2), 1, 1, 'C', true);
}

// === BAGIAN III: KEMAJUAN STUDI ===
$pdf->SectionTitle('PENCAPAIAN KEMAJUAN STUDI');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
$pdf->Cell(90, 8, 'Tahapan Studi', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Status', 1, 0, 'C', true);
$pdf->Cell(40, 8, 'Tanggal Selesai', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$no = 1;
foreach ($daftar_pencapaian_valid as $item) {
    $status = 'Belum Selesai';
    $tanggal = '-';
    $isDone = false;
    
    if (isset($status_pencapaian[$item]) && $status_pencapaian[$item]['status'] == 'Selesai') {
        $status = 'Selesai';
        $tanggal = !empty($status_pencapaian[$item]['tanggal_selesai']) 
            ? date('d M Y', strtotime($status_pencapaian[$item]['tanggal_selesai'])) 
            : date('d M Y');
        $isDone = true;
    }
    
    if ($isDone) {
        $pdf->SetFillColor(220, 255, 220);
        $pdf->SetTextColor(0, 100, 0);
    } else {
        $pdf->SetFillColor(255);
        $pdf->SetTextColor(0);
    }
    
    $pdf->Cell(10, 7, $no++, 1, 0, 'C', $isDone);
    $pdf->Cell(90, 7, $item, 1, 0, 'L', $isDone);
    $pdf->Cell(40, 7, $status, 1, 0, 'C', $isDone);
    $pdf->Cell(40, 7, $tanggal, 1, 1, 'C', $isDone);
    $pdf->SetTextColor(0);
}

// === BAGIAN IV: EVALUASI SOFT SKILL ===
$pdf->SectionTitle('EVALUASI SOFT SKILL TERAKHIR');

if (!empty($evaluasi_data)) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 6, 'Periode Evaluasi: ' . htmlspecialchars($periode_evaluasi_terakhir), 0, 1, 'L');
    $pdf->Ln(2);

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(140, 8, 'Kategori Penilaian', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Skor (1-5)', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 10);
    $totalSkor = 0;
    $jumlahKategori = 0;
    
    foreach ($kategori_softskill_ordered as $kategori) {
        if (isset($evaluasi_data[$kategori])) {
            $eval = $evaluasi_data[$kategori];
            $skor = intval($eval['skor']);
            $pdf->Cell(140, 7, $kategori, 1, 0, 'L');
            $pdf->Cell(40, 7, $skor, 1, 1, 'C');
            $totalSkor += $skor;
            $jumlahKategori++;
        } else {
            $pdf->Cell(140, 7, $kategori, 1, 0, 'L');
            $pdf->Cell(40, 7, '-', 1, 1, 'C');
        }
    }

    $rataRataSkor = ($jumlahKategori > 0) ? $totalSkor / $jumlahKategori : 0;
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(140, 8, 'Rata-rata Skor:', 1, 0, 'R', true);
    $pdf->Cell(40, 8, number_format($rataRataSkor, 2), 1, 1, 'C', true);
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 8, 'Data evaluasi soft skill belum tersedia.', 0, 1, 'L');
}

// === BAGIAN V: CATATAN AKADEMIK PENTING ===
$pdf->SectionTitle('CATATAN AKADEMIK PENTING');

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor($pdf->campusGreen[0], $pdf->campusGreen[1], $pdf->campusGreen[2]);
$pdf->Cell(0, 8, 'Peringatan Nilai Bermasalah (Aktif)', 0, 1, 'L');
$pdf->SetTextColor(0);

if ($nilai_kritis_result && $nilai_kritis_result->num_rows > 0) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(255, 243, 205);
    $pdf->Cell(15, 7, 'No', 1, 0, 'C', true);
    $pdf->Cell(105, 7, 'Nama Mata Kuliah', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Semester', 1, 0, 'C', true);
    $pdf->Cell(30, 7, 'Nilai', 1, 1, 'C', true);

    $pdf->SetFont('Arial', '', 10);
    $no = 1;
    while ($nilai = $nilai_kritis_result->fetch_assoc()) {
        $pdf->SetFillColor(255, 249, 230);
        $pdf->Cell(15, 7, $no++, 1, 0, 'C', true);
        $pdf->Cell(105, 7, htmlspecialchars($nilai['nama_mk']), 1, 0, 'L', true);
        $pdf->Cell(30, 7, $nilai['semester_diambil'], 1, 0, 'C', true);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(200, 0, 0);
        $pdf->Cell(30, 7, htmlspecialchars($nilai['nilai_huruf']), 1, 1, 'C', true);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(0);
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(0, 100, 0);
    $pdf->Cell(0, 8, 'Tidak ada laporan nilai bermasalah yang aktif saat ini.', 0, 1, 'L');
    $pdf->SetTextColor(0);
}
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor($pdf->campusGreen[0], $pdf->campusGreen[1], $pdf->campusGreen[2]);
$pdf->Cell(0, 8, 'Tindak Lanjut Peringatan Nilai Terbaru', 0, 1, 'L');
$pdf->SetTextColor(0);

if ($logbook_result && $logbook_result->num_rows > 0) {
    $pdf->SetFont('Arial', '', 10);
    $log = $logbook_result->fetch_assoc();
    $pdf->SetFillColor(255, 243, 205);
    $pdf->SetWidths([35, 145]);
    $pdf->SetAligns(['L', 'L']);
    $pdf->Row(['Tanggal:', date('d M Y', strtotime($log['tanggal_bimbingan']))], false, true, 5);
    $pdf->Row(['Topik:', htmlspecialchars($log['topik_bimbingan'])], false, true, 5);
    $pdf->Row(['Pembahasan:', htmlspecialchars($log['isi_bimbingan'])], false, true, 5);
    if (!empty($log['tindak_lanjut'])) {
        $pdf->Row(['Tindak Lanjut:', htmlspecialchars($log['tindak_lanjut'])], false, true, 5);
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 8, 'Belum ada catatan tindak lanjut untuk peringatan nilai.', 0, 1, 'L');
}

// === TANDA TANGAN ===
$pdf->Ln(15);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(120, 6, '', 0, 0);
$pdf->Cell(0, 6, 'Palopo, ' . date('d F Y'), 0, 1, 'L');
$pdf->Cell(120, 6, '', 0, 0);
$pdf->Cell(0, 6, 'Dosen Pembimbing Akademik,', 0, 1, 'L');
$pdf->Ln(18);
$pdf->Cell(120, 6, '', 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 6, htmlspecialchars($mhs_data['nama_dosen']), 0, 1, 'L');
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(120, 6, '', 0, 0);
$pdf->Cell(0, 6, 'NIDN: ' . htmlspecialchars($mhs_data['nidn_dosen'] ?? '-'), 0, 1, 'L');

// === OUTPUT PDF ===
$nama_file = 'Laporan_Akademik_' . str_replace(' ', '_', $mhs_data['nama_mahasiswa']) . '_' . $nim . '_' . date('Y-m-d') . '.pdf';
$pdf->Output('I', $nama_file); // 'I' untuk tampil di browser, 'D' untuk download

$conn->close();
?>
