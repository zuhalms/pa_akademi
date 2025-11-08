<?php
session_start();
require_once 'config.php';

$error_message = '';

// Jika sudah login sebagai admin, redirect ke dashboard
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'admin') {
    header("Location: admin/dashboard_admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Query untuk admin
    $stmt = $conn->prepare("SELECT id_admin, username_admin, nama_admin, password, role_admin FROM admin WHERE username_admin = ? AND status = 'active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['user_id'] = $admin['id_admin'];
            $_SESSION['user_name'] = $admin['nama_admin'];
            $_SESSION['user_role'] = 'admin';
            $_SESSION['admin_role'] = $admin['role_admin']; // super_admin atau admin
            header("Location: admin/dashboard_admin.php");
            exit();
        }
    }
    $stmt->close();
    
    $error_message = "Username atau password salah!";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SMART-BA</title>
    <link rel="icon" href="assets/logo_uin.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
            padding: 3rem;
        }
        .admin-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2.5rem;
            color: white;
        }
        .btn-admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem;
            font-weight: 600;
        }
        .btn-admin:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="admin-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h3 class="text-center fw-bold mb-2">Admin Panel</h3>
        <p class="text-center text-muted mb-4">SMART-BA Dashboard</p>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-admin">Masuk ke Dashboard</button>
            </div>
            <div class="text-center">
                <a href="login.php" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke Login Utama
                </a>
            </div>
        </form>
    </div>
</body>
</html>