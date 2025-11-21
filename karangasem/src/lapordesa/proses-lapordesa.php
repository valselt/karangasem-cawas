<?php
// LOAD KONEKSI
include '../koneksi.php';

// =========================================
// CEK METODE
// =========================================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Akses tidak valid.");
}

// =========================================
// AMBIL DATA FORM
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

// Pisahkan koordinat jika ada
if (!empty($koordinat) && strpos($koordinat, ',') !== false) {
    list($latitude, $longitude) = explode(",", $koordinat);
    $latitude = trim($latitude);
    $longitude = trim($longitude);
}

// =========================================
// VALIDASI UKURAN FILE (LIMIT 30 MB)
// =========================================
if ($_FILES['foto-laporan-user']['error'] !== UPLOAD_ERR_OK) {
    die("Gagal upload file.");
}

$fileSize = $_FILES['foto-laporan-user']['size'];
$maxSize  = 30 * 1024 * 1024; // 30 MB

if ($fileSize > $maxSize) {
    die("Ukuran file terlalu besar. Maksimal 30 MB.");
}

// =========================================
// PERSIAPAN KONVERSI WEBP
// =========================================
$folderPath = "../img/lapordesa/";
if (!is_dir($folderPath)) {
    mkdir($folderPath, 0777, true);
}

$originalName = $_FILES['foto-laporan-user']['name'];
$tmpName      = $_FILES['foto-laporan-user']['tmp_name'];
$fileExt      = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

// Nama file output: (tanggal)(jam)-(nama).webp
$dateName = date("Ymd-His");
$cleanNama = preg_replace("/[^a-zA-Z0-9-_]/", "", strtolower(str_replace(" ", "-", $nama)));
$finalFileName = $dateName . "-" . $cleanNama . ".webp";

$finalPath = $folderPath . $finalFileName;

// =========================================
// KONVERSI MENJADI WEBP
// =========================================

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
        // langsung simpan ulang (tanpa konversi)
        $image = imagecreatefromwebp($tmpName);
        break;

    default:
        die("Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.");
}

// Simpan versi WEBP
if (!imagewebp($image, $finalPath, 80)) {
    die("Gagal menyimpan file WEBP.");
}

imagedestroy($image);

// Simpan path untuk database
$pathDB = "img/lapordesa/" . $finalFileName;

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
    $nama,
    $email,
    $nomor,
    $alamat,
    $rw,
    $pesan,
    $pathDB,
    $latitude,
    $longitude
);

if ($stmt->execute()) {
    echo "<script>alert('Laporan berhasil dikirim!'); window.location.href='index.php';</script>";
} else {
    echo "Gagal menyimpan data: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
