<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Desa Karangasem</title>
    <link rel="icon" href="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/karangasem-color.png" type="image/png">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>"/>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=BBH+Sans+Bogle&family=Stack+Sans+Headline:wght@200..700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script src="script.js"></script>
    <script src="peta.js"></script>
  </head>
  <body>

    <?php include 'navbar.php'; ?>

    <div class="container">
      <div class="section-1">
        <video autoplay muted loop playsinline class="video-background">
          <source src="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/vidbg.webm" type="video/mp4" />
        </video>
        <div class="shell-section-1">
          <div class="section-1-1">
            <span class="text-title">Welcome To </span>
            <span class="text-title">DESA KARANGASEM</span>
            <p>
              Desa Karangasem adalah sebuah desa di Kecamatan Cawas, Klaten, dengan luas ±193,56 ha dan penduduk sekitar ±3.100 jiwa; berakar dari nama yang berarti “pekarangan berisi pohon asem”
            </p>
          </div>
          <div class="section-1-2">
            <a class="button-landingpage" id="button-landingpage-1" href="potensidesa">
              <div class="liquidGlass-effect"></div>
              <div class="liquidGlass-tint"></div>
              <div class="liquidGlass-shine"></div>
              <div class="liquidGlass-text">
                <span class="material-symbols-rounded">fertile</span>
                <p>Lihat Potensi Desa</p>
              </div>
            </a>
            <a class="button-landingpage" id="button-landingpage-2" href="umkm">
              <div class="liquidGlass-effect"></div>
              <div class="liquidGlass-tint"></div>
              <div class="liquidGlass-shine"></div>
              <div class="liquidGlass-text">
                <span class="material-symbols-rounded">shopping_cart</span>
                <p>UMKM</p>
              </div>
            </a>
            <a class="button-landingpage" id="button-landingpage-3" href="lapordesa">
              <div class="liquidGlass-effect"></div>
              <div class="liquidGlass-tint"></div>
              <div class="liquidGlass-shine"></div>
              <div class="liquidGlass-text">
                <span class="material-symbols-rounded">report</span>
                <p>Lapor Desa</p>
              </div>
            </a>
          </div>
        </div>
      </div>
      <div class="section-3">
        <div class="section-3-1">
          <span class="text-title">Lokasi Desa</span>
          <div class="lokasidesa-button">
            <a href="https://maps.app.goo.gl/i865EGS7Qsh6FurHA" class="other-button lokasidesa" target="_blank" rel="noopener noreferrer">
              <div class="icon-maps">
                <img src="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/icon/googlemaps.png" alt="Google Maps">
              </div>
              <p>Buka di Google Maps</p>
            </a>
            <a href="https://maps.apple.com/place?address=Karangasem,%20Klaten,%20Central%20Java,%20Indonesia&auid=10098370620942775029&coordinate=-7.796315,110.699025&lsp=6489&name=Karangasem&map=explore" class="other-button lokasidesa" target="_blank" rel="noopener noreferrer">
              <div class="icon-maps">
                <img src="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/icon/applemaps.png" alt="Apple Maps">
              </div>
            
              <p>Buka di Apple Maps</p>
            </a>
          </div>
        </div>
        <div class="section-3-2">
          <div id="desaMap"></div>
          <div class="map-toggle-group">
            
            <div id="resetMap" class="toggle-map-btn">
                <div class="liquidGlass-effect"></div>
                <div class="liquidGlass-tint"></div>
                <div class="liquidGlass-shine"></div>
                <div class="liquidGlass-text">
                    <span class="material-symbols-rounded">zoom_in_map</span>
                </div>
            </div>

            <div id="toggleSatellite" class="toggle-map-btn">
                <div class="liquidGlass-effect"></div>
                <div class="liquidGlass-tint"></div>
                <div class="liquidGlass-shine"></div>
                <div class="liquidGlass-text">
                    <span class="material-symbols-rounded">public_off</span>
                </div>
            </div>
          </div>
      </div>
      <div class="section-3-3">
        <p>Desa Karangasem berbatasan dengan Desa Ngerangan dan Desa Jambakan di Sebelah Barat, Desa Nanggulan di Sebelah Utara, Desa Burikan di Sebelah Timur, dan berbatasan langsung dengan Provinsi Daerah Istimewa Yogyakarta, tepatnya Desa Tancep pada Sebelah Selatan. </p>
        <p>Peta yang digunakan adalah <a class="license-map" href="https://leafletjs.com/" target="_blank" rel="noopener noreferrer">Leaflet</a> dan <a class="license-map" href="https://www.arcgis.com/home/item.html?id=10df2279f9684e4a9f6a7f08febac2a9" target="_blank" rel="noopener noreferrer">ESRI World Imagery</a>
</p>

      </div>
    </div>

    <?php include 'footer.php'; ?>

    <?php include 'liquidglass.php'; ?>
  </body>
</html>
