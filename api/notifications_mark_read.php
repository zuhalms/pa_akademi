<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'unauth']); exit(); }
$role = $_SESSION['user_role'] ?? 'mahasiswa';
$user = $_SESSION['user_id'];
$conn = new mysqli('localhost','root','','db_pa_akademi');
if ($conn->connect_error) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'db']); exit(); }

// Accept POST params: type (krs|logbook) and optional nim (for dosen marking specific student)
$type = $_POST['type'] ?? null;
$nim_param = $_POST['nim'] ?? null;

try {
    if ($role === 'mahasiswa') {
        if ($type === 'krs') {
            $stmt = $conn->prepare("UPDATE mahasiswa SET krs_notif_dilihat = TRUE WHERE nim = ?");
            $stmt->bind_param('s', $user); $stmt->execute(); $stmt->close();
            echo json_encode(['ok'=>true,'marked'=>'krs']);
            $conn->close(); exit();
        } elseif ($type === 'logbook') {
            // Mark only logbook entries for this student (from Dosen)
            $stmt2 = $conn->prepare("UPDATE logbook SET status_baca = 'Dibaca' WHERE nim_mahasiswa = ? AND pengisi = 'Dosen'");
            $stmt2->bind_param('s', $user); $stmt2->execute(); $stmt2->close();
            echo json_encode(['ok'=>true,'marked'=>'logbook']);
            $conn->close(); exit();
        } else {
            // No type provided: do nothing to avoid accidental mass-marking
            echo json_encode(['ok'=>false,'error'=>'no_type']);
            $conn->close(); exit();
        }
    } else {
        // Dosen
        if ($type === 'logbook' && !empty($nim_param)) {
            // Mark logbook entries from a specific mahasiswa (that belong to this dosen) as read
            $stmt = $conn->prepare("UPDATE logbook SET status_baca = 'Dibaca' WHERE id_dosen = ? AND nim_mahasiswa = ? AND pengisi = 'Mahasiswa'");
            $stmt->bind_param('is', $user, $nim_param); $stmt->execute(); $stmt->close();
            echo json_encode(['ok'=>true,'marked'=>'logbook','nim'=>$nim_param]);
            $conn->close(); exit();
        } else {
            echo json_encode(['ok'=>false,'error'=>'invalid']);
            $conn->close(); exit();
        }
    }
} catch (Exception $e) {
    http_response_code(500); echo json_encode(['ok'=>false,'error'=>'exception']);
}

$conn->close();
