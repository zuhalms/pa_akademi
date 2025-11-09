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

// Ambil judul skripsi yang disetujui
$judul_skripsi = '-';
$stmt_judul = $conn->prepare("
    SELECT judul_usulan 
    FROM konsultasi_judul 
    WHERE nim = ? AND status = 'Disetujui' 
    ORDER BY tanggal_pengajuan DESC 
    LIMIT 1
");

if ($stmt_judul) {
    $stmt_judul->bind_param("s", $nim_mahasiswa_login);
    $stmt_judul->execute();
    $result_judul = $stmt_judul->get_result();
    if ($result_judul->num_rows > 0) {
        $data_judul = $result_judul->fetch_assoc();
        $judul_skripsi = $data_judul['judul_usulan'];
    }
    $stmt_judul->close();
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

// ===================================================================
// Definisi Class PDF dengan Desain Modern
// ===================================================================
class PDF extends FPDF {
    private $nim = '';
    private $headerColor = [4, 157, 111]; // Warna hijau kampus (#049D6F)
    private $accentColor = [52, 152, 219]; // Biru cerah
    
    public function __construct($nim = '') {
        parent::__construct();
        $this->nim = $nim;
    }
    
    // Header dengan desain modern
    public function Header() {
        // Background header dengan gradient effect (simulasi dengan rectangle)
        $this->SetFillColor($this->headerColor[0], $this->headerColor[1], $this->headerColor[2]);
        $this->Rect(0, 0, 210, 45, 'F');
        
        // Logo (jika ada - sesuaikan path)
        // $this->Image('assets/logo_uin.png', 15, 10, 25);
        
        // Judul utama
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 18);
        $this->SetY(12);
        $this->Cell(0, 8, 'LAPORAN KEMAJUAN STUDI', 0, 1, 'C');
        
        // Sub judul
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, 'SMART-BA Fakultas Syariah', 0, 1, 'C');
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 5, 'Universitas Islam Negeri Kota Palopo', 0, 1, 'C');
        
        // Reset text color
        $this->SetTextColor(0, 0, 0);
        $this->Ln(10);
    }
    
    // Footer dengan desain modern
    public function Footer() {
        $this->SetY(-20);
        
        // Garis pembatas
        $this->SetDrawColor($this->headerColor[0], $this->headerColor[1], $this->headerColor[2]);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        
        $this->Ln(3);
        
        // Info footer
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(95, 4, 'Dicetak: ' . date('d F Y, H:i') . ' WIB', 0, 0, 'L');
        $this->Cell(95, 4, 'Halaman ' . $this->PageNo() . ' dari {nb}', 0, 0, 'R');
    }
    
    // Fungsi untuk membuat card/box dengan shadow effect
    public function DrawCard($x, $y, $width, $height, $title = '') {
        // Shadow effect
        $this->SetFillColor(220, 220, 220);
        $this->RoundedRect($x + 1, $y + 1, $width, $height, 3, 'F');
        
        // Card utama
        $this->SetFillColor(255, 255, 255);
        $this->SetDrawColor(200, 200, 200);
        $this->RoundedRect($x, $y, $width, $height, 3, 'DF');
        
        // Title bar jika ada
        if ($title) {
            $this->SetFillColor($this->headerColor[0], $this->headerColor[1], $this->headerColor[2]);
            $this->RoundedRect($x, $y, $width, 10, 3, 'F');
            
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Arial', 'B', 11);
            $this->SetXY($x, $y + 2);
            $this->Cell($width, 6, $title, 0, 0, 'C');
            $this->SetTextColor(0, 0, 0);
        }
    }
    
    // Fungsi untuk rounded rectangle
    public function RoundedRect($x, $y, $w, $h, $r, $style = 'D') {
        $k = $this->k;
        $hp = $this->h;
        
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
            
        $MyArc = 4/3 * (sqrt(2) - 1);
        
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }
    
    public function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', 
            $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, 
            $x3*$this->k, ($h-$y3)*$this->k));
    }
}

// ===================================================================
// Inisiasi PDF
// ===================================================================
$pdf = new PDF(htmlspecialchars($mahasiswa['nim']));
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 25);

