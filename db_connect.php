<?php
$servername = "sql200.infinityfree.com";   // ganti sesuai host InfinityFree
$username = "if0_40177499";        // username kamu
$password = "Zuhal2208";    // password kamu
$dbname = "if0_40177499_db_pa_akademik";     // nama database kamu

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
// echo "Koneksi sukses!"; // opsional untuk tes
?>
