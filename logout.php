<?php
// Selalu mulai sesi di awal untuk bisa memanipulasinya
session_start();

// Hapus semua data yang tersimpan di dalam sesi (seperti user_id dan user_role)
session_unset();

// Hancurkan sesi itu sendiri secara permanen
session_destroy();

// Setelah sesi dihancurkan, arahkan pengguna kembali ke halaman login
header("Location: login.php");
exit(); // Pastikan tidak ada kode lain yang dieksekusi setelah pengalihan
?>