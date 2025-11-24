<?php
// cek-laporan.php
require '../koneksi.php'; // Sesuaikan path koneksi Anda

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket = $_POST['ticket'] ?? '';

    // 1. Cek Laporan Utama
    $stmt = $conn->prepare("SELECT * FROM laporandesa WHERE ticket = ?");
    $stmt->bind_param("s", $ticket);
    $stmt->execute();
    $result = $stmt->get_result();
    $laporan = $result->fetch_assoc();

    if (!$laporan) {
        http_response_code(404);
        echo "Tiket tidak ditemukan.";
        exit;
    }

    $idLaporan = $laporan['id'];

    // 2. Ambil Riwayat Tanggapan (Join dengan Users untuk dapat nama penanggap)
    // Diurutkan DESC (Terbaru diatas)
    $stmt2 = $conn->prepare("
        SELECT r.*, u.nama_lengkap, u.level 
        FROM riwayat_tanggapan r 
        LEFT JOIN users u ON r.id_user = u.id 
        WHERE r.id_laporan = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt2->bind_param("i", $idLaporan);
    $stmt2->execute();
    $riwayat = $stmt2->get_result();

    // 3. Render HTML Timeline
    echo '<div class="timeline">';

    // A. Loop Riwayat Tanggapan (Terbaru)
    while ($row = $riwayat->fetch_assoc()) {
        $statusClass = 't-status-' . $row['status_laporan']; // mapping class css
        $tanggal = date('d M Y, H:i', strtotime($row['created_at']));
        $penanggap = $row['nama_lengkap'] ?? 'Sistem';
        
        // Tentukan Label Penanggap
        $badgeRole = '';
        if($row['level'] == 'rw') $badgeRole = '(Ketua RW)';
        if($row['level'] == 'perangkat_desa') $badgeRole = '(Admin Desa)';

        echo "
        <div class='timeline-item $statusClass'>
            <div class='timeline-header'>
                <span class='timeline-user'>$penanggap <small>$badgeRole</small></span>
                <span class='timeline-date'>$tanggal</span>
            </div>
            <div class='timeline-body'>
                {$row['isi_tanggapan']}
                <br>
                <small style='color:#666; margin-top:5px; display:block;'>Status diubah menjadi: <b>".ucfirst($row['status_laporan'])."</b></small>
            </div>
        </div>";
    }

    // B. Tampilkan Laporan Awal (Paling Bawah / Awal Mula)
    // Item terakhir (paling lama) adalah laporan warga itu sendiri
    $tanggalLapor = date('d M Y, H:i', strtotime($laporan['created_at']));
    echo "
        <div class='timeline-item'>
            <div class='timeline-header'>
                <span class='timeline-user'>{$laporan['nama']} (Pelapor)</span>
                <span class='timeline-date'>$tanggalLapor</span>
            </div>
            <div class='timeline-body'>
                <b>Isi Laporan:</b><br>
                {$laporan['pesan_keluhan']}
                <div style='margin-top:5px;'>
                   <span style='background:#eee; padding:2px 8px; border-radius:4px; font-size:0.8rem;'>Status Awal: Menunggu</span>
                </div>
            </div>
        </div>
    ";

    echo '</div>'; // End .timeline
}
?>