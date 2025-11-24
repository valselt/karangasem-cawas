<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lapor Desa</title>
    <link rel="icon" href="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/karangasem-color.png" type="image/png">
    <link rel="stylesheet" href="lapordesa.css?v=<?php echo time(); ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=BBH+Sans+Bogle&family=Stack+Sans+Headline:wght@200..700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0"
    />
    <script src="lapordesa.js?v=<?php echo time(); ?>"></script>
    <script src="../script.js?v=<?php echo time(); ?>"></script>

  </head>
  <body>

    <?php include 'navbar.php'; ?>

    <div class="container-lapor">
        <div class="header-lapor">
            <img src="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/lapordesa.png" alt="logo" />
        </div>
      <div class="section-1-lapor">
        <div class="section-1-1-lapor">
          <span class="text-title">Sistem Lapor Desa</span>
          <a class="other-button">
            <span class="material-symbols-rounded">content_paste_search</span>
            <p>Cek Laporan Desa</p>
          </a>

        </div>
        
        <p>oleh GIAT 13 UNNES</p>

        <div id="last-ticket-display" class="last-ticket-box">
            <span>Laporan Terakhir:</span>
            <strong id="ticket-number-text">#-------</strong>
            
            <button type="button" class="btn-copy-ticket" id="copy-ticket-btn" title="Salin Tiket">
                <span class="material-symbols-rounded">content_copy</span>
            </button>
        </div>
      </div>
      <div class="section-2-lapor">
        <form class="form-laporan" action="proses-lapordesa.php" method="POST" enctype="multipart/form-data">
          <div class="info-lapor">
            <input
              type="text"
              id="nama_lengkap"
              name="nama_user"
              required
              placeholder=" "
            />
            <label for="nama_lengkap">Nama Lengkap</label>
          </div>

          <div class="info-lapor">
            <input type="email" id="email" name="email_user" placeholder=" " />
            <label for="email">Email</label>
          </div>

          <div class="info-lapor">
            <input
              type="tel"
              id="nomor"
              name="nomor_user"
              required
              placeholder=" "
            />
            <label for="nomor">Nomor Telepon/WhatsApp</label>
          </div>

          <div class="info-lapor">
            <input
              type="text"
              id="alamat"
              name="alamat_user"
              required
              placeholder=" "
            />
            <label for="alamat">Alamat</label>
          </div>

          <div class="info-lapor">
            <select id="rw" name="lokasi_rw" required>
              <option value="" disabled selected></option>

              <option value="rw-1">1</option>
              <option value="rw-2">2</option>
              <option value="rw-3">3</option>
              <option value="rw-4">4</option>
              <option value="rw-5">5</option>
              <option value="rw-6">6</option>
              <option value="rw-7">7</option>
              <option value="rw-8">8</option>
              <option value="rw-9">9</option>
              <option value="rw-10">10</option>
            </select>
            <label for="rw">RW</label>
          </div>

          <div class="info-lapor">
            <textarea
              id="pesan"
              name="isi_pesan"
              rows="5"
              required
              placeholder=" "
            ></textarea>
            <label for="pesan">Keluhan</label>
          </div>

          <input type="file" id="bukti-foto" name="foto-laporan-user" accept="image/*"  hidden required />

          <div class="upload-gps-wrapper">

            <!-- UPLOAD FILE (DESKTOP) -->
            <label for="bukti-foto" class="file-drop-area">
              <span class="material-symbols-rounded file-drop-icon">
                upload_file
              </span>

              <!-- wrapper teks -->
              <span class="file-drop-text-desktop">
                Seret & lepas file Anda di sini, atau
                <strong>klik untuk memilih file</strong>
              </span>

              <!-- nama file DI DALAM kotak -->
              <span class="file-upload-filename"></span>

              <div class="upload-progress">
                <div class="upload-progress-bar"></div>
              </div>
            </label>

            <!-- UPLOAD FILE (MOBILE) -->
            <label for="bukti-foto" class="file-button-mobile">
              <span class="material-symbols-rounded">add_photo_alternate</span>
              <span class="file-button-text-mobile">Upload Gambar</span>

              <div class="upload-progress">
                <div class="upload-progress-bar"></div>
              </div>
            </label>

            <!-- GPS BUTTON -->
            <button type="button" id="ambil-gps" class="gps-btn">
              <span class="material-symbols-rounded">my_location</span>
            </button>

          </div>

          <input type="hidden" id="koordinat_gps" name="koordinat_gps">

          <div>
            <button type="submit" id="kirim-laporan">
              <span class="material-symbols-rounded">send</span>
              <p>Kirim Pesan</p>
            </button>
          </div>
        </form>
      </div>
    </div>

    <?php include '../footer.php'; ?>

    <!-- ================================
    POPUP SUKSES
    ================================ -->
    <div id="popup-overlay"></div>

    <div id="popup-success">
        <div class="popup-icon">
            <span class="material-symbols-rounded">mark_email_read</span>
        </div>

        <h2>Laporan Terkirim!</h2>

        <button id="popup-close-btn">Kembali</button>
    </div>

    <!-- ================================
     POPUP WARNING (VALIDASI FORM)
    ================================ -->
    <div id="popup-warning">
        <div class="popup-icon warning">
            <span class="material-symbols-rounded">warning</span>
        </div>

        <h2 id="warning-text">Anda Belum Mengisi ...</h2>

        <button id="popup-warning-btn">Kembali</button>
    </div>

    <div id="popup-ticket-input" class="popup-input-container">
        <h3 class="popup-title">Lacak Laporan Desa</h3>
        <p style="text-align:center; margin-bottom:1rem; color:#666;">Masukkan No. Tiket Anda (contoh: #A1B2C3)</p>
        
        <input type="text" id="input-ticket-field" class="input-ticket-box" placeholder="#XXXXXX">
        <button id="btn-submit-ticket" class="btn-check-ticket">CEK STATUS</button>
        <button id="btn-close-ticket" style="width:100%; margin-top:0.5rem; background:none; border:none; cursor:pointer; color:#999;">Batal</button>
    </div>

    <div id="popup-ticket-result" class="popup-input-container" style="max-width: 500px;">
        <h3 class="popup-title">Riwayat Laporan</h3>
        
        <div id="timeline-content" class="timeline-scroll-area">
             <p style="text-align:center;">Memuat data...</p>
        </div>

        <button id="btn-close-result" class="btn-check-ticket" style="margin-top:1rem;">Tutup</button>
    </div>


  </body>
</html>
