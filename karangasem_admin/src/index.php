<?php
session_start(); // WAJIB PALING ATAS
ob_start();

// 1. CEK LOGIN
if (!isset($_SESSION['login_status']) || $_SESSION['login_status'] !== true) {
    header("Location: login.php");
    exit;
}

// AMBIL DATA USER DARI SESSION
$currentUserId = $_SESSION['user_id'];
$currentUserLevel = $_SESSION['level']; // 'perangkat_desa', 'rw', 'user'
$currentUserName = $_SESSION['nama_lengkap'];

// --- CONFIG ---
set_time_limit(600);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);

include 'koneksi.php';
require 'vendor/autoload.php'; 

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$minioConfig = [
    'version' => 'latest',
    'region'  => 'us-east-1',
    'endpoint' => 'https://cdn.ivanaldorino.web.id',
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => 'admin',
        'secret' => 'aldorino04',
    ],
];
$bucketName = 'karangasem'; 
$s3 = new S3Client($minioConfig); 

function slugify($text) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '', $text)));
}

function uploadImageToMinio($fileArray, $targetKey, $s3Client, $bucket) {
    if (isset($fileArray) && $fileArray['error'] === UPLOAD_ERR_OK) {
        $tmpName = $fileArray['tmp_name'];
        $imageInfo = getimagesize($tmpName);
        $mime = $imageInfo['mime'];
        $imgResource = null;

        switch ($mime) {
            case 'image/jpeg': $imgResource = imagecreatefromjpeg($tmpName); break;
            case 'image/png':  $imgResource = imagecreatefrompng($tmpName); break;
            case 'image/webp': $imgResource = imagecreatefromwebp($tmpName); break;
        }

        if ($imgResource) {
            $tempWebp = tempnam(sys_get_temp_dir(), 'webp');
            imagewebp($imgResource, $tempWebp, 80); 
            imagedestroy($imgResource);
            
            try {
                $s3Client->putObject([
                    'Bucket' => $bucket,
                    'Key' => $targetKey,
                    'SourceFile' => $tempWebp,
                    'ACL' => 'public-read',
                    'ContentType' => 'image/webp'
                ]);
                unlink($tempWebp);
                return "https://cdn.ivanaldorino.web.id/" . $bucket . "/" . $targetKey;
            } catch (AwsException $e) {
                return null;
            }
        }
    }
    return null;
}

// --- LOGIC HALAMAN ---
// Tentukan halaman default berdasarkan level
$defaultPage = 'potensi';
if ($currentUserLevel == 'rw') $defaultPage = 'lapor';
if ($currentUserLevel == 'user') $defaultPage = 'umkm';

$page = isset($_GET['page']) ? $_GET['page'] : $defaultPage;

// CEK AKSES HALAMAN (Security Layer)
if ($currentUserLevel == 'rw' && $page != 'lapor') {
    $page = 'lapor'; // Paksa ke lapor
}
if ($currentUserLevel == 'user' && $page != 'umkm') {
    $page = 'umkm'; // Paksa ke umkm
}

// --- VARIABEL EDIT ---
$editMode = false;
$editData = null;

if (isset($_GET['action']) && $_GET['action'] == 'edit_potensi' && isset($_GET['id']) && $currentUserLevel == 'perangkat_desa') {
    $id = intval($_GET['id']);
    $queryEdit = $conn->query("SELECT * FROM potensi_desa WHERE id = $id");
    if ($queryEdit->num_rows > 0) {
        $editMode = true;
        $editData = $queryEdit->fetch_assoc();
    }
}

// --- LOGIC POST ---

// 1. SIMPAN POTENSI (Hanya Perangkat Desa)
if (isset($_POST['simpan_potensi']) && $currentUserLevel == 'perangkat_desa') { 
    $nama = $_POST['nama'];
    $jenis = $_POST['jenis'];
    $deskripsi = $_POST['deskripsi'];
    $lat = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $lng = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
    $link = !empty($_POST['link_website']) ? $_POST['link_website'] : null;
    
    $isUpdate = isset($_POST['id_potensi']) && !empty($_POST['id_potensi']);
    $fotoUrl = $isUpdate ? $_POST['foto_lama'] : null; 

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nama)));
        $fileName = "websiteutama/potensi_desa/" . $slug . "-" . time() . ".webp";
        $uploaded = uploadImageToMinio($_FILES['foto'], $fileName, $s3, $bucketName);
        if ($uploaded) $fotoUrl = $uploaded;
    }

    if ($isUpdate) {
        $id = intval($_POST['id_potensi']);
        $stmt = $conn->prepare("UPDATE potensi_desa SET nama_potensi=?, jenis_potensi=?, deskripsi_potensi=?, path_foto_potensi=?, link_potensi=?, latitude_potensi=?, longitude_potensi=? WHERE id=?");
        $stmt->bind_param("ssssssdi", $nama, $jenis, $deskripsi, $fotoUrl, $link, $lat, $lng, $id);
        $redirectStatus = "success_edit";
    } else {
        $cekUrutan = $conn->query("SELECT MAX(urutan) as max_urutan FROM potensi_desa");
        $rowUrutan = $cekUrutan->fetch_assoc();
        $nextUrutan = $rowUrutan['max_urutan'] + 1;
        $stmt = $conn->prepare("INSERT INTO potensi_desa (nama_potensi, jenis_potensi, deskripsi_potensi, path_foto_potensi, link_potensi, latitude_potensi, longitude_potensi, urutan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssdi", $nama, $jenis, $deskripsi, $fotoUrl, $link, $lat, $lng, $nextUrutan);
        $redirectStatus = "success_add";
    }

    if ($stmt->execute()) {
        header("Location: ?page=potensi&status=" . $redirectStatus);
        exit;
    }
}