// ===================================================================
// SECTION 1: Info Mahasiswa dalam Card (dengan Judul Skripsi)
// ===================================================================
$pdf->SetY(50);
$currentY = $pdf->GetY();

// Hitung tinggi card berdasarkan judul (jika panjang, tambah tinggi)
$cardHeight = 50; // Default height
if (strlen($judul_skripsi) > 80) {
    $cardHeight = 58;
}

// Draw card untuk info mahasiswa
$pdf->DrawCard(15, $currentY, 180, $cardHeight);

$pdf->SetY($currentY + 8);
$pdf->SetX(20);

// Icon dan data mahasiswa dengan layout yang lebih rapi
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(70, 70, 70);
$pdf->Cell(35, 6, 'Nama', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 6, ': ' . htmlspecialchars($mahasiswa['nama_mahasiswa']), 0, 1);

$pdf->SetX(20);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(70, 70, 70);
$pdf->Cell(35, 6, 'NIM', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 6, ': ' . htmlspecialchars($mahasiswa['nim']), 0, 1);

$pdf->SetX(20);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(70, 70, 70);
$pdf->Cell(35, 6, 'Dosen PA', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 6, ': ' . htmlspecialchars($mahasiswa['nama_dosen']), 0, 1);

// Tambahkan Judul Skripsi
$pdf->SetX(20);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(70, 70, 70);
$pdf->Cell(35, 6, 'Judul Skripsi', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);

// Jika judul panjang, gunakan MultiCell
if (strlen($judul_skripsi) > 80) {
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->SetX(60);
    $pdf->MultiCell(135, 6, htmlspecialchars($judul_skripsi), 0, 'L');
} else {
    $pdf->Cell(0, 6, ': ' . htmlspecialchars($judul_skripsi), 0, 1);
}

$pdf->Ln(5);

// ===================================================================
// SECTION 2: Progress Card dengan Desain Menarik
// ===================================================================
$currentY = $pdf->GetY();
$pdf->DrawCard(15, $currentY, 180, 45, 'PERSENTASE KEMAJUAN STUDI');

$pdf->SetY($currentY + 15);

// Progress bar dengan desain modern
$barX = 30;
$barY = $pdf->GetY();
$barWidth = 150;
$barHeight = 12;

// Background bar (abu-abu muda dengan border)
$pdf->SetFillColor(240, 240, 240);
$pdf->SetDrawColor(200, 200, 200);
$pdf->RoundedRect($barX, $barY, $barWidth, $barHeight, 2, 'DF');

// Progress bar (gradient effect - dari hijau tua ke hijau terang)
if ($persentase_kemajuan > 0) {
    $progressWidth = $barWidth * ($persentase_kemajuan / 100);
    
    // Warna berdasarkan persentase
    if ($persentase_kemajuan < 40) {
        $pdf->SetFillColor(231, 76, 60); // Merah untuk <40%
    } elseif ($persentase_kemajuan < 70) {
        $pdf->SetFillColor(241, 196, 15); // Kuning untuk 40-70%
    } else {
        $pdf->SetFillColor(46, 204, 113); // Hijau untuk >70%
    }
    
    $pdf->RoundedRect($barX, $barY, $progressWidth, $barHeight, 2, 'F');
}

// Teks persentase di tengah bar
$pdf->SetY($barY + 2);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetX($barX);
$pdf->Cell($barWidth, 8, $persentase_kemajuan . '% Selesai', 0, 0, 'C');

// Info detail di bawah bar
$pdf->Ln(12);
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, 'Progress: ' . $jumlah_selesai . ' dari ' . $total_pencapaian . ' milestone tercapai', 0, 1, 'C');

$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(8);

// ===================================================================
// SECTION 3: Tabel Pencapaian dengan Desain Modern
// ===================================================================
$currentY = $pdf->GetY();
$pdf->DrawCard(15, $currentY, 180, 12, 'DETAIL PENCAPAIAN (MILESTONES)');

$pdf->SetY($currentY + 17);

