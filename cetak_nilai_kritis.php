<?php
session_start();

// Keamanan: Pastikan yang mengakses adalah dosen yang login dan ada NIM yang dikirim
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen' || !isset($_GET['nim'])) {
    exit('Akses ditolak. Silakan login terlebih dahulu.');
}

require('fpdf/fpdf.php'); // Pastikan library FPDF ada di folder fpdf/

// Koneksi ke database
$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
  //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { 
    die("Koneksi gagal: " . $conn->connect_error); 
}

$nim = $_GET['nim'];
$id_dosen = $_SESSION['user_id'];

// Ambil data mahasiswa dan dosen PA-nya
$mhs_stmt = $conn->prepare("
    SELECT m.nama_mahasiswa, p.nama_prodi, d.nama_dosen, d.nidn_dosen
    FROM mahasiswa m
    JOIN dosen d ON m.id_dosen_pa = d.id_dosen
    JOIN program_studi p ON m.id_prodi = p.id_prodi
    WHERE m.nim = ? AND m.id_dosen_pa = ?
");
$mhs_stmt->bind_param("si", $nim, $id_dosen);
$mhs_stmt->execute();
$mhs_data = $mhs_stmt->get_result()->fetch_assoc();

if (!$mhs_data) {
    exit('Data mahasiswa tidak ditemukan atau Anda tidak memiliki akses.');
}
$dosen_pa_name = $mhs_data['nama_dosen'];
$dosen_pa_nidn = $mhs_data['nidn_dosen'];

// Ambil data nilai kritis (D atau E) untuk mahasiswa ini
$nilai_kritis_result = $conn->query("
    SELECT nama_mk, semester_diambil, nilai_huruf 
    FROM nilai_mahasiswa 
    WHERE nim_mahasiswa = '{$nim}' AND nilai_huruf IN ('D', 'E') 
    ORDER BY semester_diambil ASC
");

class PDF extends FPDF {
    // Fungsi Header dan Footer standar
    function Header() { /* Dibiarkan kosong karena kita akan menggunakan template header terpisah */ }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Dokumen ini dicetak melalui sistem SMART-BA pada ' . date('d/m/Y H:i'),0,0,'L');
        $this->Cell(0,10,'Halaman '.$this->PageNo(),0,0,'R');
    }
}

// Inisialisasi PDF
$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();

// === MEMANGGIL KOP SURAT ===
include 'templates/report_header.php';

// Judul Laporan
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'LAPORAN NILAI KRITIS MAHASISWA', 0, 1, 'C');
$pdf->Ln(5);

// Informasi Mahasiswa
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 7, 'Nama Mahasiswa', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $mhs_data['nama_mahasiswa'], 0, 1);
$pdf->Cell(40, 7, 'NIM', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $nim, 0, 1);
$pdf->Cell(40, 7, 'Program Studi', 0, 0);
$pdf->Cell(5, 7, ':', 0, 0);
$pdf->Cell(0, 7, $mhs_data['nama_prodi'], 0, 1);
$pdf->Ln(10);

// Tabel Nilai Kritis
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(230, 230, 230); // Warna abu-abu untuk header tabel
$pdf->Cell(15, 10, 'No', 1, 0, 'C', true);
$pdf->Cell(115, 10, 'Nama Mata Kuliah', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Semester', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Nilai', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 12);
$no = 1;
if ($nilai_kritis_result->num_rows > 0) {
    while ($nilai = $nilai_kritis_result->fetch_assoc()) {
        $pdf->Cell(15, 10, $no++, 1, 0, 'C');
        $pdf->Cell(115, 10, $nilai['nama_mk'], 1, 0);
        $pdf->Cell(30, 10, $nilai['semester_diambil'], 1, 0, 'C');
        // Memberi warna merah pada nilai D atau E
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(220, 53, 69); // Warna Merah Bootstrap
        $pdf->Cell(30, 10, $nilai['nilai_huruf'], 1, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->SetTextColor(0, 0, 0); // Kembalikan warna teks ke hitam
    }
} else {
    $pdf->Cell(190, 10, 'Tidak ada laporan nilai kritis (D/E) yang tercatat.', 1, 1, 'C');
}

// === MEMANGGIL BLOK TANDA TANGAN ===
include 'templates/report_footer.php';

// Output file PDF
$pdf->Output('D', 'Laporan_Nilai_Kritis_' . $nim . '.pdf');

$conn->close();
?>