// 2. SIMPAN UMKM (User & Perangkat Desa)
if (isset($_POST['simpan_umkm'])) {
    $namaUsaha = $_POST['nama_usaha'];
    $pemilik = $_POST['nama_pemilik'];
    $kategori = $_POST['kategori'];
    $kontak = $_POST['kontak'];
    $alamat = $_POST['alamat'];
    $deskripsiUsaha = $_POST['deskripsi_usaha'];
    $lat = !empty($_POST['latitude']) ? $_POST['latitude'] : 0;
    $lng = !empty($_POST['longitude']) ? $_POST['longitude'] : 0;
    
    $qris = isset($_POST['qris']) ? 1 : 0;
    $punyaWa = isset($_POST['punya_wa']) ? 1 : 0;
    $waSama = isset($_POST['wa_sama']) ? 1 : 0;
    $waBeda = ($punyaWa && !$waSama) ? $_POST['wa_beda'] : null;
    $punyaIg = isset($_POST['punya_ig']) ? 1 : 0;
    $userIg = $punyaIg ? $_POST['user_ig'] : null;
    $punyaFb = isset($_POST['punya_fb']) ? 1 : 0;
    $linkFb = $punyaFb ? $_POST['link_fb'] : null;

    $pathFotoUsaha = null;
    if (isset($_FILES['foto_usaha']) && $_FILES['foto_usaha']['error'] === UPLOAD_ERR_OK) {
        $cleanNamaUsaha = slugify($namaUsaha);
        $tgl = date('Ymd-His');
        $keyUmkm = "websiteutama/umkm/" . $cleanNamaUsaha . "_" . $tgl . ".webp";
        $pathFotoUsaha = uploadImageToMinio($_FILES['foto_usaha'], $keyUmkm, $s3, $bucketName);
    }

    $userIdToInsert = $currentUserId;
    $statusDiacc = ($currentUserLevel == 'perangkat_desa') ? 1 : 0;

    $stmtUmkm = $conn->prepare("INSERT INTO umkm (id_user, nama_usaha, deskripsi_usaha, kategori_usaha, nama_pemilik_usaha, kontak_usaha, alamat_usaha, latitude, longitude, path_foto_usaha, diacc, qris, punya_whatsapp, no_wa_apakahsama, no_wa_berbeda, punya_instagram, username_instagram, punya_facebook, link_facebook) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmtUmkm->bind_param("issssssddsiiiisisis", 
        $userIdToInsert, $namaUsaha, $deskripsiUsaha, $kategori, $pemilik, $kontak, $alamat, $lat, $lng, $pathFotoUsaha, 
        $statusDiacc, // Gunakan variabel ini
        $qris, $punyaWa, $waSama, $waBeda, $punyaIg, $userIg, $punyaFb, $linkFb
    );

    if ($stmtUmkm->execute()) {
        $newUmkmId = $conn->insert_id;

        if (isset($_POST['nama_produk']) && is_array($_POST['nama_produk'])) {
            $stmtProduk = $conn->prepare("INSERT INTO umkmproduk (umkm_id, nama_produk, harga_produk, deskripsi_produk, path_foto_produk) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($_POST['nama_produk'] as $key => $nmProduk) {
                $hrgProduk = str_replace('.', '', $_POST['harga_produk'][$key]);
                $descProduk = $_POST['deskripsi_produk'][$key];
                $pathFotoProduk = null;
                
                if (isset($_FILES['foto_produk']['name'][$key]) && $_FILES['foto_produk']['error'][$key] === UPLOAD_ERR_OK) {
                    $singleFile = [
                        'name' => $_FILES['foto_produk']['name'][$key],
                        'type' => $_FILES['foto_produk']['type'][$key],
                        'tmp_name' => $_FILES['foto_produk']['tmp_name'][$key],
                        'error' => $_FILES['foto_produk']['error'][$key],
                        'size' => $_FILES['foto_produk']['size'][$key]
                    ];
                    $cleanNamaProduk = slugify($nmProduk);
                    $cleanNamaUsaha = slugify($namaUsaha);
                    $tgl = date('Ymd-His');
                    $keyProduk = "websiteutama/umkm/fotoprodukumkm/" . $cleanNamaProduk . $cleanNamaUsaha . $tgl . rand(10,99) . ".webp";
                    $pathFotoProduk = uploadImageToMinio($singleFile, $keyProduk, $s3, $bucketName);
                }
                $stmtProduk->bind_param("isiss", $newUmkmId, $nmProduk, $hrgProduk, $descProduk, $pathFotoProduk);
                $stmtProduk->execute();
            }
        }
        header("Location: ?page=umkm&status=success_add");
        exit;
    }
}

