<?php
// templates/report_footer.php
// Footer laporan PDF dengan tanda tangan dosen pembimbing

if (!isset($dosen_pa_name) || !isset($dosen_pa_nidn)) {
    die('Error: Variabel dosen tidak tersedia');
}

// Spasi sebelum tanda tangan
$pdf->Ln(15);

// Baris Tanggal dan Lokasi
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(120); // Pindah ke kanan untuk kolom tanda tangan
$pdf->Cell(0, 6, 'Palopo, ' . date('d F Y'), 0, 1, 'L');

// Baris Judul Tanda Tangan
$pdf->Cell(120);
$pdf->Cell(0, 6, 'Dosen Pembimbing Akademik,', 0, 1, 'L');

// Spasi untuk tanda tangan (garis)
$pdf->Ln(18);
$pdf->Cell(120);
$pdf->SetDrawColor(0, 0, 0);
$pdf->SetLineWidth(0.5);
$pdf->Line(120, $pdf->GetY() - 2, 200, $pdf->GetY() - 2);

// Nama Dosen (Cetak Tebal)
$pdf->Ln(3);
$pdf->Cell(120);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 5, htmlspecialchars($dosen_pa_name), 0, 1, 'L');

// NIDN Dosen
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(120);
$pdf->Cell(0, 5, 'NIDN: ' . htmlspecialchars($dosen_pa_nidn), 0, 1, 'L');
?>
