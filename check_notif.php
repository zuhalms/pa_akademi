<?php
session_start();

// Jika tidak ada sesi login, hentikan.
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit();
}

 $host = 'sql306.infinityfree.com';
    $db_user = 'if0_40207082';
    $db_pass = 'apabagus125';
    $db_name = 'if0_40207082_db_pa';
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    echo json_encode(['count' => 0]);
    exit();
}

$total_notif = 0;
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Logika pengecekan notifikasi
if ($user_role == 'dosen') {
    // Dosen mengecek logbook baru dari mahasiswa
    $stmt = $conn->prepare("SELECT COUNT(id_log) as jumlah FROM logbook WHERE id_dosen = ? AND pengisi = 'Mahasiswa' AND status_baca = 'Belum Dibaca'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $total_notif = $result['jumlah'] ?? 0;

} elseif ($user_role == 'mahasiswa') {
    // Mahasiswa mengecek logbook baru dari dosen
    $stmt_log = $conn->prepare("SELECT COUNT(id_log) as jumlah FROM logbook WHERE nim_mahasiswa = ? AND pengisi = 'Dosen' AND status_baca = 'Belum Dibaca'");
    $stmt_log->bind_param("s", $user_id);
    $stmt_log->execute();
    $result_log = $stmt_log->get_result()->fetch_assoc();
    $log_count = $result_log['jumlah'] ?? 0;

    // Mahasiswa juga mengecek KRS yang baru disetujui
    $stmt_krs = $conn->prepare("SELECT COUNT(nim) as jumlah FROM mahasiswa WHERE nim = ? AND krs_disetujui = TRUE AND krs_notif_dilihat = FALSE");
    $stmt_krs->bind_param("s", $user_id);
    $stmt_krs->execute();
    $result_krs = $stmt_krs->get_result()->fetch_assoc();
    $krs_count = $result_krs['jumlah'] ?? 0;

    $total_notif = $log_count + $krs_count;
}

$conn->close();

// Kembalikan hasilnya dalam format JSON yang bisa dibaca JavaScript
header('Content-Type: application/json');
echo json_encode(['count' => $total_notif]);
?>