<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen') {
    header("Location: login.php");
    exit();
}

$page_title = 'Edit Riwayat Mahasiswa';
require 'templates/header.php';
$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
  //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

$id_dosen = $_SESSION['user_id'];
$nim = $_GET['nim'] ?? '';
$message = '';

// Ambil daftar mahasiswa bimbingan untuk dropdown
$stmt = $conn->prepare("SELECT nim, nama_mahasiswa FROM mahasiswa WHERE id_dosen_pa = ? ORDER BY nama_mahasiswa ASC");
$stmt->bind_param('i', $id_dosen);
$stmt->execute();
$list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Jika form submit untuk menyimpan riwayat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nim_selected'])) {
    $nim_post = $_POST['nim_selected'];
    $ips_values = $_POST['ips'];
    $sks_values = $_POST['sks'];
    $stmtup = $conn->prepare("INSERT INTO riwayat_akademik (nim_mahasiswa, semester, ip_semester, sks_semester) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE ip_semester = VALUES(ip_semester), sks_semester = VALUES(sks_semester)");
    for ($i=1;$i<=14;$i++) {
        if (isset($ips_values[$i]) && is_numeric($ips_values[$i]) && isset($sks_values[$i]) && is_numeric($sks_values[$i])) {
            $ip = (float)$ips_values[$i]; $sks = (int)$sks_values[$i];
            if ($ip > 0 && $sks >= 0) {
                $stmtup->bind_param('sidi', $nim_post, $i, $ip, $sks);
                $stmtup->execute();
            }
        }
    }
    $stmtup->close();
    $message = 'Riwayat mahasiswa berhasil diperbarui.';
    $nim = $nim_post;
}

// Jika ada NIM yang dipilih, ambil data riwayat
$riwayat = [];
if (!empty($nim)) {
    $stmt2 = $conn->prepare("SELECT semester, ip_semester, sks_semester FROM riwayat_akademik WHERE nim_mahasiswa = ?");
    $stmt2->bind_param('s', $nim);
    $stmt2->execute();
    $res = $stmt2->get_result();
    while ($r = $res->fetch_assoc()) { $riwayat[$r['semester']] = $r; }
    $stmt2->close();
}

$conn->close();
?>

<div class="container my-5">
    <h3>Edit Riwayat Mahasiswa</h3>
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message); ?></div><?php endif; ?>

    <form method="GET" action="edit_riwayat.php" class="mb-3 d-flex gap-2 align-items-center">
        <select name="nim" class="form-select" onchange="this.form.submit()">
            <option value="">Pilih Mahasiswa...</option>
            <?php foreach($list as $l): ?>
                <option value="<?= htmlspecialchars($l['nim']); ?>" <?= ($nim == $l['nim']) ? 'selected' : ''; ?>><?= htmlspecialchars($l['nama_mahasiswa']); ?> (<?= htmlspecialchars($l['nim']); ?>)</option>
            <?php endforeach; ?>
        </select>
        <a href="dashboard_dosen.php" class="btn btn-secondary">Kembali</a>
    </form>

    <?php if (!empty($nim)): ?>
    <form method="POST" action="edit_riwayat.php">
        <input type="hidden" name="nim_selected" value="<?= htmlspecialchars($nim); ?>">
        <div class="table-responsive">
            <table class="table">
                <thead class="table-dark"><tr><th>Semester</th><th>IP</th><th>SKS</th></tr></thead>
                <tbody>
                    <?php for ($i=1;$i<=14;$i++): ?>
                        <tr>
                            <td><strong>Semester <?= $i; ?></strong></td>
                            <td><input type="number" step="0.01" min="0" max="4.00" class="form-control" name="ips[<?= $i; ?>]" value="<?= htmlspecialchars($riwayat[$i]['ip_semester'] ?? ''); ?>"></td>
                            <td><input type="number" min="0" max="24" class="form-control" name="sks[<?= $i; ?>]" value="<?= htmlspecialchars($riwayat[$i]['sks_semester'] ?? ''); ?>"></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
        <div class="d-grid"><button type="submit" class="btn btn-primary">Simpan Riwayat</button></div>
    </form>
    <?php endif; ?>
</div>

<?php require 'templates/footer.php'; ?>
