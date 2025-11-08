<?php
// config.php - Konfigurasi Database Dinamis
// File ini otomatis mendeteksi environment (development/production)

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ===================================================================
// == DETEKSI ENVIRONMENT (DEVELOPMENT vs PRODUCTION) ==
// ===================================================================

// Cek apakah sedang di localhost (XAMPP)
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1' || strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0) {
    // DEVELOPMENT - XAMPP Lokal
    $host = "localhost";
    $dbuser = "root";
    $dbpass = "";
    $dbname = "db_pa_akademi";
    $environment = "DEVELOPMENT";
} else {
    // PRODUCTION - InfinityFree Hosting
    $host = "sql306.infinityfree.com";
    $dbuser = "if0_40207082";
    $dbpass = "apabagus125";
    $dbname = "if0_40207082_db_pa";
    $environment = "PRODUCTION";
}

// ===================================================================
// == BUAT KONEKSI DATABASE ==
// ===================================================================

$conn = new mysqli($host, $dbuser, $dbpass, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal (" . $environment . "): " . $conn->connect_error);
}

// Set charset UTF-8
$conn->set_charset("utf8mb4");

// Untuk debugging (bisa dihapus di production)
// echo "<!-- Environment: " . $environment . " -->";

?>
