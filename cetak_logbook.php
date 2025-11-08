<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Validasi akses
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen' || !isset($_GET['nim'])) {
    header("Location: dashboard_dosen.php");
    exit();
}

$nim_mahasiswa = $_GET['nim'];
$id_dosen_login = $_SESSION['user_id'];

require_once 'config.php';

// Ambil data mahasiswa
$stmt_mhs = $conn->prepare("SELECT m.*, p.nama_prodi, d.nama_dosen, d.id_dosen
                            FROM mahasiswa m 
                            JOIN program_studi p ON m.id_prodi = p.id_prodi 
                            JOIN dosen d ON m.id_dosen_pa = d.id_dosen 
                            WHERE m.nim = ? AND m.id_dosen_pa = ?");
$stmt_mhs->bind_param("si", $nim_mahasiswa, $id_dosen_login);
$stmt_mhs->execute();
$result_mhs = $stmt_mhs->get_result();

if ($result_mhs->num_rows === 0) { 
    echo "Data tidak ditemukan atau akses ditolak.";
    exit(); 
}

$mahasiswa = $result_mhs->fetch_assoc();

// Ambil semua logbook
$result_log = $conn->query("SELECT * FROM logbook 
                            WHERE nim_mahasiswa = '{$nim_mahasiswa}' 
                            ORDER BY tanggal_bimbingan DESC, created_at DESC");

$total_bimbingan = $result_log->num_rows;

// Tanggal cetak
$tanggal_cetak = date('d F Y, H:i');
$bulan_indonesia = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
    'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
    'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
    'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
];
$tanggal_cetak = str_replace(array_keys($bulan_indonesia), array_values($bulan_indonesia), $tanggal_cetak);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logbook Bimbingan - <?= htmlspecialchars($mahasiswa['nama_mahasiswa']); ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 15mm; }
            @page { size: A4; margin: 15mm; }
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
            background: #fff;
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Kop Surat Gaya Kementerian */
        .kop-surat {
            display: flex;
            align-items: center;
            border-bottom: 4px double #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .logo-container {
            flex-shrink: 0;
            margin-right: 20px;
        }
        
        .logo-kop {
            height: 110px;
            width: auto;
            object-fit: contain;
        }
        
        .kop-text {
            flex: 1;
            text-align: center;
        }
        
        .kop-text h1 {
            font-size: 11pt;
            font-weight: bold;
            margin: 0 0 2px 0;
            letter-spacing: 0.5px;
        }
        
        .kop-text h2 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0 0 2px 0;
            letter-spacing: 1px;
        }
        
        .kop-text h3 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0 0 8px 0;
        }
        
        .kop-text p {
            font-size: 9pt;
            margin: 1px 0;
            line-height: 1.4;
        }
        
        .judul-dokumen {
            text-align: center;
            margin: 30px 0 20px;
        }
        
        .judul-dokumen h2 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        
        .info-mahasiswa {
            margin: 20px 0;
            border: 1px solid #000;
            padding: 15px;
            background: #f9f9f9;
        }
        
        .info-mahasiswa table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-mahasiswa td {
            padding: 5px;
            font-size: 11pt;
        }
        
        .info-mahasiswa td:first-child {
            width: 30%;
            font-weight: bold;
        }
        
        .logbook-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10pt;
        }
        
        .logbook-table th,
        .logbook-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        .logbook-table th {
            background: #049D6F;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .logbook-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }
        
        .badge-dosen {
            background: #2196F3;
            color: white;
        }
        
        .badge-mahasiswa {
            background: #049D6F;
            color: white;
        }
        
        .badge-peringatan {
            background: #FFC107;
            color: #000;
        }
        
        .ttd-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .ttd-box {
            float: right;
            width: 45%;
            text-align: center;
        }
        
        .ttd-box p {
            margin: 5px 0;
        }
        
        .ttd-space {
            height: 60px;
        }
        
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #049D6F;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14pt;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 1000;
            font-family: Arial, sans-serif;
        }
        
        .btn-print:hover {
            background: #037a59;
        }
        
        .btn-back {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 12px 24px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14pt;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 1000;
            text-decoration: none;
            font-family: Arial, sans-serif;
            display: inline-block;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
        
        .footer-info {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-size: 9pt;
            color: #666;
            text-align: center;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>

    <!-- Tombol Kembali & Print -->
    <a href="detail_mahasiswa.php?nim=<?= urlencode($nim_mahasiswa); ?>" class="btn-back no-print">
        ← Kembali
    </a>
    
    <button onclick="window.print()" class="btn-print no-print">
        🖨️ Cetak
    </button>

    <!-- Kop Surat (Gaya Kementerian) -->
    <div class="kop-surat">
        <div class="logo-container">
            <img src="assets/logo_uin.png" alt="Logo UIN Palopo" class="logo-kop">
        </div>
        <div class="kop-text">
            <h1>KEMENTERIAN AGAMA REPUBLIK INDONESIA</h1>
            <h2>UNIVERSITAS ISLAM NEGERI PALOPO</h2>
            <h3>FAKULTAS SYARIAH DAN HUKUM</h3>
            <p>Jalan Agatis II, Balandai, Kecamatan Bara, Kota Palopo, Sulawesi Selatan 91914</p>
            <p>Telp: +62821-xxxxxxxx | Email: kontak@uinpalopo.ac.id | Website: www.uinpalopo.ac.id</p>
        </div>
    </div>

    <!-- Judul Dokumen -->
    <div class="judul-dokumen">
        <h2>LOGBOOK BIMBINGAN AKADEMIK</h2>
        <p style="font-size: 11pt;">Tahun Akademik <?= date('Y'); ?>/<?= date('Y') + 1; ?></p>
    </div>

    <!-- Info Mahasiswa -->
    <div class="info-mahasiswa">
        <table>
            <tr>
                <td>Nama Mahasiswa</td>
                <td>: <strong><?= htmlspecialchars($mahasiswa['nama_mahasiswa']); ?></strong></td>
            </tr>
            <tr>
                <td>NIM</td>
                <td>: <?= htmlspecialchars($mahasiswa['nim']); ?></td>
            </tr>
            <tr>
                <td>Program Studi</td>
                <td>: <?= htmlspecialchars($mahasiswa['nama_prodi']); ?></td>
            </tr>
            <tr>
                <td>Angkatan</td>
                <td>: <?= htmlspecialchars($mahasiswa['angkatan']); ?></td>
            </tr>
            <tr>
                <td>IPK</td>
                <td>: <?= number_format($mahasiswa['ipk'], 2); ?></td>
            </tr>
            <tr>
                <td>Total SKS</td>
                <td>: <?= htmlspecialchars($mahasiswa['total_sks'] ?? '0'); ?> SKS</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>: <strong><?= htmlspecialchars($mahasiswa['status']); ?></strong></td>
            </tr>
            <tr>
                <td>Dosen PA</td>
                <td>: <?= htmlspecialchars($mahasiswa['nama_dosen']); ?></td>
            </tr>
        </table>
    </div>

    <!-- Tabel Logbook -->
    <?php if ($total_bimbingan > 0): ?>
    <table class="logbook-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 10%;">Pengisi</th>
                <th style="width: 18%;">Topik</th>
                <th style="width: 35%;">Isi Bimbingan</th>
                <th style="width: 20%;">Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            mysqli_data_seek($result_log, 0);
            while($log = $result_log->fetch_assoc()): 
                $is_dosen = ($log['pengisi'] == 'Dosen');
                $is_peringatan = ($log['topik_bimbingan'] == 'Peringatan Akademik Terkait Nilai');
                $is_catatan_dosen = ($log['topik_bimbingan'] == 'Catatan Dosen');
                
                $badge_class = $is_dosen ? 'badge-dosen' : 'badge-mahasiswa';
                if ($is_peringatan) {
                    $badge_class = 'badge-peringatan';
                }
            ?>
            <tr>
                <td style="text-align: center;"><?= $no++; ?></td>
                <td style="text-align: center; white-space: nowrap;">
                    <?= date('d/m/Y', strtotime($log['tanggal_bimbingan'])); ?>
                </td>
                <td style="text-align: center;">
                    <span class="badge <?= $badge_class; ?>">
                        <?= htmlspecialchars($log['pengisi']); ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($log['topik_bimbingan']); ?></td>
                <td><?= nl2br(htmlspecialchars($log['isi_bimbingan'])); ?></td>
                <td>
                    <?php if (!empty($log['tindak_lanjut'])): ?>
                        <?= nl2br(htmlspecialchars($log['tindak_lanjut'])); ?>
                    <?php else: ?>
                        <span style="color: #999;">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="no-data">
        <p>Belum ada data bimbingan untuk mahasiswa ini.</p>
    </div>
    <?php endif; ?>

    <!-- Tanda Tangan -->
    <div class="ttd-section">
        <div style="clear: both;">
            <div class="ttd-box">
                <p>Palopo, <?= $tanggal_cetak; ?> WIB</p>
                <p style="margin-top: 10px;">Dosen Pembimbing Akademik,</p>
                <div class="ttd-space"></div>
                <p><strong><?= htmlspecialchars($mahasiswa['nama_dosen']); ?></strong></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-info">
        <p>Dokumen ini dicetak dari Sistem Informasi Bimbingan Akademik UIN Palopo</p>
        <p>Dicetak oleh: <?= htmlspecialchars($_SESSION['nama'] ?? 'Dosen'); ?> pada <?= $tanggal_cetak; ?> WIB</p>
    </div>

</body>
</html>

<?php
// Tutup koneksi
if (isset($stmt_mhs)) $stmt_mhs->close();
$conn->close();
?>