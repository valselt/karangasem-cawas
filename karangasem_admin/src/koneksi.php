<?php

$host = "100.115.160.110"; 
$user = "root"; 
$pass = "aldorino04"; 
$db   = "karangasem"; 


$port = 3306; 


$conn = new mysqli($host, $user, $pass, $db, $port);


if ($conn->connect_error) {

    die("Koneksi ke CasaOS Gagal: (" . $conn->connect_errno . ") " . $conn->connect_error);
}


$conn->set_charset("utf8mb4");
?>