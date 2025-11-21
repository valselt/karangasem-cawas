<?php
include 'koneksi.php';
require 'vendor/autoload.php'; 

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// --- KONFIGURASI MINIO ---
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

// --- VARIABEL EDIT ---
$editMode = false;
$editData = null;

// 1. CEK MODE EDIT
if (isset($_GET['action']) && $_GET['action'] == 'edit_potensi' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $queryEdit = $conn->query("SELECT * FROM potensi_desa WHERE id = $id");
    if ($queryEdit->num_rows > 0) {
        $editMode = true;
        $editData = $queryEdit->fetch_assoc();
    }
}

// 2. LOGIC TAMBAH & UPDATE POTENSI
if (isset($_POST['simpan_potensi'])) { 
    $nama = $_POST['nama'];
    $jenis = $_POST['jenis'];
    $deskripsi = $_POST['deskripsi'];
    $lat = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
    $lng = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
    $link = !empty($_POST['link_website']) ? $_POST['link_website'] : null;
    
    $isUpdate = isset($_POST['id_potensi']) && !empty($_POST['id_potensi']);
    $fotoUrl = $isUpdate ? $_POST['foto_lama'] : null; 

    // Handle Upload Foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['foto']['tmp_name'];
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nama)));
        $fileName = "websiteutama/potensi_desa/" . $slug . "-" . time() . ".webp";
        
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
                $s3 = new S3Client($minioConfig);
                $s3->putObject([
                    'Bucket' => $bucketName, 'Key' => $fileName, 'SourceFile' => $tempWebp,
                    'ACL' => 'public-read', 'ContentType' => 'image/webp'
                ]);
                $fotoUrl = $minioConfig['endpoint'] . "/$bucketName/$fileName";
                unlink($tempWebp);
            } catch (AwsException $e) { die("Error Upload MinIO: " . $e->getMessage()); }
        }
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
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Logic Lainnya
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

