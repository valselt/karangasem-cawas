<?php
require 'koneksi.php';

$sql = "SELECT id, nama, latitude, longitude, jenis, path_foto FROM lokasi_umum";
$result = $conn->query($sql);

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>
