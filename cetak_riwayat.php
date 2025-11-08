<?php
session_start();

// Keamanan: Pastikan yang mengakses adalah dosen atau mahasiswa yang login dan ada NIM
if (!isset($_SESSION['user_id']) || !isset($_GET['nim'])) {
    exit('Akses ditolak. Silakan login terlebih dahulu.');
}

require('fpdf/fpdf.php'); // Pastikan Anda sudah memiliki folder fpdf/ dengan library FPDF

// Koneksi ke database
$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
  //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

$nim = $_GET['nim'];

// Ambil data mahasiswa dan dosen PA-nya
$mhs_stmt = $conn->prepare("
    SELECT m.nama_mahasiswa, p.nama_prodi, d.nama_dosen, d.nidn_dosen
    FROM mahasiswa m
    JOIN dosen d ON m.id_dosen_pa = d.id_dosen
    JOIN program_studi p ON m.id_prodi = p.id_prodi
    WHERE m.nim = ?
");
$mhs_stmt->bind_param("s", $nim);
$mhs_stmt->execute();
$mhs_data = $mhs_stmt->get_result()->fetch_assoc();

if (!$mhs_data) {
    exit('Data mahasiswa tidak ditemukan.');
}
$dosen_pa_name = $mhs_data['nama_dosen'];
$dosen_pa_nidn = $mhs_data['nidn_dosen'];

// Ambil data riwayat bimbingan
$logbook_result = $conn->query("SELECT * FROM logbook WHERE nim_mahasiswa = '{$nim}' ORDER BY tanggal_bimbingan ASC");

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
$pdf->Cell(0, 10, 'LAPORAN RIWAYAT BIMBINGAN AKADEMIK', 0, 1, 'C');
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

// Tabel Riwayat Bimbingan
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230); // Warna abu-abu untuk header tabel
$pdf->Cell(10, 10, 'No', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Tanggal', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Topik Bimbingan', 1, 0, 'C', true);
$pdf->Cell(100, 10, 'Detail Pembahasan & Tindak Lanjut', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$no = 1;
if ($logbook_result->num_rows > 0) {
    while ($log = $logbook_result->fetch_assoc()) {
        $pdf->Cell(10, 10, $no++, 1, 0, 'C');
        $pdf->Cell(30, 10, date('d-m-Y', strtotime($log['tanggal_bimbingan'])), 1, 0, 'C');
        $pdf->Cell(50, 10, $log['topik_bimbingan'], 1, 0);

        // Menggunakan MultiCell untuk teks yang panjang agar bisa wrap
        $cellWidth = 100;
        $cellHeight = 10;
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->MultiCell($cellWidth, 5, "Pembahasan: " . $log['isi_bimbingan'] . "\n" . "Tindak Lanjut: " . $log['tindak_lanjut'], 0, 'L');
        $pdf->SetXY($x + $cellWidth, $y); // Kembali ke posisi setelah MultiCell
        $pdf->Cell($cellWidth, $cellHeight, '', 1, 1); // Buat border untuk MultiCell
    }
} else {
    $pdf->Cell(190, 10, 'Tidak ada riwayat bimbingan yang tercatat.', 1, 1, 'C');
}

// === MEMANGGIL BLOK TANDA TANGAN ===
include 'templates/report_footer.php';

$pdf->Output('D', 'Laporan_Bimbingan_' . $nim . '.pdf');

$conn->close();
?>