<?php
// Konfigurasi Database (Cross-Container)

$host = "mysql_db";   // <--- Nama service dari Docker MySQL Pusat
$user = "root";
$pass = "aldorino04"; // Password dari Docker MySQL Pusat
$db   = "karangasem"; // Pastikan database ini SUDAH ANDA BUAT di phpMyAdmin (localhost:9999)
$port = 3306;         // <--- Gunakan Port 3306 (Port Internal antar container)

// Membuat koneksi
$conn = new mysqli($host, $user, $pass, $db, $port);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?>