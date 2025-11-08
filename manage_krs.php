<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'dosen') {
    header("Location: login.php");
    exit();
}

$page_title = 'Kelola KRS';
require 'templates/header.php';

$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
   //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

$id_dosen = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve']) && is_array($_POST['students'])) {
    $approved = $_POST['students']; // array of nims
    $stmt = $conn->prepare("UPDATE mahasiswa SET krs_disetujui = TRUE, krs_notif_dilihat = FALSE WHERE nim = ? AND id_dosen_pa = ?");
    foreach ($approved as $nim) {
        $stmt->bind_param('si', $nim, $id_dosen);
        $stmt->execute();
    }
    $stmt->close();
    $message = 'KRS terpilih telah disetujui.';
}

// Jika ada GET param nim, setujui satu mahasiswa cepat
if (isset($_GET['nim']) && !empty($_GET['nim'])) {
    $nim_quick = $_GET['nim'];
    $stmt_quick = $conn->prepare("UPDATE mahasiswa SET krs_disetujui = TRUE, krs_notif_dilihat = FALSE WHERE nim = ? AND id_dosen_pa = ?");
    $stmt_quick->bind_param('si', $nim_quick, $id_dosen);
    $stmt_quick->execute();
    $stmt_quick->close();
    $message = 'KRS mahasiswa ' . htmlspecialchars($nim_quick) . ' telah disetujui.';
}

// Ambil mahasiswa yang KRS belum disetujui
$stmt2 = $conn->prepare("SELECT nim, nama_mahasiswa, angkatan FROM mahasiswa WHERE id_dosen_pa = ? AND krs_disetujui = FALSE ORDER BY angkatan DESC, nama_mahasiswa ASC");
$stmt2->bind_param('i', $id_dosen);
$stmt2->execute();
$result = $stmt2->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt2->close();
$conn->close();
?>

<div class="container my-5">
    <h3>Kelola KRS</h3>
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if (count($students) == 0): ?>
        <div class="alert alert-info">Tidak ada KRS yang menunggu persetujuan.</div>
    <?php else: ?>
        <form method="POST" action="manage_krs.php">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead><tr><th><input type="checkbox" id="checkAll"></th><th>NIM</th><th>Nama</th><th>Angkatan</th></tr></thead>
                    <tbody>
                        <?php foreach($students as $s): ?>
                        <tr>
                            <td><input type="checkbox" name="students[]" value="<?= htmlspecialchars($s['nim']); ?>"></td>
                            <td><?= htmlspecialchars($s['nim']); ?></td>
                            <td><a href="detail_mahasiswa.php?nim=<?= urlencode($s['nim']); ?>"><?= htmlspecialchars($s['nama_mahasiswa']); ?></a></td>
                            <td><?= htmlspecialchars($s['angkatan']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="approve" class="btn btn-primary">Setujui Terpilih</button>
                <a href="dashboard_dosen.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    document.getElementById('checkAll')?.addEventListener('change', function(e){
        document.querySelectorAll('input[name="students[]"]').forEach(cb => cb.checked = this.checked);
    });
</script>

<?php require 'templates/footer.php'; ?>