// Header tabel dengan warna gradient
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(4, 157, 111); // Hijau kampus
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(4, 157, 111);
$pdf->SetX(15);

// Lebar kolom yang presisi: Total = 180
$col_no = 10;        // Kolom No
$col_nama = 110;     // Kolom Nama Pencapaian (diperbesar)
$col_status = 30;    // Kolom Status
$col_tanggal = 30;   // Kolom Tanggal
// Total: 10 + 110 + 30 + 30 = 180

$pdf->Cell($col_no, 9, 'No', 1, 0, 'C', true);
$pdf->Cell($col_nama, 9, 'Nama Pencapaian', 1, 0, 'C', true);
$pdf->Cell($col_status, 9, 'Status', 1, 0, 'C', true);
$pdf->Cell($col_tanggal, 9, 'Tanggal', 1, 1, 'C', true);

// Data tabel dengan alternate row colors
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0, 0, 0);
$no = 1;

foreach ($daftar_pencapaian as $item) {
    $is_selesai = isset($status_pencapaian[$item]) && $status_pencapaian[$item]['status'] == 'Selesai';
    
    // Tentukan tanggal
    $tanggal = '-';
    if ($is_selesai && !empty($status_pencapaian[$item]['tanggal_selesai'])) {
        $tanggal = date('d M Y', strtotime($status_pencapaian[$item]['tanggal_selesai']));
    }
    
    // Alternate row color (zebra striping)
    if ($no % 2 == 0) {
        $pdf->SetFillColor(250, 250, 250);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }
    
    $pdf->SetX(15);
    $pdf->SetDrawColor(220, 220, 220);
    
    // Nomor
    $pdf->Cell($col_no, 10, $no, 1, 0, 'C', true);
    
    // Nama pencapaian dengan bullet point
    $bullet = $is_selesai ? '* ' : '  '; // Gunakan asterisk untuk bullet
    $pdf->Cell($col_nama, 10, $bullet . $item, 1, 0, 'L', true);
    
    // Status dengan badge warna
    if ($is_selesai) {
        $pdf->SetFillColor(212, 237, 218); // Hijau muda
        $pdf->SetTextColor(28, 94, 42); // Hijau tua
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($col_status, 10, 'SELESAI', 1, 0, 'C', true);
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);
    } else {
        $pdf->SetFillColor(255, 243, 205); // Kuning muda
        $pdf->SetTextColor(133, 100, 4); // Kuning tua
        $pdf->Cell($col_status, 10, 'Belum', 1, 0, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
    }
    
    // Tanggal
    if ($no % 2 == 0) {
        $pdf->SetFillColor(250, 250, 250);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }
    $pdf->Cell($col_tanggal, 10, $tanggal, 1, 1, 'C', true);
    
    $no++;
}

// ===================================================================
// SECTION 4: Summary Box
// ===================================================================
$pdf->Ln(5);
$currentY = $pdf->GetY();

// Box summary dengan border berwarna
$pdf->SetDrawColor(52, 152, 219);
$pdf->SetLineWidth(0.8);
$pdf->RoundedRect(15, $currentY, 180, 25, 3, 'D');

$pdf->SetY($currentY + 5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(52, 152, 219);
$pdf->Cell(0, 5, 'RINGKASAN', 0, 1, 'C');

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(70, 70, 70);
$pdf->Ln(2);

$summary_text = 'Mahasiswa telah menyelesaikan ' . $jumlah_selesai . ' dari ' . $total_pencapaian . ' milestone yang diperlukan ';
$summary_text .= 'untuk menyelesaikan studi dengan persentase kemajuan ' . $persentase_kemajuan . '%.';

$pdf->SetX(20);
$pdf->MultiCell(170, 5, $summary_text, 0, 'C');

// ===================================================================
// Tampilkan PDF
// ===================================================================
$filename = 'Laporan_Kemajuan_' . htmlspecialchars($mahasiswa['nim']) . '_' . date('Y-m-d') . '.pdf';

// Gunakan 'I' untuk tampil di browser, atau 'D' untuk download
$pdf->Output('I', $filename);

// Tutup koneksi
$conn->close();
?>