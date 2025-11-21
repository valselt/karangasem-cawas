<?php
// Header agar browser tahu ini adalah data JSON
header('Content-Type: application/json');

// Include koneksi (sesuaikan path jika perlu, asumsi file ini ada di folder potensidesa)
include '../koneksi.php';

$sql = "SELECT * FROM potensi_desa ORDER BY urutan ASC";
$result = $conn->query($sql);

$data = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        
        // --- LOGIKA PRE-PROCESSING UNTUK BUTTON ---
        // Kita siapkan datanya di sini agar JS tinggal pakai
        $btn_data = [
            'show' => false,
            'url' => '#',
            'icon' => ''
        ];

        if ($row['jenis_potensi'] == 'tempat') {
            $btn_data['show'] = true;
            $btn_data['icon'] = 'assistant_direction';
            // Menambahkan koma antara lat dan long
            $btn_data['url'] = "https://www.google.com/maps?daddr=" . $row['latitude_potensi'] . "," . $row['longitude_potensi'];
        } elseif ($row['jenis_potensi'] == 'budaya') {
            $btn_data['show'] = true;
            $btn_data['icon'] = 'language';
            $btn_data['url'] = $row['link_potensi'];
        }
        // Jika 'none', 'show' tetap false

        // Masukkan data tombol yang sudah diolah ke dalam array baris
        $row['button_config'] = $btn_data;
        
        // Masukkan ke array utama
        $data[] = $row;
    }
}

// Keluarkan output JSON
echo json_encode($data);
?>