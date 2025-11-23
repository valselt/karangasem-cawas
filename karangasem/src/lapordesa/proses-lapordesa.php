<?php
// --- SETUP DEBUGGING ---
ini_set('display_errors', 0); // Matikan display error agar tidak merusak respon JSON/AJAX
error_reporting(E_ALL);

function catatLog($pesan) {
    $waktu = date("Y-m-d H:i:s");
    file_put_contents("debug_log.txt", "[$waktu] $pesan" . PHP_EOL, FILE_APPEND);
}

catatLog("=== MULAI REQUEST BARU ===");

// --- 1. LOAD KONEKSI ---
// Cek folder koneksi (Naik 1 level atau 2 level)
$pathKoneksi = '';
if (file_exists('../koneksi.php')) {
    $pathKoneksi = '../koneksi.php';
} elseif (file_exists('../../koneksi.php')) {
    $pathKoneksi = '../../koneksi.php';
} else {
    catatLog("CRITICAL: File koneksi.php tidak ditemukan!");
    http_response_code(500); die();
}

include $pathKoneksi;
if (!$conn) {
    catatLog("CRITICAL: Koneksi database gagal.");
    http_response_code(500); die();
}
catatLog("Database Connected. Host: " . $conn->host_info);

// --- 2. LOAD AWS SDK (AUTO DETECT PATH) ---
// Ini perbaikan utamanya: Mencari folder vendor di berbagai level direktori
$possiblePaths = [
    __DIR__ . '/vendor/autoload.php',       // Cek di folder ini
    __DIR__ . '/../vendor/autoload.php',    // Cek naik 1 level
    __DIR__ . '/../../vendor/autoload.php', // Cek naik 2 level
    '/var/www/html/vendor/autoload.php'     // Cek path absolut Docker
];

$vendorFound = false;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        require $path;
        catatLog("Vendor ditemukan di: $path");
        $vendorFound = true;
        break;
    }
}

if (!$vendorFound) {
    catatLog("CRITICAL: Folder 'vendor' TIDAK DITEMUKAN di manapun!");
    catatLog("Posisi file saat ini: " . __DIR__);
    http_response_code(500);
    die("Server Error: Library AWS hilang.");
}

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

catatLog("AWS SDK Loaded Berhasil.");

// --- 3. KONFIGURASI MINIO ---
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

// --- 4. VALIDASI INPUT ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); die();
}

$nama = $_POST['nama_user'] ?? 'Tanpa Nama';
catatLog("User: $nama");

if (!isset($_FILES['foto-laporan-user']) || $_FILES['foto-laporan-user']['error'] !== UPLOAD_ERR_OK) {
    catatLog("Error Upload: " . ($_FILES['foto-laporan-user']['error'] ?? 'File tidak ada'));
    http_response_code(400); die();
}

// --- 5. PROSES GAMBAR (METODE FILE SYSTEM) ---
$tmpName = $_FILES['foto-laporan-user']['tmp_name'];
$imageInfo = getimagesize($tmpName);
if (!$imageInfo) {
    catatLog("File bukan gambar valid.");
    http_response_code(400); die();
}

// Konversi WebP
$tempWebp = tempnam(sys_get_temp_dir(), 'webp');
$mime = $imageInfo['mime'];
$imgResource = null;

if ($mime == 'image/jpeg') $imgResource = imagecreatefromjpeg($tmpName);
elseif ($mime == 'image/png') $imgResource = imagecreatefrompng($tmpName);
elseif ($mime == 'image/webp') $imgResource = imagecreatefromwebp($tmpName);

if ($imgResource) {
    imagewebp($imgResource, $tempWebp, 80);
    imagedestroy($imgResource);
} else {
    // Jika gagal buat resource, copy saja file aslinya (fallback)
    copy($tmpName, $tempWebp);
}

// --- 6. UPLOAD MINIO ---
$cleanNama = preg_replace("/[^a-zA-Z0-9-_]/", "", strtolower(str_replace(" ", "-", $nama)));
$finalFileName = date("Ymd-His") . "-" . $cleanNama . ".webp";
$targetKey = "websiteutama/lapordesa/" . $finalFileName;

try {
    $s3 = new S3Client($minioConfig);
    $result = $s3->putObject([
        'Bucket'     => $bucketName,
        'Key'        => $targetKey,
        'SourceFile' => $tempWebp, // Metode stabil
        'ACL'        => 'public-read',
        'ContentType'=> 'image/webp'
    ]);
    
    catatLog("MinIO Upload OK: " . $targetKey);
    unlink($tempWebp);
} catch (AwsException $e) {
    catatLog("MinIO Gagal: " . $e->getMessage());
    unlink($tempWebp);
    http_response_code(500); die();
}

// --- 7. DATABASE ---
$cdnUrlPrefix = "https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lapordesa/";
$pathDB = $cdnUrlPrefix . $finalFileName;

$sql = "INSERT INTO laporandesa (nama, email, nomor_telepon, alamat, rw, pesan_keluhan, path_keluhan_foto, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

$email = $_POST['email_user'] ?? '';
$nomor = $_POST['nomor_user'] ?? '';
$alamat = $_POST['alamat_user'] ?? '';
$rw = $_POST['lokasi_rw'] ?? '';
$pesan = $_POST['isi_pesan'] ?? '';
$gps = $_POST['koordinat_gps'] ?? '';
$lat = null; $lng = null;
if(!empty($gps) && strpos($gps, ',') !== false) { list($lat, $lng) = explode(",", $gps); }

$stmt->bind_param("sssssssss", $nama, $email, $nomor, $alamat, $rw, $pesan, $pathDB, $lat, $lng);

if ($stmt->execute()) {
    catatLog("DB Insert OK. ID: " . $stmt->insert_id);
    http_response_code(200);
} else {
    catatLog("DB Insert Gagal: " . $stmt->error);
    http_response_code(500);
}

$stmt->close();
$conn->close();
?>