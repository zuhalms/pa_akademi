<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include konfigurasi database (otomatis XAMPP atau InfinityFree)
require_once 'config.php';

$error_message = '';

// Jika sudah login, langsung arahkan ke dashboard yang sesuai
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] == 'dosen') {
        header("Location: dashboard_dosen.php");
        exit();
    } elseif ($_SESSION['user_role'] == 'mahasiswa') {
        header("Location: dashboard_mahasiswa.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // $conn sudah siap dari config.php (otomatis XAMPP atau InfinityFree)
    
    $role = $_POST['role'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Bersihkan spasi dari input pengguna
    $username_clean = str_replace(' ', '', $username);

    if ($role == 'dosen') {
        // Query untuk dosen dengan REPLACE untuk menghilangkan spasi
        $stmt = $conn->prepare("SELECT id_dosen, nama_dosen, password FROM dosen WHERE REPLACE(nidn_dosen, ' ', '') = ?");
        $stmt->bind_param("s", $username_clean);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $dosen = $result->fetch_assoc();
            if (password_verify($password, $dosen['password'])) {
                $_SESSION['user_id'] = $dosen['id_dosen'];
                $_SESSION['user_name'] = $dosen['nama_dosen'];
                $_SESSION['user_role'] = 'dosen';
                header("Location: dashboard_dosen.php");
                exit();
            }
        }
        $stmt->close();
        
    } elseif ($role == 'mahasiswa') {
        // Query untuk mahasiswa dengan REPLACE untuk menghilangkan spasi
        $stmt = $conn->prepare("SELECT nim, nama_mahasiswa, password FROM mahasiswa WHERE REPLACE(nim, ' ', '') = ?");
        $stmt->bind_param("s", $username_clean);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $mahasiswa = $result->fetch_assoc();
            if (password_verify($password, $mahasiswa['password'])) {
                // Simpan NIM asli (dengan spasi jika ada) ke session
                $_SESSION['user_id'] = $mahasiswa['nim'];
                $_SESSION['user_name'] = $mahasiswa['nama_mahasiswa'];
                $_SESSION['user_role'] = 'mahasiswa';
                header("Location: dashboard_mahasiswa.php");
                exit();
            }
        }
        $stmt->close();
    }

    // Jika semua gagal
    $error_message = "Kredensial yang Anda masukkan salah.";
}

// Tutup koneksi (opsional, karena PHP otomatis menutupnya)
if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMART-BA</title>

    <link rel="icon" href="assets/logo_uin.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #e6f6f1;
        }
        .main-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-wrapper {
            width: 100%;
            max-width: 900px;
            display: flex;
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-art {
            flex-basis: 45%;
            background: linear-gradient(135deg, #00A86B, #008F5A);
            color: white;
            padding: 4rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-art .icon { 
            font-size: 3.5rem; 
            margin-bottom: 1rem; 
        }
        .login-art h2 { 
            font-weight: 700; 
            margin-bottom: 0.5rem; 
        }
        .login-art p { 
            font-size: 0.9rem; 
            opacity: 0.9; 
        }
        .feature-list { 
            list-style: none; 
            padding: 0; 
            margin-top: 2rem; 
        }
        .feature-list li { 
            margin-bottom: 0.75rem; 
            display: flex; 
            align-items: center; 
        }
        .feature-list i { 
            margin-right: 0.75rem; 
        }
        
        .login-form-container {
            flex-basis: 55%;
            padding: 3rem;
        }
        .role-selector { 
            display: flex; 
            gap: 1rem; 
            margin-bottom: 1.5rem; 
        }
        .role-selector .role-btn {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            text-decoration: none;
            color: #333;
        }
        .role-selector .role-btn.active {
            background-color: #00A86B;
            color: white;
            border-color: #00A86B;
            font-weight: 600;
        }
        .role-selector .role-btn:hover:not(.active) { 
            background-color: #f8f9fa; 
        }

        .btn-brand {
            background-color: #00A86B;
            border-color: #00A86B;
            color: white;
            font-weight: 600;
            padding: 0.75rem;
        }
        .btn-brand:hover {
            background-color: #008F5A;
            border-color: #008F5A;
            color: white;
        }
        
        @media (max-width: 768px) {
            .login-art { 
                display: none; 
            }
            .login-form-container { 
                flex-basis: 100%; 
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="login-wrapper">
            <div class="login-art">
                <i class="bi bi-bank icon"></i>
                <h2>SMART-BA</h2>
                <p>Sistem Manajemen Akademik dan Bimbingan Terpadu</p>
                <p class="mt-2" style="font-size: 0.8rem;">Fakultas Syariah <br>Universitas Islam Negeri Kota Palopo</p>
                <ul class="feature-list">
                    <li><i class="bi bi-check-circle-fill"></i> Multi-Role Access</li>
                    <li><i class="bi bi-check-circle-fill"></i> Digital Logbook</li>
                    <li><i class="bi bi-check-circle-fill"></i> Real-time Analytics</li>
                    <li><i class="bi bi-check-circle-fill"></i> Secure & Reliable</li>
                </ul>
            </div>
            <div class="login-form-container">
                <div>
                    <h3 class="fw-bold">Selamat Datang!</h3>
                    <p class="text-muted mb-4">Silakan login untuk melanjutkan</p>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Login Sebagai:</label>
                            <div class="role-selector">
                                <a href="#" class="role-btn active" data-role="mahasiswa" data-label="NIM" data-placeholder="Masukkan NIM tanpa spasi">
                                    <i class="bi bi-person-fill me-2"></i>Mahasiswa
                                </a>
                                <a href="#" class="role-btn" data-role="dosen" data-label="ID Dosen" data-placeholder="Masukkan ID Dosen PA tanpa spasi">
                                    <i class="bi bi-person-workspace me-2"></i>Dosen PA
                                </a>
                            </div>
                            <input type="hidden" id="role" name="role" value="mahasiswa">
                        </div>

                        <div class="mb-3">
                            <label for="username" id="username-label" class="form-label">NIM</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan NIM tanpa spasi" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan Password Anda" required>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-brand">Masuk</button>
                        </div>
                        <div class="text-center">
                            <a href="index.php" class="text-decoration-none text-muted small">Kembali ke Beranda</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleButtons = document.querySelectorAll('.role-btn');
            const roleInput = document.getElementById('role');
            const usernameLabel = document.getElementById('username-label');
            const usernameInput = document.getElementById('username');

            roleButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    roleButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    const role = this.dataset.role;
                    roleInput.value = role;
                    usernameLabel.textContent = this.dataset.label;
                    usernameInput.placeholder = this.dataset.placeholder;
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
