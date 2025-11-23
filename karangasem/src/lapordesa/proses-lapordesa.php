<?php
// --- 1. SETTING PHP AGAR LEBIH KUAT ---
set_time_limit(600);
ini_set('memory_limit', '512M'); 
ini_set('display_errors', 0); // Matikan display error agar tidak merusak AJAX
error_reporting(E_ALL);

// LOAD KONEKSI
// Cek folder koneksi (Naik 1 level atau 2 level agar aman)
$pathKoneksi = '';
if (file_exists('../koneksi.php')) {
    $pathKoneksi = '../koneksi.php';
} elseif (file_exists('../../koneksi.php')) {
    $pathKoneksi = '../../koneksi.php';
} else {
    http_response_code(500); die("Error: File koneksi.php tidak ditemukan");
}
include $pathKoneksi;

// LOAD AWS SDK (AUTO DETECT PATH)
$possiblePaths = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    '/var/www/html/vendor/autoload.php'
];
$vendorFound = false;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) { require $path; $vendorFound = true; break; }
}
if (!$vendorFound) { http_response_code(500); die("Error: Library AWS tidak ditemukan"); }

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// =========================================
// 2. KONFIGURASI MINIO
// =========================================
$minioConfig = [
    'version'     => 'latest',
    'region'      => 'us-east-1',
    'endpoint'    => 'https://cdn.ivanaldorino.web.id', 
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => 'admin',
        'secret' => 'aldorino04',
    ],
    'http' => [
        'verify' => false // Bypass SSL
    ]
];

$bucketName = 'karangasem'; 
$cdnUrlPrefix = "https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lapordesa/";

// =========================================
// 3. FUNGSI UPLOAD HELPER
// =========================================
function uploadToMinio($fileArray, $namaFileBaru, $s3Client, $bucket) {
    if (!isset($fileArray) || $fileArray['error'] !== UPLOAD_ERR_OK) return false;

    $tmpName = $fileArray['tmp_name'];
    $imageInfo = getimagesize($tmpName);
    if (!$imageInfo) return false;
    
    $mime = $imageInfo['mime'];
    $imgResource = null;

    switch ($mime) {
        case 'image/jpeg': $imgResource = imagecreatefromjpeg($tmpName); break;
        case 'image/png':  $imgResource = imagecreatefrompng($tmpName); break;
        case 'image/webp': $imgResource = imagecreatefromwebp($tmpName); break;
    }

    if ($imgResource) {
        // Buat file temporary fisik
        $tempWebp = tempnam(sys_get_temp_dir(), 'webp');
        imagewebp($imgResource, $tempWebp, 80); 
        imagedestroy($imgResource);
        
        $targetKey = "websiteutama/lapordesa/" . $namaFileBaru;

        try {
            // Upload pakai SourceFile (METODE YANG SUKSES)
            $s3Client->putObject([
                'Bucket'     => $bucket,
                'Key'        => $targetKey,
                'SourceFile' => $tempWebp, 
                'ACL'        => 'public-read',
                'ContentType'=> 'image/webp'
            ]);
            unlink($tempWebp);
            return true;
        } catch (AwsException $e) {
            if (file_exists($tempWebp)) unlink($tempWebp);
            return false; 
        }
    }
    return false;
}

// =========================================
// 4. LOGIKA UTAMA
// =========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") { http_response_code(405); die(); }

// Ambil Data
$nama       = $_POST['nama_user'] ?? '';
$email      = $_POST['email_user'] ?? '';
$nomor      = $_POST['nomor_user'] ?? '';
$alamat     = $_POST['alamat_user'] ?? '';
$rw         = $_POST['lokasi_rw'] ?? '';
$pesan      = $_POST['isi_pesan'] ?? '';
$koordinat  = $_POST['koordinat_gps'] ?? ''; // Javascript akan mengisi ini

// Parsing Koordinat
$latitude = null; $longitude = null;
if (!empty($koordinat) && strpos($koordinat, ',') !== false) {
    list($latitude, $longitude) = explode(",", $koordinat);
    $latitude = trim($latitude); $longitude = trim($longitude);
}

// Upload File
if (!isset($_FILES['foto-laporan-user']) || $_FILES['foto-laporan-user']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); die("File error");
}

$cleanNama = preg_replace("/[^a-zA-Z0-9-_]/", "", strtolower(str_replace(" ", "-", $nama)));
$finalFileName = date("Ymd-His") . "-" . $cleanNama . ".webp";

try {
    $s3 = new S3Client($minioConfig);
} catch (Exception $e) {
    http_response_code(500); die("MinIO Connection Error");
}

// Eksekusi Upload
if (uploadToMinio($_FILES['foto-laporan-user'], $finalFileName, $s3, $bucketName)) {
    
    // Simpan ke DB
    $pathDB = $cdnUrlPrefix . $finalFileName;
    $stmt = $conn->prepare("INSERT INTO laporandesa (nama, email, nomor_telepon, alamat, rw, pesan_keluhan, path_keluhan_foto, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $nama, $email, $nomor, $alamat, $rw, $pesan, $pathDB, $latitude, $longitude);

    if ($stmt->execute()) {
        http_response_code(200); // Kode sukses untuk Javascript
        echo "Berhasil";
    } else {
        http_response_code(500);
        echo "Database Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    http_response_code(500);
    echo "Gagal Upload ke Storage";
}

$conn->close();
?>