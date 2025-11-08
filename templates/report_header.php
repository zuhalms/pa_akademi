<?php
// templates/report_header.php
// Header laporan PDF dengan logo dan identitas institusi UIN Palopo

// Validasi file logo
$logo_path = 'assets/logo_uin.png';
if (!file_exists($logo_path)) {
    // Jika logo tidak ada, tampilkan pesan di log tapi jangan error
    error_log("Warning: Logo file tidak ditemukan di: " . $logo_path);
}

// Set Y position untuk header
$pdf->SetY(10);

// Tambahkan logo jika ada
if (file_exists($logo_path)) {
    $pdf->Image($logo_path, 10, 8, 25); // x, y, width
}

// Kop Surat - Kementerian
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 6, 'KEMENTERIAN AGAMA REPUBLIK INDONESIA', 0, 1, 'C');

// Kop Surat - Nama Universitas
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 8, 'UNIVERSITAS ISLAM NEGERI PALOPO', 0, 1, 'C');

// Kop Surat - Fakulas (Opsional)
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 6, 'FAKULTAS SYARIAH DAN HUKUM', 0, 1, 'C');

// Alamat
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 4, 'Jalan Agatis Kel. Balandai Kec. Bara Kota Palopo, Sulawesi Selatan 91914', 0, 1, 'C');

// Website dan Kontak
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 4, 'Website: www.uinpalopo.ac.id | Telepon: (0471) XXXXX | Email: fsh@uinpalopo.ac.id', 0, 1, 'C');

// Garis kop surat
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.8);
$pdf->Line(10, $pdf->GetY() + 2, 200, $pdf->GetY() + 2);

// Garis bawah yang lebih tipis
$pdf->SetLineWidth(0.3);
$pdf->Line(10, $pdf->GetY() + 4, 200, $pdf->GetY() + 4);

// Spasi setelah kop surat
$pdf->Ln(10);
?>
