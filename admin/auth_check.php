<?php
// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login_admin.php");
    exit();
}