$page = isset($_GET['page']) ? $_GET['page'] : 'potensi';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Desa Karangasem</title>
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
            <a href="?page=potensi" class="menu-item <?= $page == 'potensi' ? 'active' : '' ?>"><span class="material-symbols-rounded">fertile</span> Potensi Desa</a>
            <a href="?page=umkm" class="menu-item <?= $page == 'umkm' ? 'active' : '' ?>"><span class="material-symbols-rounded">shopping_cart</span> UMKM</a>
            <a href="?page=lapor" class="menu-item <?= $page == 'lapor' ? 'active' : '' ?>"><span class="material-symbols-rounded">report</span> Lapor Desa</a>
        </nav>
        <div class="sidebar-footer">
            <div class="theme-wrapper">
                <span style="font-size: 0.85rem; font-weight: 500; color: rgba(255,255,255,0.8);">Mode Tampilan</span>
                <button id="theme-toggle" class="theme-toggle-btn" title="Ganti Mode"><span class="material-symbols-rounded" id="theme-icon" style="font-size: 18px;">dark_mode</span></button>
            </div>
        </div>
    </div>

    <div class="main-content">
        <?php if (isset($_GET['status'])): ?>
            <div id="status-message" data-status="<?= $_GET['status'] ?>" style="display:none;"></div>
        <?php endif; ?>

        <?php if ($page == 'potensi'): ?>
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
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_potensi" value="<?= $editMode ? ($editData['id'] ?? '') : '' ?>">
                    <input type="hidden" name="foto_lama" value="<?= $editMode ? ($editData['path_foto_potensi'] ?? '') : '' ?>">

                    <div class="form-row">
                        <div class="flex-grow-2"> 
                            <label>Nama Potensi</label>
                            <input type="text" name="nama" id="input-nama" class="form-control" 
                                   placeholder="Contoh: Sendang Bulus" required
                                   value="<?= $editMode ? htmlspecialchars($editData['nama_potensi'] ?? '') : '' ?>">
                        </div>
                        <div class="flex-grow-1"> 
                            <label>Jenis</label>
                            <select name="jenis" id="select-jenis" class="form-control">
                                <option value="" disabled <?= !$editMode ? 'selected' : '' ?>>-- Pilih --</option>
                                <option value="tempat" <?= ($editMode && ($editData['jenis_potensi'] ?? '') == 'tempat') ? 'selected' : '' ?>>Tempat</option>
                                <option value="budaya" <?= ($editMode && ($editData['jenis_potensi'] ?? '') == 'budaya') ? 'selected' : '' ?>>Budaya</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label>Deskripsi Singkat</label>
                        <textarea name="deskripsi" class="form-control" placeholder="Jelaskan potensi..." rows="3"><?= $editMode ? htmlspecialchars($editData['deskripsi_potensi'] ?? '') : '' ?></textarea>
                    </div>

                    <div id="container-tempat" class="<?= ($editMode && ($editData['jenis_potensi'] ?? '') == 'tempat') ? '' : 'd-none' ?>" style="margin-bottom: 25px;">
                        <label>Lokasi (Klik pada peta)</label>
                        <div id="map-container" style="height: 350px; width: 100%;"></div>
                        <input type="hidden" name="latitude" id="input-lat" value="<?= $editMode ? ($editData['latitude_potensi'] ?? '') : '' ?>">
                        <input type="hidden" name="longitude" id="input-lng" value="<?= $editMode ? ($editData['longitude_potensi'] ?? '') : '' ?>">
                    </div>

                    <div id="container-budaya" class="<?= ($editMode && ($editData['jenis_potensi'] ?? '') == 'budaya') ? '' : 'd-none' ?>" style="margin-bottom: 25px;">
                        <label>Link Video/Website</label>
                        <input type="url" name="link_website" class="form-control" placeholder="https://..." 
                               value="<?= $editMode ? htmlspecialchars($editData['link_potensi'] ?? '') : '' ?>">
                    </div>

                    <div style="margin-bottom: 30px;">
                        <label>Foto Utama <?= $editMode ? '(Biarkan kosong jika tidak ingin mengubah)' : '' ?></label>
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
                            <td>
                                <?php if($row['path_foto_potensi']): ?>
                                    <a href="<?= htmlspecialchars($row['path_foto_potensi']) ?>" target="_blank">
                                        <img src="<?= htmlspecialchars($row['path_foto_potensi']) ?>" style="width: 80px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    </a>
                                <?php else: ?> - <?php endif; ?>
                            </td>
                            <td>
                                <a href="?page=potensi&action=edit_potensi&id=<?= $row['id'] ?>" class="btn btn-warning btn-icon-only" title="Edit"><span class="material-symbols-rounded">edit</span></a>
                                <a href="?page=potensi&action=hapus_potensi&id=<?= $row['id'] ?>" class="btn btn-danger btn-icon-only btn-delete" data-confirm="Hapus?"><span class="material-symbols-rounded">delete</span></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($page == 'umkm'): ?>
            <h1>Kelola UMKM</h1>
            
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

            <h3>Daftar UMKM Aktif</h3>
            <div class="table-container" style="margin-bottom: 40px;">
                <table>
                    <thead><tr><th>Nama Usaha</th><th>Pemilik</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php $resAktif = $conn->query("SELECT * FROM umkm WHERE diacc = 1"); while($row=$resAktif->fetch_assoc()): ?>
                    <tr><td><?= $row['nama_usaha'] ?></td><td><?= $row['nama_pemilik_usaha'] ?></td><td><span class="text-success">Aktif</span></td></tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

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
                            // LOGIC PENGELOMPOKAN DATA
                            $sqlFull = "SELECT u.*, 
                                               p.id as id_produk, p.nama_produk, p.harga_produk, p.deskripsi_produk, p.path_foto_produk 
                                        FROM umkm u 
                                        LEFT JOIN umkmproduk p ON u.id = p.umkm_id 
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

                            foreach ($umkmData as $umkm):
                                $products = $umkm['products'] ?? [];
                                $count = count($products);
                                $rowspan = $count > 0 ? $count : 1;
                                $info = $umkm['info']; 
                            ?>
                            
                            <tr>
                                <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);"><strong><?= htmlspecialchars($info['nama_usaha']) ?></strong></td>
                                <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);"><?= htmlspecialchars($info['nama_pemilik_usaha']) ?></td>
                                <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);"><?= htmlspecialchars($info['kontak_usaha']) ?></td>
                                <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);"><?= htmlspecialchars($info['alamat_usaha']) ?></td>

                                <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);">
                                    <?php if (!empty($info['path_foto_usaha'])): ?>
                                        <a href="<?= htmlspecialchars($info['path_foto_usaha']) ?>" target="_blank">
                                            <img src="<?= htmlspecialchars($info['path_foto_usaha']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);"><?= $info['qris'] ? '<span class="badge">Yes</span>' : 'No' ?></td>
                                <td rowspan="<?= $rowspan ?>" style="vertical-align:top; background-color:var(--bg-color);">
                                    <?php 
                                        $sosmed = [];
                                        if($info['punya_whatsapp']) $sosmed[] = "WA";
                                        if($info['punya_instagram']) $sosmed[] = "IG";
                                        if($info['punya_facebook']) $sosmed[] = "FB";
                                        echo implode(", ", $sosmed);
                                    ?>
                                </td>

                                <td style="border-left: 2px solid var(--card-border);">
                                    <?= $count > 0 ? htmlspecialchars($products[0]['nama_produk']) : '<em class="text-muted">Belum ada produk</em>' ?>
                                </td>
                                <td>
                                    <?= $count > 0 ? 'Rp '.number_format($products[0]['harga_produk'],0,',','.') : '-' ?>
                                </td>
                                <td>
                                    <?= $count > 0 ? htmlspecialchars(substr($products[0]['deskripsi_produk'], 0, 50)).'...' : '-' ?>
                                </td>
                                <td>
                                    <?php if($count > 0 && !empty($products[0]['path_foto_produk'])): ?>
                                        <a href="<?= htmlspecialchars($products[0]['path_foto_produk']) ?>" target="_blank">
                                            <img src="<?= htmlspecialchars($products[0]['path_foto_produk']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                                        </a>
                                    <?php else: ?> - <?php endif; ?>
                                </td>
                            </tr>

                            <?php 
                            if ($count > 1): 
                                for($i = 1; $i < $count; $i++): 
                                    $p = $products[$i];
                            ?>
                            <tr>
                                <td style="border-left: 2px solid var(--card-border);"><?= htmlspecialchars($p['nama_produk']) ?></td>
                                <td>Rp <?= number_format($p['harga_produk'],0,',','.') ?></td>
                                <td><?= htmlspecialchars(substr($p['deskripsi_produk'], 0, 50)).'...' ?></td>
                                <td>
                                    <?php if(!empty($p['path_foto_produk'])): ?>
                                        <a href="<?= htmlspecialchars($p['path_foto_produk']) ?>" target="_blank">
                                            <img src="<?= htmlspecialchars($p['path_foto_produk']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                                        </a>
                                    <?php else: ?> - <?php endif; ?>
                                </td>
                            </tr>
                            <?php endfor; endif; ?>

                            <tr><td colspan="10" style="padding:0; border-bottom: 2px solid var(--card-border);"></td></tr>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($page == 'lapor'): ?>
            <h1>Laporan Warga</h1>
            <div class="card">
                <div class="table-container">
                    <table style="font-size: 0.9rem;">
                        <thead>
                            <tr>
                                <th style="min-width:100px;">Tanggal</th>
                                <th style="min-width:120px;">Pelapor</th>
                                <th style="min-width:150px;">Kontak</th>
                                <th style="min-width:150px;">Alamat</th>
                                <th style="min-width:200px;">Pesan</th>
                                <th>Lokasi</th>
                                <th>Foto</th>
                            </tr>
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
                                <?php if($row['email']): ?>
                                    <div style="margin-bottom:4px;">
                                        <span class="material-symbols-rounded" style="font-size:14px; vertical-align:middle;">mail</span> 
                                        <?= htmlspecialchars($row['email']) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <span class="material-symbols-rounded" style="font-size:14px; vertical-align:middle;">call</span> 
                                    <?= htmlspecialchars($row['nomor_telepon']) ?>
                                </div>
                            </td>
                            
                            <td>
                                <?= htmlspecialchars($row['alamat']) ?>
                                <br>
                                <span class="badge" style="margin-top:5px; display:inline-block;"><?= htmlspecialchars($row['rw']) ?></span>
                            </td>
                            
                            <td><div style="max-width: 250px; white-space: normal;"><?= htmlspecialchars($row['pesan_keluhan']) ?></div></td>
                            
                            <td class="text-center">
                                <?php if(!empty($row['latitude']) && !empty($row['longitude'])): ?>
                                    <a href="https://www.google.com/maps?daddr=<?= $row['latitude'] ?>,<?= $row['longitude'] ?>" 
                                       target="_blank" 
                                       class="btn btn-success btn-icon-only" 
                                       title="Lihat Lokasi">
                                        <span class="material-symbols-rounded">assistant_direction</span>
                                    </a>
                                <?php else: ?>
                                    <span style="color:#ccc;">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="text-center">
                                <?php if(!empty($row['path_keluhan_foto'])): ?>
                                    <a href="<?= htmlspecialchars($row['path_keluhan_foto']) ?>" target="_blank" class="btn btn-primary btn-icon-only">
                                        <span class="material-symbols-rounded">image</span>
                                    </a>
                                <?php else: ?>
                                    <span style="color:#ccc;">-</span>
                                <?php endif; ?>
                            </td>
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