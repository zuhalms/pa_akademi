<?php
// =================================================================
// SKRIP UNTUK MENGIMPOR DATA DARI CSV KE DATABASE
// Cukup jalankan file ini sekali saja melalui browser.
// =================================================================

// --- 1. Konfigurasi Koneksi Database ---
$host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Default XAMPP kosong
$db_name = 'db_pa_akademi';

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
echo "<h1>Proses Impor Data</h1>";
echo "Koneksi ke database berhasil.<br>";

// --- 2. Baca File CSV ---
$file_path = 'data_mahasiswa.csv';
if (!file_exists($file_path)) {
    die("Error: File '{$file_path}' tidak ditemukan. Pastikan file ada di folder yang sama.");
}

$file = fopen($file_path, 'r');

// Lewati baris header di CSV (5 baris pertama dari file Anda)
for ($i = 0; $i < 5; $i++) {
    fgetcsv($file);
}

echo "Membaca file CSV...<br>";
$rowCount = 0;

// --- 3. Proses Setiap Baris Data ---
while (($row = fgetcsv($file)) !== FALSE) {
    // Skip jika baris kosong
    if (empty($row[1])) continue;

    // Ambil data dari setiap kolom CSV
    $nim = trim($row[1]);
    $nama_mahasiswa = trim($row[2]);
    $nama_prodi = trim($row[3]);
    $angkatan = trim($row[4]);
    $status_semester = trim($row[5]);
    $semester_berjalan = (int)trim($row[6]);
    $sks_semester = (int)trim($row[7]);
    $batas_sks = (int)trim($row[8]);
    $total_sks = (int)trim($row[9]);
    $ips = (float)trim($row[10]);
    $ipk = (float)trim($row[11]);
    $krs_disetujui = ($row[12] === 'Ya' || $row[12] === 'TRUE');

    // Proses data Dosen PA (memisahkan NIDN dan Nama)
    $dosen_pa_raw = trim($row[13]);
    list($nidn_dosen, $nama_dosen) = explode(' - ', $dosen_pa_raw, 2);
    $nidn_dosen = trim($nidn_dosen);
    $nama_dosen = trim($nama_dosen);

    // --- 4. Masukkan Data ke Tabel Relasional ---

    // a. Proses Program Studi
    $prodi_stmt = $conn->prepare("SELECT id_prodi FROM program_studi WHERE nama_prodi = ?");
    $prodi_stmt->bind_param("s", $nama_prodi);
    $prodi_stmt->execute();
    $prodi_result = $prodi_stmt->get_result();
    if ($prodi_result->num_rows > 0) {
        $id_prodi = $prodi_result->fetch_assoc()['id_prodi'];
    } else {
        $insert_prodi_stmt = $conn->prepare("INSERT INTO program_studi (nama_prodi) VALUES (?)");
        $insert_prodi_stmt->bind_param("s", $nama_prodi);
        $insert_prodi_stmt->execute();
        $id_prodi = $conn->insert_id;
    }

    // b. Proses Dosen
    $dosen_stmt = $conn->prepare("SELECT id_dosen FROM dosen WHERE nidn_dosen = ?");
    $dosen_stmt->bind_param("s", $nidn_dosen);
    $dosen_stmt->execute();
    $dosen_result = $dosen_stmt->get_result();
    if ($dosen_result->num_rows > 0) {
        $id_dosen_pa = $dosen_result->fetch_assoc()['id_dosen'];
    } else {
        $insert_dosen_stmt = $conn->prepare("INSERT INTO dosen (nidn_dosen, nama_dosen) VALUES (?, ?)");
        $insert_dosen_stmt->bind_param("ss", $nidn_dosen, $nama_dosen);
        $insert_dosen_stmt->execute();
        $id_dosen_pa = $conn->insert_id;
    }

    // c. Masukkan Data Mahasiswa (gunakan INSERT IGNORE untuk menghindari error duplikat NIM)
    $mhs_stmt = $conn->prepare("
        INSERT IGNORE INTO mahasiswa (nim, nama_mahasiswa, angkatan, status_semester, semester_berjalan, sks_semester, batas_sks, total_sks, ips, ipk, krs_disetujui, id_prodi, id_dosen_pa)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $mhs_stmt->bind_param("ssisiiiiiidii", $nim, $nama_mahasiswa, $angkatan, $status_semester, $semester_berjalan, $sks_semester, $batas_sks, $total_sks, $ips, $ipk, $krs_disetujui, $id_prodi, $id_dosen_pa);
    $mhs_stmt->execute();
    $rowCount++;
}

fclose($file);
echo "<br>Selesai! Sebanyak **" . $rowCount . " baris data mahasiswa** telah berhasil diproses dan diimpor ke database.<br>";
echo "<strong style='color:red;'>PENTING: Hapus atau ganti nama file 'import_data.php' ini setelah selesai untuk keamanan.</strong>";

$conn->close();
?>