// ACTION LOGIC (Hanya Perangkat Desa)
if ($currentUserLevel == 'perangkat_desa') {
    if (isset($_GET['action']) && $_GET['action'] == 'reorder_potensi') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        if ($data) { foreach ($data as $item) { $conn->query("UPDATE potensi_desa SET urutan = ".intval($item['urutan'])." WHERE id = ".intval($item['id'])); } echo json_encode(['status' => 'success']); } exit;
    }
    if (isset($_GET['action']) && $_GET['action'] == 'hapus_potensi') { 
        $conn->query("DELETE FROM potensi_desa WHERE id = ".intval($_GET['id'])); header("Location: ?page=potensi"); exit; 
    }
    if (isset($_GET['action']) && $_GET['action'] == 'acc_umkm') { 
        $conn->query("UPDATE umkm SET diacc = 1 WHERE id = ".intval($_GET['id'])); header("Location: ?page=umkm&status=success_acc"); exit; 
    }
    if (isset($_GET['action']) && $_GET['action'] == 'nonaktif_umkm') { 
        $conn->query("UPDATE umkm SET diacc = 0 WHERE id = ".intval($_GET['id'])); header("Location: ?page=umkm&status=success_deactivate"); exit; 
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Desa Karangasem</title>
    <link rel="icon" href="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/karangasem_admin.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Stack+Sans+Headline:wght@200..700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-logo-container">
            <img src="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/karangasem-color.png" alt="Logo" id="sidebar-logo" class="sidebar-logo">
        </div>
        
        <nav style="flex-grow: 1;">
            <?php if($currentUserLevel == 'perangkat_desa'): ?>
                <a href="?page=potensi" class="menu-item <?= $page == 'potensi' ? 'active' : '' ?>"><span class="material-symbols-rounded">fertile</span> Potensi Desa</a>
                <a href="?page=umkm" class="menu-item <?= $page == 'umkm' ? 'active' : '' ?>"><span class="material-symbols-rounded">shopping_cart</span> UMKM</a>
                <a href="?page=lapor" class="menu-item <?= $page == 'lapor' ? 'active' : '' ?>"><span class="material-symbols-rounded">report</span> Lapor Desa</a>
            
            <?php elseif($currentUserLevel == 'rw'): ?>
                <a href="?page=lapor" class="menu-item <?= $page == 'lapor' ? 'active' : '' ?>"><span class="material-symbols-rounded">report</span> Lapor Desa</a>
            
            <?php elseif($currentUserLevel == 'user'): ?>
                <a href="?page=umkm" class="menu-item <?= $page == 'umkm' ? 'active' : '' ?>"><span class="material-symbols-rounded">shopping_cart</span> UMKM Saya</a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-bottom-wrapper">
            
            <div class="sidebar-profile">
                <div class="profile-info">
                    <div class="profile-name"><?= htmlspecialchars($currentUserName) ?></div>
                    <div class="profile-role"><?= str_replace('_', ' ', $currentUserLevel) ?></div>
                </div>
                <a href="logout.php" class="btn-logout" title="Keluar">
                    <span class="material-symbols-rounded" style="font-size: 20px;">logout</span>
                </a>
            </div>

            <div class="sidebar-divider"></div>

            <div class="sidebar-footer">
                <div class="theme-wrapper">
                    <span style="font-size: 0.85rem; font-weight: 500; color: rgba(255,255,255,0.8);">Mode Tampilan</span>
                    <button id="theme-toggle" class="theme-toggle-btn" title="Ganti Mode"><span class="material-symbols-rounded" id="theme-icon" style="font-size: 18px;">dark_mode</span></button>
                </div>
            </div>

        </div>
    </div>

    <div class="main-content">
        <?php if (isset($_GET['status'])): ?>
            <div id="status-message" data-status="<?= $_GET['status'] ?>" style="display:none;"></div>
        <?php endif; ?>

        <?php if ($page == 'potensi' && $currentUserLevel == 'perangkat_desa'): ?>
            <h1>Kelola Potensi Desa</h1>
            
            <div class="card">
                <div class="card-header" style="border-bottom:none; padding-bottom:0; margin-bottom:15px; display: flex; justify-content: space-between; align-items: center;">
                    <h3><?= $editMode ? 'Edit Data Potensi' : 'Tambah Potensi Baru' ?></h3>
                    <?php if ($editMode): ?>
                        <a href="?page=potensi" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.8rem;">
                            <span class="material-symbols-rounded" style="font-size:16px;">close</span> Batal Edit
                        </a>
                    <?php endif; ?>
                </div>
                
                <form id="form-potensi" method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="id_potensi" value="<?= $editMode ? ($editData['id'] ?? '') : '' ?>">
                    <input type="hidden" name="foto_lama" value="<?= $editMode ? ($editData['path_foto_potensi'] ?? '') : '' ?>">
                    <div class="form-row">
                        <div class="flex-grow-2"> 
                            <label>Nama Potensi <span class="required-mark">*</span></label>
                            <input type="text" name="nama" id="input-nama" class="form-control" placeholder="Contoh: Sendang Bulus" required value="<?= $editMode ? htmlspecialchars($editData['nama_potensi'] ?? '') : '' ?>">
                        </div>
                        <div class="flex-grow-1"> 
                            <label>Jenis <span class="required-mark">*</span></label>
                            <select name="jenis" id="select-jenis" class="form-control" required>
                                <option value="" disabled <?= !$editMode ? 'selected' : '' ?>>-- Pilih --</option>
                                <option value="tempat" <?= ($editMode && ($editData['jenis_potensi'] ?? '') == 'tempat') ? 'selected' : '' ?>>Tempat</option>
                                <option value="budaya" <?= ($editMode && ($editData['jenis_potensi'] ?? '') == 'budaya') ? 'selected' : '' ?>>Budaya</option>
                            </select>
                        </div>
                    </div>
                    <div style="margin-bottom: 25px;">
                        <label>Deskripsi Singkat <span class="required-mark">*</span></label>
                        <textarea name="deskripsi" class="form-control" placeholder="Jelaskan potensi..." rows="3" required><?= $editMode ? htmlspecialchars($editData['deskripsi_potensi'] ?? '') : '' ?></textarea>
                    </div>
                    <div id="container-tempat" class="<?= ($editMode && ($editData['jenis_potensi'] ?? '') == 'tempat') ? '' : 'd-none' ?>" style="margin-bottom: 25px;">
                        <label>Lokasi (Klik pada peta) <span class="required-mark">*</span></label>
                        <div id="map-container" style="height: 350px; width: 100%;"></div>
                        <input type="hidden" name="latitude" id="input-lat" value="<?= $editMode ? ($editData['latitude_potensi'] ?? '') : '' ?>">
                        <input type="hidden" name="longitude" id="input-lng" value="<?= $editMode ? ($editData['longitude_potensi'] ?? '') : '' ?>">
                    </div>
                    <div id="container-budaya" class="<?= ($editMode && ($editData['jenis_potensi'] ?? '') == 'budaya') ? '' : 'd-none' ?>" style="margin-bottom: 25px;">
                        <label>Link Video/Website <span class="required-mark">*</span></label>
                        <input type="url" name="link_website" id="input-link" class="form-control" placeholder="https://..." value="<?= $editMode ? htmlspecialchars($editData['link_potensi'] ?? '') : '' ?>">
                    </div>
                    <div style="margin-bottom: 30px;">
                        <label>Foto Utama <?= $editMode ? '(Biarkan kosong jika tidak ingin mengubah)' : '<span class="required-mark">*</span>' ?></label>
                        <?php if ($editMode && !empty($editData['path_foto_potensi'])): ?>
                            <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                                <img src="<?= htmlspecialchars($editData['path_foto_potensi']) ?>" style="height: 60px; border-radius: 8px;">
                                <span style="font-size: 0.8rem; color: var(--text-muted);">Foto saat ini</span>
                            </div>
                        <?php endif; ?>
                        <div class="upload-area" id="upload-area">
                            <input type="file" name="foto" id="input-foto" accept="image/jpeg, image/png, image/webp" <?= $editMode ? '' : 'required' ?>>
                            <div class="upload-icon"><span class="material-symbols-rounded" style="font-size: 60px;">cloud_upload</span></div>
                            <div class="upload-text" id="upload-text"><strong>Klik untuk upload</strong> atau drag & drop foto di sini</div>
                        </div>
                    </div>
                    <button type="submit" name="simpan_potensi" class="btn btn-primary" style="width: 100%; padding: 18px;">
                        <span class="material-symbols-rounded">save</span> <?= $editMode ? 'Simpan Perubahan' : 'Simpan Data Potensi' ?>
                    </button>
                </form>
            </div>

            <div class="table-container">
                <table id="table-potensi">
                    <thead><tr><th width="5%">#</th><th>Nama</th><th>Jenis</th><th>Info</th><th>Foto</th><th>Aksi</th></tr></thead>
                    <tbody id="sortable-list">
                        <?php $resPotensi = $conn->query("SELECT * FROM potensi_desa ORDER BY urutan ASC"); while ($row = $resPotensi->fetch_assoc()): ?>
                        <tr class="draggable-row" draggable="true" data-id="<?= $row['id'] ?>">
                            <td class="drag-handle" style="text-align:center; cursor:grab;"><span class="material-symbols-rounded" style="color:var(--text-muted);">drag_indicator</span></td>
                            <td><?= htmlspecialchars($row['nama_potensi']) ?></td>
                            <td><span class="badge"><?= htmlspecialchars($row['jenis_potensi']) ?></span></td>
                            <td><?= $row['jenis_potensi'] == 'tempat' ? '<small>Lat: '.substr($row['latitude_potensi'],0,6).'<br>Lng: '.substr($row['longitude_potensi'],0,6).'</small>' : '<a href="'.$row['link_potensi'].'" target="_blank">Link</a>' ?></td>
                            <td><?php if($row['path_foto_potensi']): ?><a href="<?= htmlspecialchars($row['path_foto_potensi']) ?>" target="_blank"><img src="<?= htmlspecialchars($row['path_foto_potensi']) ?>" style="width: 80px; height: 60px; object-fit: cover; border-radius: 8px;"></a><?php else: ?> - <?php endif; ?></td>
                            <td>
                                <a href="?page=potensi&action=edit_potensi&id=<?= $row['id'] ?>" class="btn btn-warning btn-icon-only" title="Edit"><span class="material-symbols-rounded">edit</span></a>
                                <a href="?page=potensi&action=hapus_potensi&id=<?= $row['id'] ?>" class="btn btn-danger btn-icon-only btn-delete" data-confirm="Hapus?"><span class="material-symbols-rounded">delete</span></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($page == 'umkm' && ($currentUserLevel == 'user' || $currentUserLevel == 'perangkat_desa')): ?>
            <h1>Kelola UMKM</h1>

            <?php if (isset($_GET['action']) && $_GET['action'] == 'tambah_umkm'): ?>
                <div class="card" style="margin-bottom: 40px;">
                    <div class="card-header" style="margin-bottom:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <h3>Tambah UMKM Baru</h3>
                            <a href="?page=umkm" class="btn btn-secondary"><span class="material-symbols-rounded">close</span> Batal</a>
                        </div>
                    </div>
                    <form id="form-umkm" method="POST" enctype="multipart/form-data" novalidate>
                        <h4>A. Informasi Usaha</h4>
                        <div class="form-row">
                            <div class="flex-grow-2">
                                <label>Nama Usaha <span class="required-mark">*</span></label>
                                <input type="text" name="nama_usaha" class="form-control" required placeholder="Contoh: Keripik Singkong Barokah">
                            </div>
                            <div class="flex-grow-1">
                                <label>Kategori <span class="required-mark">*</span></label>
                                <select name="kategori" class="form-control" required>
                                    <option value="warung">Warung</option>
                                    <option value="pedagangkakilima">Pedagang Kaki Lima</option>
                                    <option value="pengrajin">Pengrajin</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="flex-grow-1">
                                <label>Nama Pemilik <span class="required-mark">*</span></label>
                                <input type="text" name="nama_pemilik" class="form-control" required placeholder="Nama Lengkap">
                            </div>
                            <div class="flex-grow-1">
                                <label>Kontak HP (Utama) <span class="required-mark">*</span></label>
                                <input type="text" name="kontak" class="form-control" required placeholder="08xxxxx">
                            </div>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label>Deskripsi Usaha</label>
                            <textarea name="deskripsi_usaha" class="form-control" rows="3" placeholder="Jelaskan tentang usaha ini..."></textarea>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label>Alamat Lengkap <span class="required-mark">*</span></label>
                            <textarea name="alamat" class="form-control" rows="2" required placeholder="Jalan, RT/RW, Dusun..."></textarea>
                        </div>
                        <div style="margin-bottom: 25px;">
                            <label>Lokasi Usaha (Klik pada peta) <span class="required-mark">*</span></label>
                            <div id="map-container" style="height: 300px; width: 100%;"></div>
                            <input type="hidden" name="latitude" id="input-lat">
                            <input type="hidden" name="longitude" id="input-lng">
                        </div>
                        <div class="form-row">
                            <div style="flex:1;">
                                <label>Foto Usaha<span class="required-mark">*</span></label>
                                <div class="upload-area" id="upload-area-usaha">
                                    <input type="file" name="foto_usaha" id="input-foto-usaha" accept="image/*" required>
                                    <div class="upload-icon"><span class="material-symbols-rounded" style="font-size: 60px;">cloud_upload</span></div>
                                    <div class="upload-text" id="upload-text-usaha"><strong>Klik untuk upload</strong> atau drag & drop foto usaha di sini</div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:10px; padding-top:25px;">
                                <input type="checkbox" name="qris" id="chk-qris" style="width:20px; height:20px;">
                                <label for="chk-qris" style="margin:0; cursor:pointer;">Mendukung QRIS?</label>
                            </div>
                        </div>
                        <h4 style="margin-top:30px;">B. Sosial Media</h4>
                        <div style="background: var(--input-bg); padding:20px; border-radius:12px; margin-bottom:20px;">
                            <div style="margin-bottom:15px;">
                                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                                    <input type="checkbox" name="punya_wa" id="chk-wa" style="width:18px; height:18px;" onchange="toggleSosmed('wa')">
                                    <label for="chk-wa" style="margin:0;">Punya WhatsApp?</label>
                                </div>
                                <div id="box-wa" style="display:none; margin-left:28px;">
                                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                                        <input type="checkbox" name="wa_sama" id="chk-wa-sama" checked onchange="toggleWaSama()">
                                        <label for="chk-wa-sama" style="margin:0; font-weight:normal;">Nomor sama dengan kontak utama?</label>
                                    </div>
                                    <input type="text" name="wa_beda" id="inp-wa-beda" class="form-control" placeholder="Masukkan nomor WA khusus (jika beda)" style="display:none;">
                                </div>
                            </div>
                            <div style="margin-bottom:15px;">
                                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                                    <input type="checkbox" name="punya_ig" id="chk-ig" style="width:18px; height:18px;" onchange="toggleSosmed('ig')">
                                    <label for="chk-ig" style="margin:0;">Punya Instagram?</label>
                                </div>
                                <div id="box-ig" style="display:none; margin-left:28px;">
                                    <input type="text" name="user_ig" class="form-control" placeholder="Username IG (tanpa @)">
                                </div>
                            </div>
                            <div>
                                <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                                    <input type="checkbox" name="punya_fb" id="chk-fb" style="width:18px; height:18px;" onchange="toggleSosmed('fb')">
                                    <label for="chk-fb" style="margin:0;">Punya Facebook?</label>
                                </div>
                                <div id="box-fb" style="display:none; margin-left:28px;">
                                    <input type="text" name="link_fb" class="form-control" placeholder="Link Profil Facebook Lengkap">
                                </div>
                            </div>
                        </div>
                        <h4 style="margin-top:30px;">C. Daftar Produk</h4>
                        <div id="products-wrapper">
                            <div class="product-item" style="border: 1px dashed var(--accent-color); padding:20px; border-radius:12px; margin-bottom:20px;">
                                <h5 style="margin-top:0; color:var(--accent-color);">Produk #1</h5>
                                <div class="form-row">
                                    <div class="flex-grow-2">
                                        <label>Nama Produk <span class="required-mark">*</span></label>
                                        <input type="text" name="nama_produk[]" class="form-control" required placeholder="Contoh: Keripik Rasa Balado">
                                    </div>
                                    <div class="flex-grow-1">
                                        <label>Harga (Rp) <span class="required-mark">*</span></label>
                                        <input type="number" name="harga_produk[]" class="form-control" required placeholder="15000">
                                    </div>
                                </div>
                                <div style="margin-bottom: 20px;">
                                    <label>Deskripsi Produk</label>
                                    <textarea name="deskripsi_produk[]" class="form-control" rows="2"></textarea>
                                </div>
                                <div>
                                    <label>Foto Produk <span class="required-mark">*</span></label>
                                    <div class="upload-area" id="upload-area-produk-0">
                                        <input type="file" name="foto_produk[]" id="input-foto-produk-0" accept="image/*" required>
                                        <div class="upload-icon"><span class="material-symbols-rounded" style="font-size: 60px;">cloud_upload</span></div>
                                        <div class="upload-text" id="upload-text-produk-0"><strong>Klik untuk upload</strong> atau drag & drop foto produk di sini</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="text-align:center; margin-bottom:30px;">
                            <button type="button" class="btn btn-secondary" onclick="addProductField()">
                                <span class="material-symbols-rounded">add_circle</span> Tambah Produk Lain
                            </button>
                        </div>
                        <button type="submit" name="simpan_umkm" class="btn btn-primary" style="width: 100%; padding: 18px;">
                            <span class="material-symbols-rounded">save</span> Simpan Data UMKM & Produk
                        </button>
                    </form>
                    <script>
                        // SCRIPT SAMA DENGAN YANG ANDA BERIKAN
                        function toggleSosmed(type) { const chk = document.getElementById('chk-' + type); const box = document.getElementById('box-' + type); box.style.display = chk.checked ? 'block' : 'none'; }
                        function toggleWaSama() { const chk = document.getElementById('chk-wa-sama'); const inp = document.getElementById('inp-wa-beda'); inp.style.display = chk.checked ? 'none' : 'block'; if(!chk.checked) inp.focus(); }
                        function setupUploadArea(areaId, inputId, textId) { const area = document.getElementById(areaId); const input = document.getElementById(inputId); const text = document.getElementById(textId); if(!area || !input) return; ['dragenter', 'dragover'].forEach(eventName => { area.addEventListener(eventName, (e) => { e.preventDefault(); area.classList.add('dragover'); }); }); ['dragleave', 'drop'].forEach(eventName => { area.addEventListener(eventName, (e) => { e.preventDefault(); area.classList.remove('dragover'); }); }); area.addEventListener('drop', (e) => { const files = e.dataTransfer.files; if (files.length > 0) { input.files = files; text.innerHTML = `File Terpilih: <strong>${files[0].name}</strong>`; } }); input.addEventListener('change', function() { if (this.files.length > 0) { text.innerHTML = `File Terpilih: <strong>${this.files[0].name}</strong>`; } }); }
                        let productCount = 1;
                        function addProductField() { const wrapper = document.getElementById('products-wrapper'); const newIndex = productCount; const div = document.createElement('div'); div.className = 'product-item'; div.style.cssText = 'border: 1px dashed var(--accent-color); padding:20px; border-radius:12px; margin-bottom:20px; position:relative;'; div.innerHTML = `<button type="button" onclick="this.parentElement.remove()" style="position:absolute; top:10px; right:10px; background:none; border:none; color:#e74c3c; cursor:pointer;"><span class="material-symbols-rounded">delete</span></button><h5 style="margin-top:0; color:var(--accent-color);">Produk #${newIndex + 1}</h5><div class="form-row"><div class="flex-grow-2"><label>Nama Produk <span class="required-mark">*</span></label><input type="text" name="nama_produk[]" class="form-control" required placeholder="Contoh: Produk Lain"></div><div class="flex-grow-1"><label>Harga (Rp) <span class="required-mark">*</span></label><input type="number" name="harga_produk[]" class="form-control" required placeholder="15000"></div></div><div style="margin-bottom: 20px;"><label>Deskripsi Produk</label><textarea name="deskripsi_produk[]" class="form-control" rows="2"></textarea></div><div><label>Foto Produk (MinIO) <span class="required-mark">*</span></label><div class="upload-area" id="upload-area-produk-${newIndex}"><input type="file" name="foto_produk[]" id="input-foto-produk-${newIndex}" accept="image/*" required><div class="upload-icon"><span class="material-symbols-rounded" style="font-size: 60px;">cloud_upload</span></div><div class="upload-text" id="upload-text-produk-${newIndex}"><strong>Klik untuk upload</strong> atau drag & drop foto produk di sini</div></div></div>`; wrapper.appendChild(div); setupUploadArea(`upload-area-produk-${newIndex}`, `input-foto-produk-${newIndex}`, `upload-text-produk-${newIndex}`); productCount++; }
                        document.addEventListener("DOMContentLoaded", function() { setupUploadArea('upload-area-usaha', 'input-foto-usaha', 'upload-text-usaha'); setupUploadArea('upload-area-produk-0', 'input-foto-produk-0', 'upload-text-produk-0'); });
                    </script>
                </div>
            <?php endif; ?>
            
            <?php if (!isset($_GET['action']) || $_GET['action'] != 'tambah_umkm'): ?>
                <div style="margin-bottom: 20px; text-align: right;">
                    <a href="?page=umkm&action=tambah_umkm" class="btn btn-primary">
                        <span class="material-symbols-rounded">add_business</span> Tambah UMKM Baru
                    </a>
                </div>
            <?php endif; ?>

            <?php if($currentUserLevel == 'perangkat_desa'): ?>
                <div class="card card-warning">
                    <div class="card-header"><h3><span class="material-symbols-rounded">pending</span> Menunggu Persetujuan</h3></div>
                    <div class="table-container">
                        <table>
                            <thead><tr><th>Nama</th><th>Pemilik</th><th>Aksi</th></tr></thead>
                            <tbody>
                            <?php $resAcc = $conn->query("SELECT * FROM umkm WHERE diacc = 0 ORDER BY created_at DESC");
                            if ($resAcc->num_rows > 0): while ($row = $resAcc->fetch_assoc()): ?>
                                <tr><td><?= $row['nama_usaha'] ?></td><td><?= $row['nama_pemilik_usaha'] ?></td><td><a href="?page=umkm&action=acc_umkm&id=<?= $row['id'] ?>" class="btn btn-success btn-acc">Terima</a></td></tr>
                            <?php endwhile; else: ?> <tr><td colspan="3" class="text-center">Kosong</td></tr> <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($currentUserLevel == 'perangkat_desa'): ?>
                <h3>Daftar UMKM Aktif</h3>
                <div class="table-container" style="margin-bottom: 40px;">
                    <table>
                        <thead>
                            <tr><th>Nama Usaha</th><th>Pemilik</th><th>Status</th><th style="width: 100px;">Aksi</th></tr>
                        </thead>
                        <tbody>
                        <?php $resAktif = $conn->query("SELECT * FROM umkm WHERE diacc = 1"); while($row=$resAktif->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_usaha']) ?></td>
                            <td><?= htmlspecialchars($row['nama_pemilik_usaha']) ?></td>
                            <td><span class="text-success">Aktif</span></td>
                            <td>
                                <a href="?page=umkm&action=nonaktif_umkm&id=<?= $row['id'] ?>" class="btn btn-danger btn-icon-only btn-nonaktif" title="Non-Aktifkan">
                                    <span class="material-symbols-rounded">block</span>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <h3>Data Lengkap (UMKM & Produk)</h3>
            <div class="card">
                <div class="table-container">
                    <table style="font-size: 0.9rem;">
                        <thead>
                            <tr>
                                <th style="min-width:150px;">Nama Usaha</th>
                                <th style="min-width:120px;">Pemilik</th>
                                <th style="min-width:100px;">Kontak</th>
                                <th style="min-width:200px;">Alamat</th>
                                <th>Foto Usaha</th>
                                <th>QRIS</th>
                                <th>Sosmed</th>
                                <th style="min-width:150px; border-left: 2px solid var(--card-border);">Produk</th>
                                <th style="min-width:100px;">Harga</th>
                                <th style="min-width:200px;">Deskripsi Produk</th>
                                <th>Foto Produk</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // --- LOGIKA FILTER USER ---
                            $whereClause = "";
                            if ($currentUserLevel == 'user') {
                                // User hanya lihat datanya sendiri
                                $whereClause = "WHERE u.id_user = $currentUserId";
                            }
                            
                            $sqlFull = "SELECT u.*, 
                                            p.id as id_produk, p.nama_produk, p.harga_produk, p.deskripsi_produk, p.path_foto_produk 
                                        FROM umkm u 
                                        LEFT JOIN umkmproduk p ON u.id = p.umkm_id 
                                        $whereClause
                                        ORDER BY u.nama_usaha ASC";
                            $resFull = $conn->query($sqlFull);
                            
                            $umkmData = [];
                            while($row = $resFull->fetch_assoc()) {
                                $id = $row['id'];
                                $umkmData[$id]['info'] = $row; 
                                if ($row['id_produk']) {
                                    $umkmData[$id]['products'][] = $row;
                                }
                            }

                            if (empty($umkmData)) {
                                echo "<tr><td colspan='11' class='text-center'>Belum ada data UMKM.</td></tr>";
                            } else {
                                foreach ($umkmData as $umkm):
                                    $products = $umkm['products'] ?? [];
                                    $count = count($products);
                                    $rowspan = $count > 0 ? $count : 1;
                                    $info = $umkm['info']; 
                                ?>
                                <tr>
                                    <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);"><strong><?= htmlspecialchars($info['nama_usaha']) ?></strong>
                                    <?php if($info['diacc'] == 0): ?><br><span class="badge badge-danger" style="font-size:0.7rem; margin-top:5px;">Pending</span><?php endif; ?>
                                    </td>
                                    <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);"><?= htmlspecialchars($info['nama_pemilik_usaha']) ?></td>
                                    <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);"><?= htmlspecialchars($info['kontak_usaha']) ?></td>
                                    <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);"><?= htmlspecialchars($info['alamat_usaha']) ?></td>
                                    <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);">
                                        <?php if (!empty($info['path_foto_usaha'])): ?>
                                            <a href="<?= htmlspecialchars($info['path_foto_usaha']) ?>" target="_blank"><img src="<?= htmlspecialchars($info['path_foto_usaha']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;"></a>
                                        <?php else: ?> <span class="text-muted">-</span> <?php endif; ?>
                                    </td>
                                    <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);">
                                        <?php if($info['qris']): ?><span class="badge badge-success">Yes</span><?php else: ?><span class="badge badge-danger">No</span><?php endif; ?>
                                    </td>
                                    <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);">
                                        <?php 
                                            $sosmed = [];
                                            if($info['punya_whatsapp']) $sosmed[] = "WA";
                                            if($info['punya_instagram']) $sosmed[] = "IG";
                                            if($info['punya_facebook']) $sosmed[] = "FB";
                                            echo implode(", ", $sosmed);
                                        ?>
                                    </td>
                                    <td style="border-left: 2px solid var(--card-border);"><?= $count > 0 ? htmlspecialchars($products[0]['nama_produk']) : '<em class="text-muted">Belum ada produk</em>' ?></td>
                                    <td><?= $count > 0 ? 'Rp '.number_format($products[0]['harga_produk'],0,',','.') : '-' ?></td>
                                    <td><?= $count > 0 ? htmlspecialchars(substr($products[0]['deskripsi_produk'], 0, 50)).'...' : '-' ?></td>
                                    <td><?php if($count > 0 && !empty($products[0]['path_foto_produk'])): ?><a href="<?= htmlspecialchars($products[0]['path_foto_produk']) ?>" target="_blank"><img src="<?= htmlspecialchars($products[0]['path_foto_produk']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;"></a><?php else: ?> - <?php endif; ?></td>
                                </tr>
                                <?php if ($count > 1): for($i = 1; $i < $count; $i++): $p = $products[$i]; ?>
                                <tr>
                                    <td style="border-left: 2px solid var(--card-border);"><?= htmlspecialchars($p['nama_produk']) ?></td>
                                    <td>Rp <?= number_format($p['harga_produk'],0,',','.') ?></td>
                                    <td><?= htmlspecialchars(substr($p['deskripsi_produk'], 0, 50)).'...' ?></td>
                                    <td><?php if(!empty($p['path_foto_produk'])): ?><a href="<?= htmlspecialchars($p['path_foto_produk']) ?>" target="_blank"><img src="<?= htmlspecialchars($p['path_foto_produk']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;"></a><?php else: ?> - <?php endif; ?></td>
                                </tr>
                                <?php endfor; endif; ?>
                                <tr><td colspan="11" style="padding:0; border-bottom: 2px solid var(--card-border);"></td></tr>
                                <?php endforeach; 
                            } // End else ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($page == 'lapor' && ($currentUserLevel == 'rw' || $currentUserLevel == 'perangkat_desa')): ?>
            <h1>Laporan Warga</h1>
            <div class="card">
                <div class="table-container">
                    <table style="font-size: 0.9rem;">
                        <thead>
                            <tr><th style="min-width:100px;">Tanggal</th><th style="min-width:120px;">Pelapor</th><th style="min-width:150px;">Kontak</th><th style="min-width:150px;">Alamat</th><th style="min-width:200px;">Pesan</th><th>Lokasi</th><th>Foto</th></tr>
                        </thead>
                        <tbody>
                        <?php 
                        $resLapor = $conn->query("SELECT * FROM laporandesa ORDER BY created_at DESC"); 
                        while($row=$resLapor->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($row['created_at'])) ?><br><small><?= date('H:i', strtotime($row['created_at'])) ?></small></td>
                            <td><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                            <td>
                                <?php if($row['email']): ?><div style="margin-bottom:4px;"><span class="material-symbols-rounded" style="font-size:14px; vertical-align:middle;">mail</span> <?= htmlspecialchars($row['email']) ?></div><?php endif; ?>
                                <div><span class="material-symbols-rounded" style="font-size:14px; vertical-align:middle;">call</span> <?= htmlspecialchars($row['nomor_telepon']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($row['alamat']) ?><br><span class="badge" style="margin-top:5px; display:inline-block;"><?= htmlspecialchars($row['rw']) ?></span></td>
                            <td><div style="max-width: 250px; white-space: normal;"><?= htmlspecialchars($row['pesan_keluhan']) ?></div></td>
                            <td class="text-center"><?php if(!empty($row['latitude']) && !empty($row['longitude'])): ?><a href="https://www.google.com/maps?daddr=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>" target="_blank" class="btn btn-success btn-icon-only" title="Lihat Lokasi"><span class="material-symbols-rounded">assistant_direction</span></a><?php else: ?><span style="color:#ccc;">-</span><?php endif; ?></td>
                            <td class="text-center"><?php if(!empty($row['path_keluhan_foto'])): ?><a href="<?= htmlspecialchars($row['path_keluhan_foto']) ?>" target="_blank" class="btn btn-primary btn-icon-only"><span class="material-symbols-rounded">image</span></a><?php else: ?><span style="color:#ccc;">-</span><?php endif; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="custom-popup" class="popup-overlay">
        <div class="popup-box">
            <div class="popup-icon" id="popup-icon-container"></div>
            <h3 class="popup-title" id="popup-title">Judul</h3>
            <p class="popup-message" id="popup-message">Pesan notifikasi.</p>
            <div id="popup-buttons" style="display: flex; justify-content: center; gap: 10px;"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="script.js"></script>
</body>
</html>