<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

$error_message = '';

// Get list dosen for dropdown
$dosen_query = "SELECT id_dosen, nama_dosen, nidn_dosen FROM dosen ORDER BY nama_dosen ASC";
$dosen_result = $conn->query($dosen_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = trim($_POST['nim']);
    $nama = trim($_POST['nama_mahasiswa']);
    $email = trim($_POST['email_mahasiswa']);
    $telepon = trim($_POST['telepon_mahasiswa']);
    $prodi = trim($_POST['prodi']);
    $angkatan = trim($_POST['angkatan']);
    $id_dosen = $_POST['id_dosen'] != '' ? $_POST['id_dosen'] : NULL;
    $alamat = trim($_POST['alamat']);

    // Validasi
    if (empty($nim) || empty($nama) || empty($email) || empty($prodi) || empty($angkatan)) {
        $error_message = "Semua field wajib diisi kecuali telepon, dosen PA, dan alamat!";
    } else {
        // Cek NIM sudah ada atau belum
        $check_query = "SELECT nim FROM mahasiswa WHERE nim = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $nim);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_message = "NIM sudah terdaftar!";
        } else {
            // Hash password (default = NIM)
            $password = password_hash($nim, PASSWORD_DEFAULT);

            // Insert data
            $insert_query = "INSERT INTO mahasiswa (nim, nama_mahasiswa, email_mahasiswa, telepon_mahasiswa, prodi, angkatan, id_dosen, alamat, password) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("ssssssiss", $nim, $nama, $email, $telepon, $prodi, $angkatan, $id_dosen, $alamat, $password);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Mahasiswa berhasil ditambahkan! Password default: NIM";
                header("Location: mahasiswa.php");
                exit();
            } else {
                $error_message = "Gagal menambahkan mahasiswa: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mahasiswa - Admin SMART-BA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 bg-dark text-white min-vh-100 p-3">
                <h4 class="mb-4">SMART-BA Admin</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="dashboard_admin.php">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white active bg-primary" href="mahasiswa.php">
                            <i class="bi bi-people me-2"></i>Mahasiswa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="dosen.php">
                            <i class="bi bi-person-workspace me-2"></i>Dosen
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="../logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Tambah Mahasiswa</h2>
                    <a href="mahasiswa.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nim" class="form-label">NIM <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nim" name="nim" required 
                                           value="<?= isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : ''; ?>">
                                    <small class="text-muted">Tanpa spasi</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nama_mahasiswa" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_mahasiswa" name="nama_mahasiswa" required
                                           value="<?= isset($_POST['nama_mahasiswa']) ? htmlspecialchars($_POST['nama_mahasiswa']) : ''; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email_mahasiswa" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email_mahasiswa" name="email_mahasiswa" required
                                           value="<?= isset($_POST['email_mahasiswa']) ? htmlspecialchars($_POST['email_mahasiswa']) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="telepon_mahasiswa" class="form-label">Telepon</label>
                                    <input type="text" class="form-control" id="telepon_mahasiswa" name="telepon_mahasiswa"
                                           value="<?= isset($_POST['telepon_mahasiswa']) ? htmlspecialchars($_POST['telepon_mahasiswa']) : ''; ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="prodi" class="form-label">Program Studi <span class="text-danger">*</span></label>
                                    <select class="form-select" id="prodi" name="prodi" required>
                                        <option value="">-- Pilih Prodi --</option>
                                        <option value="Hukum Keluarga Islam">Hukum Keluarga Islam</option>
                                        <option value="Hukum Ekonomi Syariah">Hukum Ekonomi Syariah</option>
                                        <option value="Hukum Pidana Islam">Hukum Pidana Islam</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="angkatan" class="form-label">Angkatan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="angkatan" name="angkatan" required
                                           placeholder="Contoh: 2023"
                                           value="<?= isset($_POST['angkatan']) ? htmlspecialchars($_POST['angkatan']) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="id_dosen" class="form-label">Dosen Pembimbing Akademik (PA)</label>
                                <select class="form-select" id="id_dosen" name="id_dosen">
                                    <option value="">-- Pilih Dosen PA --</option>
                                    <?php while ($dosen = $dosen_result->fetch_assoc()): ?>
                                        <option value="<?= $dosen['id_dosen']; ?>">
                                            <?= htmlspecialchars($dosen['nama_dosen']); ?> (<?= htmlspecialchars($dosen['nidn_dosen']); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Password default mahasiswa adalah <strong>NIM</strong> yang telah di-hash.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Simpan Mahasiswa
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>