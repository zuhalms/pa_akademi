<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Keamanan - Cek session dan parameter
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_GET['nim'])) { 
    die('Akses ditolak'); 
}

// Include konfigurasi database (otomatis XAMPP atau InfinityFree)
require_once 'config.php';

// Include FPDF library
if (!file_exists('fpdf/fpdf.php')) {
    die('Error: Library FPDF tidak ditemukan di fpdf/fpdf.php');
}
require_once 'fpdf/fpdf.php';

// Validasi dan sanitasi input
$nim = trim($_GET['nim']);
$id_dosen = $_SESSION['user_id'];

if (empty($nim) || !is_numeric($id_dosen)) {
    die('Parameter tidak valid');
}

// Ambil data mahasiswa dengan prepared statement (lebih aman)
$stmt_mhs = $conn->prepare("
    SELECT m.nama_mahasiswa, p.nama_prodi 
    FROM mahasiswa m 
    JOIN program_studi p ON m.id_prodi = p.id_prodi 
    WHERE m.nim = ?
");
if (!$stmt_mhs) {
    die('Error: ' . $conn->error);
}

$stmt_mhs->bind_param("s", $nim);
$stmt_mhs->execute();
$result_mhs = $stmt_mhs->get_result();

if ($result_mhs->num_rows == 0) {
    die('Data mahasiswa tidak ditemukan');
}

$mhs_data = $result_mhs->fetch_assoc();
$stmt_mhs->close();

// Ambil data dosen dengan prepared statement
$stmt_dosen = $conn->prepare("
    SELECT nama_dosen, nidn_dosen 
    FROM dosen 
    WHERE id_dosen = ?
");
if (!$stmt_dosen) {
    die('Error: ' . $conn->error);
}

$stmt_dosen->bind_param("i", $id_dosen);
$stmt_dosen->execute();
$result_dosen = $stmt_dosen->get_result();

if ($result_dosen->num_rows == 0) {
    die('Data dosen tidak ditemukan');
}

$dosen_data = $result_dosen->fetch_assoc();
$stmt_dosen->close();

$dosen_pa_name = htmlspecialchars($dosen_data['nama_dosen']);
$dosen_pa_nidn = htmlspecialchars($dosen_data['nidn_dosen']);

// Ambil data kemajuan pencapaian dengan prepared statement
$daftar_pencapaian = [
    'Seminar Proposal', 
    'Penelitian Selesai', 
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

$stmt_pencapaian->bind_param("s", $nim);
$stmt_pencapaian->execute();
$result_pencapaian = $stmt_pencapaian->get_result();

$status_pencapaian = [];
while ($row = $result_pencapaian->fetch_assoc()) { 
    $status_pencapaian[$row['nama_pencapaian']] = $row; 
}
$stmt_pencapaian->close();

// Definisi Class PDF
class PDF extends FPDF {
    public function Header() {
        // Header ini akan di-generate dari template
        // Kosongkan di sini karena sudah ada di template
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Buat instance PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

// Include template header laporan
if (file_exists('templates/report_header.php')) {
    include 'templates/report_header.php';
} else {
    // Fallback jika template tidak ada
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'UNIVERSITAS ISLAM NEGERI PALOPO', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'FAKULTAS SYARIAH DAN HUKUM', 0, 1, 'C');
    $pdf->Ln(5);
}

// Judul Laporan
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'LAPORAN KEMAJUAN STUDI MAHASISWA', 0, 1, 'C');
$pdf->Ln(5);

// Data Mahasiswa
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 7, 'Nama Mahasiswa', 0, 0); 
$pdf->Cell(5, 7, ':', 0, 0); 
$pdf->Cell(0, 7, htmlspecialchars($mhs_data['nama_mahasiswa']), 0, 1);

$pdf->Cell(40, 7, 'NIM', 0, 0); 
$pdf->Cell(5, 7, ':', 0, 0); 
$pdf->Cell(0, 7, htmlspecialchars($nim), 0, 1);

$pdf->Cell(40, 7, 'Program Studi', 0, 0); 
$pdf->Cell(5, 7, ':', 0, 0); 
$pdf->Cell(0, 7, htmlspecialchars($mhs_data['nama_prodi']), 0, 1);

$pdf->Ln(10);

// Header Tabel Pencapaian
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230);
$pdf->SetDrawColor(0, 0, 0);

$pdf->Cell(10, 10, 'No', 1, 0, 'C', true);
$pdf->Cell(100, 10, 'Pencapaian', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Status', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Tanggal Selesai', 1, 1, 'C', true);

// Data Tabel Pencapaian
$pdf->SetFont('Arial', '', 11);
$pdf->SetDrawColor(0, 0, 0);
$no = 1;
$jumlah_selesai = 0;

foreach ($daftar_pencapaian as $item) {
    $status = 'Belum Selesai';
    $tanggal = '-';
    $bg_color = false;
    
    // Cek apakah pencapaian sudah selesai
    if (isset($status_pencapaian[$item])) {
        if ($status_pencapaian[$item]['status'] == 'Selesai') {
            $status = 'Selesai';
            $tanggal = !empty($status_pencapaian[$item]['tanggal_selesai']) 
                ? date('d-m-Y', strtotime($status_pencapaian[$item]['tanggal_selesai'])) 
                : '-';
            $bg_color = true;
            $jumlah_selesai++;
        }
    }
    
    // Set warna background untuk item selesai
    if ($bg_color) {
        $pdf->SetFillColor(200, 255, 200); // Hijau muda
    } else {
        $pdf->SetFillColor(255, 255, 255); // Putih
    }
    
    $pdf->Cell(10, 10, $no++, 1, 0, 'C', $bg_color);
    $pdf->Cell(100, 10, $item, 1, 0, 'L', $bg_color);
    $pdf->Cell(40, 10, $status, 1, 0, 'C', $bg_color);
    $pdf->Cell(40, 10, $tanggal, 1, 1, 'C', $bg_color);
}

$pdf->SetFillColor(255, 255, 255);

// Ringkasan Progres
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 11);
$total_pencapaian = count($daftar_pencapaian);
$persentase = round(($jumlah_selesai / $total_pencapaian) * 100);

$pdf->Cell(0, 7, 'Ringkasan: ' . $jumlah_selesai . ' dari ' . $total_pencapaian . ' tahapan selesai (' . $persentase . '%)', 0, 1, 'L');

// Include template footer laporan
$pdf->Ln(10);
if (file_exists('templates/report_footer.php')) {
    include 'templates/report_footer.php';
} else {
    // Fallback jika template tidak ada
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'Dosen Pembimbing Akademik:', 0, 1);
    $pdf->Ln(15);
    $pdf->Cell(0, 5, '_________________________', 0, 1);
    $pdf->Cell(0, 5, $dosen_pa_name, 0, 1);
    $pdf->Cell(0, 5, 'NIDN: ' . $dosen_pa_nidn, 0, 1);
}

// Output PDF
$filename = 'Laporan_Kemajuan_Studi_' . htmlspecialchars($nim) . '_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $filename);

// Tutup koneksi
$conn->close();
?>
