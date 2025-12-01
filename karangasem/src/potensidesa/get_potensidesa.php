<?php
header('Content-Type: application/json');

include '../koneksi.php';

$sql = "SELECT * FROM potensi_desa ORDER BY urutan ASC";
$result = $conn->query($sql);

$data = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        
        $btn_data = [
            'show' => false,
            'url' => '#',
            'icon' => ''
        ];

        if ($row['jenis_potensi'] == 'tempat') {
            $btn_data['show'] = true;
            $btn_data['icon'] = 'assistant_direction';
            $btn_data['url'] = "https://www.google.com/maps?daddr=" . $row['latitude_potensi'] . "," . $row['longitude_potensi'];
        } elseif ($row['jenis_potensi'] == 'budaya') {
            $btn_data['show'] = true;
            $btn_data['icon'] = 'language';
            $btn_data['url'] = $row['link_potensi'];
        }
        $row['button_config'] = $btn_data;
        $data[] = $row;
    }
}

echo json_encode($data);
?>