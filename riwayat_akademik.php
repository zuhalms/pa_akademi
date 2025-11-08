<?php
// Tentukan judul halaman sebelum memanggil header
$page_title = 'Riwayat Akademik';
require 'templates/header.php';

// Keamanan: Pastikan yang mengakses adalah mahasiswa
if ($_SESSION['user_role'] != 'mahasiswa') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi'; // Untuk XAMPP
    //$host = 'sql306.infinityfree.com';
    //$db_user = 'if0_40207082';
    //$db_pass = 'apabagus125';
    //$db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

$nim_mahasiswa = $_SESSION['user_id'];
$pesan_sukses = '';
$pesan_error = '';

// Proses form jika ada yang disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_riwayat'])) {
    $semester = $_POST['semester'];
    $sks_diambil = $_POST['sks_diambil'];
    $ip_semester = str_replace(',', '.', $_POST['ip_semester']); // Ganti koma dengan titik

    // Cek dulu apakah data semester ini sudah ada
    $check_stmt = $conn->prepare("SELECT id_riwayat FROM riwayat_akademik WHERE nim_mahasiswa = ? AND semester = ?");
    $check_stmt->bind_param("si", $nim_mahasiswa, $semester);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $pesan_error = "Data untuk semester {$semester} sudah ada. Anda tidak dapat menambahkannya lagi.";
    } else {
        // Jika belum ada, masukkan data baru
        $insert_stmt = $conn->prepare("INSERT INTO riwayat_akademik (nim_mahasiswa, semester, sks_diambil, ip_semester) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("siid", $nim_mahasiswa, $semester, $sks_diambil, $ip_semester);
        
        if ($insert_stmt->execute()) {
            $pesan_sukses = "Riwayat akademik untuk semester {$semester} berhasil disimpan!";

            // === LOGIKA PERHITUNGAN DAN UPDATE IPK & SKS OTOMATIS ===
            $riwayat_result = $conn->query("SELECT ip_semester, sks_diambil FROM riwayat_akademik WHERE nim_mahasiswa = '{$nim_mahasiswa}'");
            
            $total_sks = 0;
            $total_bobot_kali_sks = 0;

            while ($row = $riwayat_result->fetch_assoc()) {
                $total_sks += $row['sks_diambil'];
                $total_bobot_kali_sks += ($row['ip_semester'] * $row['sks_diambil']);
            }

            $ipk_baru = ($total_sks > 0) ? ($total_bobot_kali_sks / $total_sks) : 0;
            
            $update_stmt = $conn->prepare("UPDATE mahasiswa SET ipk = ?, total_sks = ? WHERE nim = ?");
            $ipk_formatted = number_format($ipk_baru, 2, '.', '');
            $update_stmt->bind_param("dis", $ipk_formatted, $total_sks, $nim_mahasiswa);
            $update_stmt->execute();
            $update_stmt->close();
        } else {
            $pesan_error = "Terjadi kesalahan saat menyimpan data.";
        }
        $insert_stmt->close();
    }
    $check_stmt->close();
}

// Ambil data riwayat yang sudah ada untuk ditampilkan
$result_riwayat = $conn->query("SELECT * FROM riwayat_akademik WHERE nim_mahasiswa = '{$nim_mahasiswa}' ORDER BY semester ASC");
$conn->close();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Lengkapi Riwayat Akademik</h4>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted">Masukkan data Indeks Prestasi (IP) per semester untuk melacak kemajuan studi dan menghitung IPK serta Total SKS Anda secara otomatis.</p>
                    
                    <?php if($pesan_sukses): ?><div class="alert alert-success"><?= $pesan_sukses; ?></div><?php endif; ?>
                    <?php if($pesan_error): ?><div class="alert alert-danger"><?= $pesan_error; ?></div><?php endif; ?>

                    <form method="POST" action="riwayat_akademik.php" class="border p-3 rounded mb-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="semester" class="form-label">Semester</label>
                                <input type="number" class="form-control" id="semester" name="semester" min="1" max="8" required>
                            </div>
                            <div class="col-md-4">
                                <label for="sks_diambil" class="form-label">SKS Diambil</label>
                                <input type="number" class="form-control" id="sks_diambil" name="sks_diambil" min="1" max="24" required>
                            </div>
                            <div class="col-md-5">
                                <label for="ip_semester" class="form-label">IP Semester</label>
                                <input type="text" class="form-control" id="ip_semester" name="ip_semester" placeholder="Contoh: 3.75" required pattern="[0-9]+([.,][0-9]+)?">
                            </div>
                        </div>
                        <div class="d-grid mt-3">
                            <button type="submit" name="submit_riwayat" class="btn btn-primary">Simpan Data Semester</button>
                        </div>
                    </form>

                    <h5>Riwayat Tercatat</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">Semester</th>
                                    <th scope="col">SKS Diambil</th>
                                    <th scope="col">IP Semester</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_riwayat->num_rows > 0): ?>
                                    <?php while($row = $result_riwayat->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['semester']); ?></td>
                                            <td><?= htmlspecialchars($row['sks_diambil']); ?></td>
                                            <td><?= number_format($row['ip_semester'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada data riwayat akademik.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require 'templates/footer.php';
?>