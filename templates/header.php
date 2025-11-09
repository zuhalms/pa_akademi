<?php
// ===================================================================
// == BAGIAN LOGIKA PHP ANDA
// ===================================================================

// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ================= INCLUDE KONFIGURASI DATABASE =================
// Gunakan config.php yang sudah ada (auto-detect environment)
require_once __DIR__ . '/../config.php';

// ================= AMBIL NAMA USER DARI DATABASE =================
$user_name = '';

if (isset($_SESSION['user_role'])) {
    // Cek koneksi database
    if (!$conn->connect_error) {
        if ($_SESSION['user_role'] == 'dosen') {
            $stmt = $conn->prepare("SELECT nama_dosen FROM dosen WHERE id_dosen = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $user_name = $result['nama_dosen'] ?? 'Dosen';
            $stmt->close();
        } elseif ($_SESSION['user_role'] == 'mahasiswa') {
            $stmt = $conn->prepare("SELECT nama_mahasiswa FROM mahasiswa WHERE nim = ?");
            $stmt->bind_param("s", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $user_name = $result['nama_mahasiswa'] ?? 'Mahasiswa';
            $stmt->close();
        }
        // JANGAN TUTUP $conn di sini karena masih dipakai di halaman utama
    } else {
        // Jika koneksi gagal, tampilkan error
        die("Koneksi ke database gagal di header.php: " . $conn->connect_error);
    }
}

// Tentukan link dashboard berdasarkan role
$dashboard_link = ($_SESSION['user_role'] == 'dosen') ? 'dashboard_dosen.php' : 'dashboard_mahasiswa.php';


// ===================================================================
// == BAGIAN TAMPILAN HTML & CSS (DESAIN BARU) ==
// ===================================================================
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>SMART-BA</title>
    
    <link rel="icon" href="assets/logo_uin.png" type="image/png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <style>
        :root {
            /* Warna hijau baru yang lebih cerah */
            --campus-green: #049D6F;
            --ui-font: 'Poppins', sans-serif;
        }
        body {
            font-family: var(--ui-font);
            background-color: #f8f9fa;
        }
        /* Menggunakan font Poppins untuk semua elemen */
        h1, h2, h3, h4, h5, h6, .navbar-brand, .nav-link, .dropdown-item, .btn {
            font-family: var(--ui-font);
        }
        .navbar {
            background-color: var(--campus-green);
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        .navbar-brand-custom {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .navbar-brand-custom .brand-text-group {
            line-height: 1.2;
        }
        .navbar-brand-custom .brand-main-text {
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
        }
        .navbar-brand-custom .brand-sub-text {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.8);
        }
        .navbar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .navbar .nav-link:hover, .navbar .nav-link.active {
            color: #fff;
        }
        .user-pill {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 50rem;
            padding: 0.5rem 1rem;
            transition: background-color 0.2s ease;
        }
        .user-pill:hover {
            background-color: rgba(255, 255, 255, 0.25);
        }
        .user-pill .bi-person-circle {
            font-size: 1.5rem;
        }
        .dropdown-menu {
            border-radius: 0.75rem;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1.2rem;
        }
        .dropdown-item i {
            width: 1.25em;
            opacity: 0.7;
        }
    </style>
</head>
<body class="bg-light">


<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container-fluid px-4">
        
        <a class="navbar-brand-custom text-decoration-none" href="<?= $dashboard_link; ?>">
            <img src="assets/logo_uin.png" alt="Logo" height="40">
            <div class="brand-text-group">
                <div class="brand-main-text">SMART-BA Fakultas Syariah</div>
                <div class="brand-sub-text">Universitas Islam Negeri Kota Palopo</div>
            </div>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 d-flex align-items-center">
                <li class="nav-item"><a class="nav-link" href="<?= $dashboard_link; ?>"><i class="bi bi-grid-1x2-fill"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="profil.php"><i class="bi bi-person-fill"></i>Profil</a></li>
                
                <?php if ($_SESSION['user_role'] == 'mahasiswa'): ?>
                <li class="nav-item"><a class="nav-link" href="input_riwayat.php"><i class="bi bi-clock-history"></i>Riwayat</a></li>
                <?php endif; ?>


                <li class="nav-item dropdown ms-lg-3">
                    <a class="nav-link dropdown-toggle d-flex align-items-center user-pill" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-2"></i>
                        <span><?= htmlspecialchars($user_name); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end mt-2" aria-labelledby="navbarDropdown">
                        <li><h6 class="dropdown-header">Akun Saya</h6></li>
                        <li><a class="dropdown-item" href="profil.php"><i class="bi bi-gear-fill"></i>Pengaturan Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>


<main>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cek notifikasi hanya jika elemennya ada (mungkin tidak ada di semua halaman)
        const notifBadge = document.getElementById('notifCount');
        if (notifBadge) {
            function checkNotifications() {
                fetch('check_notif.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.count > 0) {
                            notifBadge.innerText = data.count;
                            notifBadge.style.display = 'block';
                        } else {
                            notifBadge.style.display = 'none';
                        }
                    })
                    .catch(error => console.error('Error fetching notifications:', error));
            }
            checkNotifications();
            setInterval(checkNotifications, 7000);
        }
    });
</script>