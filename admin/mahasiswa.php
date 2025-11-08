<?php
session_start();
require_once '../config.php';
require_once 'auth_check.php';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = "";
if ($search != '') {
    $where = "WHERE nim LIKE '%$search%' OR nama_mahasiswa LIKE '%$search%'";
}

// Get total records
$total_query = "SELECT COUNT(*) as total FROM mahasiswa $where";
$total_result = $conn->query($total_query);
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get mahasiswa data with dosen PA
$query = "SELECT m.*, d.nama_dosen 
          FROM mahasiswa m 
          LEFT JOIN dosen d ON m.id_dosen = d.id_dosen 
          $where 
          ORDER BY m.nim ASC 
          LIMIT $limit OFFSET $offset";
$result = $conn->query($query);

// Alert message from session
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa - Admin SMART-BA</title>
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
                    <h2>Data Mahasiswa</h2>
                    <a href="mahasiswa_tambah.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Mahasiswa
                    </a>
                </div>

                <!-- Alert Messages -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Search Form -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari berdasarkan NIM atau Nama..." 
                                       value="<?= htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search me-2"></i>Cari
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Prodi</th>
                                        <th>Angkatan</th>
                                        <th>Dosen PA</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php $no = $offset + 1; ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= htmlspecialchars($row['nim']); ?></td>
                                                <td><?= htmlspecialchars($row['nama_mahasiswa']); ?></td>
                                                <td><?= htmlspecialchars($row['email_mahasiswa']); ?></td>
                                                <td><?= htmlspecialchars($row['prodi']); ?></td>
                                                <td><?= htmlspecialchars($row['angkatan']); ?></td>
                                                <td><?= htmlspecialchars($row['nama_dosen'] ?? '-'); ?></td>
                                                <td>
                                                    <a href="mahasiswa_edit.php?nim=<?= urlencode($row['nim']); ?>" 
                                                       class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="mahasiswa_hapus.php?nim=<?= urlencode($row['nim']); ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Yakin ingin menghapus mahasiswa ini?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Tidak ada data mahasiswa</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>">
                                                <?= $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                        <p class="text-muted text-center">
                            Menampilkan <?= min($offset + 1, $total_rows); ?> - <?= min($offset + $limit, $total_rows); ?> 
                            dari <?= $total_rows; ?> data
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>