<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Keamanan - Cek session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Keamanan: Pastikan yang mengakses adalah mahasiswa yang sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'mahasiswa') {
    die("Akses ditolak. Silakan login sebagai mahasiswa.");
}

// Include konfigurasi database (otomatis XAMPP atau InfinityFree)
require_once 'config.php';

// Validasi FPDF library
if (!file_exists('fpdf/fpdf.php')) {
    die('Error: Library FPDF tidak ditemukan di fpdf/fpdf.php');
}
require_once 'fpdf/fpdf.php';

$nim_mahasiswa_login = $_SESSION['user_id'];

// Validasi input
if (empty($nim_mahasiswa_login)) {
    die('Error: NIM mahasiswa tidak valid');
}

// Ambil data mahasiswa dan dosen PA dengan prepared statement
$stmt_mhs = $conn->prepare("
    SELECT m.nama_mahasiswa, m.nim, d.nama_dosen 
    FROM mahasiswa m 
    JOIN dosen d ON m.id_dosen_pa = d.id_dosen 
    WHERE m.nim = ?
");

if (!$stmt_mhs) {
    die('Error: ' . $conn->error);
}

$stmt_mhs->bind_param("s", $nim_mahasiswa_login);
$stmt_mhs->execute();
$mahasiswa = $stmt_mhs->get_result()->fetch_assoc();
$stmt_mhs->close();

if (!$mahasiswa) { 
    die("Data mahasiswa tidak ditemukan."); 
}

// Ambil data pencapaian (milestones) dengan prepared statement
$daftar_pencapaian = [
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

if (!$stmt_pencapaian) {
    die('Error: ' . $conn->error);
}

$stmt_pencapaian->bind_param("s", $nim_mahasiswa_login);
$stmt_pencapaian->execute();
$result_pencapaian = $stmt_pencapaian->get_result();

$status_pencapaian = [];
$jumlah_selesai = 0;

while ($row = $result_pencapaian->fetch_assoc()) {
    $status_pencapaian[$row['nama_pencapaian']] = $row;
    if ($row['status'] == 'Selesai') { 
        $jumlah_selesai++; 
    }
}
$stmt_pencapaian->close();

$total_pencapaian = count($daftar_pencapaian);
$persentase_kemajuan = ($total_pencapaian > 0) ? round(($jumlah_selesai / $total_pencapaian) * 100) : 0;

// Definisi Class PDF
class PDF extends FPDF {
    private $nim = '';
    
    public function __construct($nim = '') {
        parent::__construct();
        $this->nim = $nim;
    }
    
    public function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Laporan Kemajuan Studi Mahasiswa', 0, 1, 'C');
        $this->Ln(5);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Dicetak pada ' . date('d M Y, H:i') . ' | Halaman ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Inisiasi PDF
$pdf = new PDF(htmlspecialchars($mahasiswa['nim']));
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);
$pdf->SetFont('Arial', '', 11);

// Tampilkan detail mahasiswa
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(40, 7, 'Nama Mahasiswa', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, ': ' . htmlspecialchars($mahasiswa['nama_mahasiswa']), 0, 1);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(40, 7, 'NIM', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, ': ' . htmlspecialchars($mahasiswa['nim']), 0, 1);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(40, 7, 'Dosen PA', 0, 0);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, ': ' . htmlspecialchars($mahasiswa['nama_dosen']), 0, 1);

$pdf->Ln(10);

// Tampilkan Progress Bar
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, 'Persentase Kemajuan', 0, 1);

// Latar bar (warna abu-abu)
$pdf->SetFillColor(230, 230, 230);
$pdf->Rect(10, $pdf->GetY(), 190, 8, 'F');

// Bar kemajuan (warna hijau)
$pdf->SetFillColor(40, 167, 69);
if ($persentase_kemajuan > 0) {
    $pdf->Rect(10, $pdf->GetY(), 190 * ($persentase_kemajuan / 100), 8, 'F');
}

// Teks persentase
$pdf->SetY($pdf->GetY() - 0.5);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 8, $persentase_kemajuan . '% Selesai (' . $jumlah_selesai . ' dari ' . $total_pencapaian . ')', 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(10);

// Tampilkan Checklist Pencapaian
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, 'Detail Pencapaian (Milestones)', 0, 1);

// Header Tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetDrawColor(0, 0, 0);

$pdf->Cell(20, 8, 'Status', 1, 0, 'C', true);
$pdf->Cell(120, 8, 'Nama Pencapaian', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Tanggal Selesai', 1, 1, 'C', true);

// Data Tabel Pencapaian
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(255, 255, 255);

foreach ($daftar_pencapaian as $item) {
    $is_selesai = isset($status_pencapaian[$item]) && $status_pencapaian[$item]['status'] == 'Selesai';
    
    // Tentukan tanggal
    $tanggal = '-';
    if ($is_selesai && !empty($status_pencapaian[$item]['tanggal_selesai'])) {
        $tanggal = date('d F Y', strtotime($status_pencapaian[$item]['tanggal_selesai']));
    }
    
    // Icon status
    $status_icon = $is_selesai ? '✓ Selesai' : '-';
    
    // Warna background untuk item selesai
    $bg_fill = $is_selesai;
    if ($bg_fill) {
        $pdf->SetFillColor(200, 255, 200); // Hijau muda
    }
    
    $pdf->Cell(20, 8, $status_icon, 1, 0, 'C', $bg_fill);
    $pdf->Cell(120, 8, ' ' . $item, 1, 0, 'L', $bg_fill);
    $pdf->Cell(50, 8, $tanggal, 1, 1, 'C', $bg_fill);
    
    // Reset warna
    if ($bg_fill) {
        $pdf->SetFillColor(255, 255, 255);
    }
}

// Tampilkan PDF di browser atau download
$filename = 'Laporan_Kemajuan_' . htmlspecialchars($mahasiswa['nim']) . '_' . date('Y-m-d') . '.pdf';

// Gunakan 'I' untuk tampil di browser, atau 'D' untuk download
$pdf->Output('I', $filename);

// Tutup koneksi
$conn->close();
?>
