<?php
// Simple JSON endpoint to return notification counts for the logged-in user
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not_logged_in']);
    exit();
}

$role = $_SESSION['user_role'];
$uid = $_SESSION['user_id'];

$host = 'localhost'; $db_user = 'root'; $db_pass = ''; $db_name = 'db_pa_akademi';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) { echo json_encode(['error' => 'db']); exit(); }

$data = ['count' => 0, 'items' => []];

if ($role == 'dosen') {
    // New logbook entries from mahasiswa to this dosen
    $stmt = $conn->prepare("SELECT l.nim_mahasiswa, m.nama_mahasiswa, COUNT(l.id_log) as jumlah FROM logbook l JOIN mahasiswa m ON l.nim_mahasiswa = m.nim WHERE l.id_dosen = ? AND l.pengisi = 'Mahasiswa' AND l.status_baca = 'Belum Dibaca' GROUP BY l.nim_mahasiswa, m.nama_mahasiswa ORDER BY m.nama_mahasiswa ASC");
    $stmt->bind_param('i', $uid);
    $stmt->execute(); $res = $stmt->get_result();
    $total = 0; $items = [];
    while ($r = $res->fetch_assoc()) { $total += (int)$r['jumlah']; $items[] = ['type' => 'logbook', 'nim' => $r['nim_mahasiswa'], 'name' => $r['nama_mahasiswa'], 'count' => (int)$r['jumlah']]; }
    $data['count'] = $total; $data['items'] = $items;
} elseif ($role == 'mahasiswa') {
    // Check if KRS approved recently or new logbook from dosen
    $stmt = $conn->prepare("SELECT krs_disetujui FROM mahasiswa WHERE nim = ?");
    $stmt->bind_param('s', $uid);
    $stmt->execute(); $cur = $stmt->get_result()->fetch_assoc();
    if ($cur && $cur['krs_disetujui']) { $data['items'][] = ['type' => 'krs', 'message' => 'KRS Anda telah disetujui']; $data['count']++; }
    $stmt2 = $conn->prepare("SELECT COUNT(id_log) as jumlah FROM logbook WHERE nim_mahasiswa = ? AND pengisi = 'Dosen' AND status_baca = 'Belum Dibaca'");
    $stmt2->bind_param('s', $uid); $stmt2->execute(); $r2 = $stmt2->get_result()->fetch_assoc();
    if ($r2 && $r2['jumlah'] > 0) { $data['items'][] = ['type' => 'logbook', 'count' => (int)$r2['jumlah']]; $data['count'] += (int)$r2['jumlah']; }
}

$conn->close();

echo json_encode($data);

?>
