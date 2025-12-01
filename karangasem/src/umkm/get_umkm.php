<?php
header('Content-Type: application/json; charset=utf-8');
include '../koneksi.php';

$umkmSql = "SELECT id, nama_usaha, deskripsi_usaha, kategori_usaha, 
            nama_pemilik_usaha, kontak_usaha, alamat_usaha, 
            latitude, longitude, path_foto_usaha, qris, created_at,
            punya_whatsapp, no_wa_apakahsama, no_wa_berbeda,
            punya_instagram, username_instagram,
            punya_facebook, link_facebook
            FROM umkm
            WHERE latitude IS NOT NULL 
            AND longitude IS NOT NULL
            AND diacc = 1"; 

$res = $conn->query($umkmSql);
$out = [];

if ($res) {
    while ($row = $res->fetch_assoc()) {

        $id = (int)$row['id'];

        $prodStmt = $conn->prepare(
            "SELECT id, nama_produk, harga_produk, deskripsi_produk, 
                    path_foto_produk, created_at 
             FROM umkmproduk 
             WHERE umkm_id = ? ORDER BY id ASC"
        );

        $prodStmt->bind_param("i", $id);
        $prodStmt->execute();
        $prodRes = $prodStmt->get_result();

        $prods = [];
        while ($p = $prodRes->fetch_assoc()) {
            $prods[] = $p;
        }

        $row['produk'] = $prods;

        // cast numeric
        $row['qris'] = (int)$row['qris'];
        $row['latitude'] = (float)$row['latitude'];
        $row['longitude'] = (float)$row['longitude'];

        $out[] = $row;
    }
}

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$conn->close();
?>