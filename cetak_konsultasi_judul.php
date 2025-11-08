<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Validasi akses dosen
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen' || !isset($_GET['nim'])) {
    header("Location: dashboard_dosen.php");
    exit();
}

$nim_mahasiswa = $_GET['nim'];
$id_dosen_login = $_SESSION['user_id'];

require_once 'config.php';

// Ambil data mahasiswa
$stmt_mhs = $conn->prepare("SELECT m.*, p.nama_prodi, d.nama_dosen, d.nip 
                            FROM mahasiswa m 
                            JOIN program_studi p ON m.id_prodi = p.id_prodi 
                            JOIN dosen d ON m.id_dosen_pa = d.id_dosen 
                            WHERE m.nim = ? AND m.id_dosen_pa = ?");
$stmt_mhs->bind_param("si", $nim_mahasiswa, $id_dosen_login);
$stmt_mhs->execute();
$result_mhs = $stmt_mhs->get_result();

if ($result_mhs->num_rows === 0) {
    echo '<div class="alert alert-danger">Data mahasiswa tidak ditemukan atau Anda tidak memiliki hak akses.</div>';
    exit();
}

$mahasiswa = $result_mhs->fetch_assoc();

// Ambil data konsultasi judul
$result_konsultasi = null;
$jumlah_konsultasi = 0;

if($conn->query("SHOW TABLES LIKE 'konsultasi_judul'")->num_rows > 0) {
    $stmt_konsul = $conn->prepare("SELECT * FROM konsultasi_judul WHERE nim = ? ORDER BY tanggal_pengajuan ASC");
    $stmt_konsul->bind_param("s", $nim_mahasiswa);
    $stmt_konsul->execute();
    $result_konsultasi = $stmt_konsul->get_result();
    $jumlah_konsultasi = $result_konsultasi->num_rows;
}

$tanggal_cetak = date('d F Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Riwayat Konsultasi Judul - <?= htmlspecialchars($mahasiswa['nama_mahasiswa']); ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 15mm; }
        }
        
        @page {
            size: A4;
            margin: 20mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
            background-color: #fff;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }
        
        .header-logo {
            flex-shrink: 0;
            margin-right: -90px;
        }
        
        .header-logo img {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-left: 30px;
        }
        
        .header-text {
            flex-grow: 1;
            text-align: center;
        }
        
        .header-text h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .header-text h2 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .header-text p {
            font-size: 11pt;
            margin: 2px 0;
        }
        
        .info-mahasiswa {
            margin: 25px 0;
            background-color: #f8f9fa;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        
        .info-mahasiswa table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-mahasiswa td {
            padding: 5px 10px;
            vertical-align: top;
        }
        
        .info-mahasiswa td:first-child {
            width: 180px;
            font-weight: bold;
        }
        
        .info-mahasiswa td:nth-child(2) {
            width: 20px;
            text-align: center;
        }
        
        .konsultasi-section {
            margin: 30px 0;
        }
        
        .konsultasi-item {
            margin-bottom: 30px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            page-break-inside: avoid;
            background-color: #fff;
        }
        
        .konsultasi-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .konsultasi-number {
            font-size: 14pt;
            font-weight: bold;
            color: #333;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 10pt;
            text-align: center;
        }
        
        .status-disetujui {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-revisi {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .status-ditolak {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-menunggu {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .konsultasi-content {
            margin: 15px 0;
        }
        
        .konsultasi-label {
            font-weight: bold;
            margin-top: 12px;
            margin-bottom: 5px;
            color: #495057;
        }
        
        .konsultasi-text {
            text-align: justify;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #049D6F;
            margin-bottom: 10px;
        }
        
        .konsultasi-judul {
            font-size: 13pt;
            font-weight: bold;
            color: #049D6F;
            margin: 10px 0;
        }
        
        .tanggal-info {
            font-size: 10pt;
            color: #6c757d;
            font-style: italic;
        }
        
        .tanggapan-dosen {
            margin-top: 15px;
            padding: 15px;
            background-color: #e7f5f1;
            border-left: 4px solid #049D6F;
            border-radius: 5px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        
        .signature-box {
            float: right;
            width: 300px;
            text-align: center;
        }
        
        .signature-box p {
            margin: 5px 0;
        }
        
        .signature-line {
            margin-top: 70px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .button-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 11pt;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        
        .btn-print {
            background-color: #049D6F;
            color: white;
        }
        
        .btn-print:hover {
            background-color: #037a59;
        }
        
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-back:hover {
            background-color: #545b62;
        }
        
        .summary-box {
            background-color: #e7f5f1;
            border: 2px solid #049D6F;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        
        .summary-box strong {
            font-size: 13pt;
            color: #049D6F;
        }
    </style>
</head>
<body>
    <!-- Tombol Cetak & Kembali -->
    <div class="button-container no-print">
        <button onclick="window.print()" class="btn btn-print">🖨️ Cetak Dokumen</button>
        <a href="detail_mahasiswa.php?nim=<?= urlencode($nim_mahasiswa); ?>" class="btn btn-back">← Kembali</a>
    </div>

    <!-- Header Dokumen dengan Logo -->
    <div class="header">
        <div class="header-logo">
            <!-- Ganti path logo sesuai dengan lokasi logo Anda -->
            <img src="assets/logo_uin2.png" alt="Logo Universitas">
        </div>
        <div class="header-text">
            <h1>Universitas Islam Negeri Kota Palopo</h1>
            <h2>Riwayat Konsultasi Judul Penelitian</h2>
            <p>Fakultas Syariah - Program Studi <?= htmlspecialchars($mahasiswa['nama_prodi']); ?></p>
        </div>
    </div>

    <!-- Informasi Mahasiswa -->
    <div class="info-mahasiswa">
        <table>
            <tr>
                <td>Nama Mahasiswa</td>
                <td>:</td>
                <td><?= htmlspecialchars($mahasiswa['nama_mahasiswa']); ?></td>
            </tr>
            <tr>
                <td>NIM</td>
                <td>:</td>
                <td><?= htmlspecialchars($mahasiswa['nim']); ?></td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>:</td>
                <td><?= htmlspecialchars($mahasiswa['nama_prodi']); ?></td>
            </tr>

            <tr>
                <td>Dosen Pembimbing Akademik</td>
                <td>:</td>
                <td><?= htmlspecialchars($mahasiswa['nama_dosen']); ?></td>
            </tr>
            <tr>
                <td>Tanggal Cetak</td>
                <td>:</td>
                <td><?= $tanggal_cetak; ?></td>
            </tr>
        </table>
    </div>

    <!-- Summary -->
    <div class="summary-box">
        <strong>Total Konsultasi: <?= $jumlah_konsultasi; ?> kali</strong>
    </div>

    <!-- Riwayat Konsultasi -->
    <div class="konsultasi-section">
        <?php if ($jumlah_konsultasi > 0): ?>
            <?php 
            $nomor = 1;
            while($konsul = $result_konsultasi->fetch_assoc()): 
                // Tentukan kelas status
                $status_class = 'status-menunggu';
                $status_icon = '⏳';
                if ($konsul['status'] == 'Disetujui') {
                    $status_class = 'status-disetujui';
                    $status_icon = '✅';
                } elseif ($konsul['status'] == 'Ditolak') {
                    $status_class = 'status-ditolak';
                    $status_icon = '❌';
                } elseif ($konsul['status'] == 'Revisi') {
                    $status_class = 'status-revisi';
                    $status_icon = '🔄';
                }
            ?>
            
            <div class="konsultasi-item">
                <div class="konsultasi-header">
                    <div class="konsultasi-number">Konsultasi ke-<?= $nomor; ?></div>
                    <div class="status-badge <?= $status_class; ?>">
                        <?= $status_icon; ?> <?= htmlspecialchars($konsul['status']); ?>
                    </div>
                </div>
                
                <div class="konsultasi-content">
                    <p class="tanggal-info">
                        📅 Tanggal Pengajuan: <?= date('d F Y, H:i', strtotime($konsul['tanggal_pengajuan'])); ?> WIB
                    </p>
                    
                    <p class="konsultasi-label">Judul Usulan:</p>
                    <div class="konsultasi-judul">
                        "<?= htmlspecialchars($konsul['judul_usulan']); ?>"
                    </div>
                    
                    <?php if (!empty($konsul['deskripsi'])): ?>
                    <p class="konsultasi-label">Deskripsi / Latar Belakang:</p>
                    <div class="konsultasi-text">
                        <?= nl2br(htmlspecialchars($konsul['deskripsi'])); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($konsul['catatan_dosen'])): ?>
                    <div class="tanggapan-dosen">
                        <p class="konsultasi-label">💬 Tanggapan Dosen Pembimbing:</p>
                        <div style="margin-top: 10px;">
                            <?= nl2br(htmlspecialchars($konsul['catatan_dosen'])); ?>
                        </div>
                        <?php if (!empty($konsul['tanggal_respon'])): ?>
                        <p class="tanggal-info" style="margin-top: 10px;">
                            🕐 Ditanggapi pada: <?= date('d F Y, H:i', strtotime($konsul['tanggal_respon'])); ?> WIB
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php elseif ($konsul['status'] == 'Menunggu'): ?>
                    <div style="margin-top: 15px; padding: 10px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;">
                        <p style="color: #856404; font-style: italic;">⏳ Menunggu tanggapan dari dosen pembimbing...</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php 
            $nomor++;
            endwhile; 
            ?>
            
        <?php else: ?>
            <div class="no-data">
                <p style="font-size: 14pt;">📋 Belum ada riwayat konsultasi judul</p>
                <p style="margin-top: 10px;">Mahasiswa belum mengajukan konsultasi judul penelitian</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tanda Tangan -->
    <?php if ($jumlah_konsultasi > 0): ?>
    <div class="signature-section clearfix">
        <div class="signature-box">
            <p><?= $tanggal_cetak; ?></p>
            <p style="font-weight: bold; margin-top: 5px;">Dosen Pembimbing Akademik</p>
            <div class="signature-line">
                <p style="font-weight: bold;"><?= htmlspecialchars($mahasiswa['nama_dosen']); ?></p>
                <?php if (!empty($mahasiswa['nip'])): ?>
                <p>NIP. <?= htmlspecialchars($mahasiswa['nip']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>

<?php
// Tutup koneksi
if (isset($stmt_mhs)) $stmt_mhs->close();
if (isset($stmt_konsul)) $stmt_konsul->close();
$conn->close();
?>