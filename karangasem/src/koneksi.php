<?php


$host = "mysql_db";   
$user = "root";
$pass = "aldorino04"; 
$db   = "karangasem"; 
$port = 3306;         


$conn = new mysqli($host, $user, $pass, $db, $port);


if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}


$conn->set_charset("utf8mb4");
?>