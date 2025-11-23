<?php
// LOAD KONEKSI
include '../koneksi.php';

// LOAD AWS SDK
require 'vendor/autoload.php'; 

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// =========================================
// 1. KONFIGURASI MINIO (MODE DOMAIN)
// =========================================
$minioConfig = [
    'version'     => 'latest',
    'region'      => 'us-east-1',
    
    // ANDA TETAP PAKAI DOMAIN (Sesuai Permintaan)
    'endpoint'    => 'https://cdn.ivanaldorino.web.id', 
    
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => 'admin',       // Pastikan ini benar
        'secret' => 'aldorino04',  // Pastikan ini benar
    ],
    // [PENTING] MATIKAN VERIFIKASI SSL
    // Ini wajib agar Docker tidak menolak sertifikat HTTPS domain sendiri
    'http' => [
        'verify' => false
    ]
];

$bucketName = 'karangasem'; 
$cdnUrlPrefix = "https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lapordesa/";
$folderMinio  = "websiteutama/lapordesa/"; 

// =========================================
// CEK METODE REQUEST
// =========================================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); 
    die("Metode tidak diizinkan.");
}

// =========================================
// VALIDASI INPUT
// =========================================
$nama       = $_POST['nama_user'] ?? '';
$email      = $_POST['email_user'] ?? '';
$nomor      = $_POST['nomor_user'] ?? '';
$alamat     = $_POST['alamat_user'] ?? '';
$rw         = $_POST['lokasi_rw'] ?? '';
$pesan      = $_POST['isi_pesan'] ?? '';
$koordinat  = $_POST['koordinat_gps'] ?? '';

$latitude = null;
$longitude = null;

// Pecah Koordinat
if (!empty($koordinat) && strpos($koordinat, ',') !== false) {
    list($latitude, $longitude) = explode(",", $koordinat);
    $latitude = trim($latitude);
    $longitude = trim($longitude);
}

// =========================================
// VALIDASI FILE
// =========================================
if (!isset($_FILES['foto-laporan-user']) || $_FILES['foto-laporan-user']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); 
    die("File tidak valid atau tidak ada.");
}

$fileSize = $_FILES['foto-laporan-user']['size'];
if ($fileSize > 30 * 1024 * 1024) {
    http_response_code(400);
    die("File terlalu besar (Max 30MB).");
}

// =========================================
// PROSES KONVERSI GAMBAR (KE MEMORY)
// =========================================
$tmpName      = $_FILES['foto-laporan-user']['tmp_name'];
$originalName = $_FILES['foto-laporan-user']['name'];
$fileExt      = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

// Generate Nama File Unik
$dateName = date("Ymd-His");
$cleanNama = preg_replace("/[^a-zA-Z0-9-_]/", "", strtolower(str_replace(" ", "-", $nama)));
$finalFileName = $dateName . "-" . $cleanNama . ".webp";
$objectKey = $folderMinio . $finalFileName;

// Deteksi Tipe File
switch ($fileExt) {
    case 'jpg':
    case 'jpeg':
        $image = imagecreatefromjpeg($tmpName);
        break;
    case 'png':
        $image = imagecreatefrompng($tmpName);
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        break;
    case 'webp':
        $image = imagecreatefromwebp($tmpName);
        break;
    default:
        http_response_code(415); 
        die("Format gambar harus JPG, PNG, atau WEBP.");
}

// Simpan hasil konversi ke variabel (Buffer)
ob_start(); 
imagewebp($image, null, 80); 
$imageContent = ob_get_contents();
ob_end_clean();
imagedestroy($image);

// =========================================
// UPLOAD KE MINIO (Via Domain)
// =========================================
try {
    $s3 = new S3Client($minioConfig);

    $s3->putObject([
        'Bucket'      => $bucketName,
        'Key'         => $objectKey,
        'Body'        => $imageContent,
        'ContentType' => 'image/webp',
        'ACL'         => 'public-read'
    ]);

    // URL FINAL
    $pathDB = $cdnUrlPrefix . $finalFileName;

} catch (AwsException $e) {
    // Jika gagal koneksi ke domain
    http_response_code(500);
    // Kirim pesan error detail agar bisa dicek di Inspect Element
    die("Gagal Upload S3: " . $e->getMessage());
}

// =========================================
// SIMPAN KE DATABASE
// =========================================
$stmt = $conn->prepare("
    INSERT INTO laporandesa
    (nama, email, nomor_telepon, alamat, rw, pesan_keluhan, path_keluhan_foto, latitude, longitude)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssssss",
    $nama, $email, $nomor, $alamat, $rw, $pesan, $pathDB, $latitude, $longitude
);

if ($stmt->execute()) {
    // SUKSES: Kirim kode 200 (OK)
    http_response_code(200);
    echo "Berhasil";
} else {
    // GAGAL: Kirim kode 500
    http_response_code(500);
    echo "Database Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>