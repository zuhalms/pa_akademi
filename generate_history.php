<?php
// Skrip untuk membuat data riwayat akademik contoh. Cukup jalankan sekali.

$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

echo "<h1>Membuat Data Riwayat Akademik...</h1>";

// Ambil beberapa NIM mahasiswa secara acak
$result = $conn->query("SELECT nim FROM mahasiswa ORDER BY RAND() LIMIT 20");
$mahasiswas = $result->fetch_all(MYSQLI_ASSOC);

$conn->query("TRUNCATE TABLE riwayat_akademik"); // Kosongkan tabel sebelum mengisi
echo "Tabel riwayat_akademik dikosongkan.<br>";

$stmt = $conn->prepare("INSERT INTO riwayat_akademik (nim_mahasiswa, semester, ip_semester, sks_semester) VALUES (?, ?, ?, ?)");

$total_records = 0;
foreach ($mahasiswas as $mhs) {
    $nim = $mhs['nim'];
    $ip = 3.8; // IP awal
    for ($smt = 1; $smt <= 8; $smt++) {
        // Jangan buat data jika IP sudah terlalu rendah
        if ($ip < 1.5) break;

        $sks = rand(18, 24);
        $stmt->bind_param("sidi", $nim, $smt, $ip, $sks);
        $stmt->execute();
        $total_records++;

        // Buat IP berfluktuasi secara realistis
        $ip -= (float)rand(0, 50) / 100;
        if ($ip < 0) $ip = 0;
    }
}

echo "Selesai! **$total_records data** riwayat akademik contoh berhasil dibuat untuk " . count($mahasiswas) . " mahasiswa.<br>";
echo "<strong style='color:red;'>PENTING: Hapus file ini sekarang.</strong>";

$stmt->close();
$conn->close();
?>