<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>UMKM Desa Karangasem</title>
    <link rel="icon" href="https://cdn.ivanaldorino.web.id/karangasem/websiteutama/karangasem-color.png" type="image/png">
    <link rel="stylesheet" href="umkm.css?v=<?php echo time(); ?>" />
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="peta-umkm.js?v=<?php echo time(); ?>"></script>
    <script src="umkm.js?v=<?php echo time(); ?>"></script>
    <script src="../script.js?v=<?php echo time(); ?>"></script>

  </head>
  <body>

    <?php include 'navbar.php'; ?>
    <div class="container-umkm">
        <div class="section-1-umkm">
            <span class="text-title-umkm">UMKM</span>
            <div class="other-button">
                <span class="material-symbols-rounded">add_location_alt</span>
                <p>Tambah Lokasi UMKM</p>
            </div>
        </div>

        <div class="umkm-search-wrapper">
            <div class="umkm-search">
                <span class="material-symbols-rounded search-icon">search</span>
                <input type="text" id="searchUmkm" placeholder="Cari UMKM...">
                <span class="material-symbols-rounded clear-icon" id="clearSearch">close</span>
            </div>
        </div>

        <div class="section-2-umkm">
            
            <div id="umkmMap">
                <div class="map-toggle-group umkm-toggle">
                    <div id="locateUserBtn" class="toggle-map-btn">
                        <div class="liquidGlass-effect"></div>
                        <div class="liquidGlass-tint"></div>
                        <div class="liquidGlass-shine"></div>
                        <div class="liquidGlass-text">
                            <span class="material-symbols-rounded">my_location</span>
                        </div>
                    </div>
                    <div id="resetMapUmkm" class="toggle-map-btn">
                        <div class="liquidGlass-effect"></div>
                        <div class="liquidGlass-tint"></div>
                        <div class="liquidGlass-shine"></div>
                        <div class="liquidGlass-text">
                            <span class="material-symbols-rounded">zoom_in_map</span>
                        </div>
                    </div>
                    <div id="toggleSatelliteUmkm" class="toggle-map-btn">
                        <div class="liquidGlass-effect"></div>
                        <div class="liquidGlass-tint"></div>
                        <div class="liquidGlass-shine"></div>
                        <div class="liquidGlass-text">
                            <span class="material-symbols-rounded">public_off</span>
                        </div>
                    </div>
                </div>
            </div>

            <div id="info-umkm" class="info-umkm">
                
                <div class="info-umkm-content-title">
                    <div class="info-umkm-title">
                        <span id="u-nama">— —</span>
                        <p id="u-alamat">— —</p>
                    </div>
                    
                    
                    <a id="u-direction" href="#" target="_blank" class="info-umkm-button button-umkm-header">
                        <span class="material-symbols-rounded">assistant_direction</span>
                    </a>
                </div>

                <div class="info-umkm-content-1">
                    <div id="u-kategori" class="info-umkm-card">
                        <span class="material-symbols-rounded">store</span>
                        <p>-</p>
                    </div>
                    
                    <div id="u-qris" class="info-umkm-card">
                        <p>QRIS</p>
                        <p>-</p>
                    </div>
                    
                    <a id="u-kontak" class="info-umkm-card">
                        <span class="material-symbols-rounded">call</span>
                        <p>-</p>
                    </a>
                    <a id="u-wa" class="info-umkm-card umkm-whatsapp" target="_blank" style="display:none;">
                        <img src="https://cdn.simpleicons.org/whatsapp/fff" />
                        <p>WhatsApp</p>
                    </a>

                    <a id="u-ig" class="info-umkm-card umkm-instagram" target="_blank" style="display:none;">
                        <img src="https://cdn.simpleicons.org/instagram/fff"/>
                        <p>Instagram</p>
                    </a>

                    <a id="u-fb" class="info-umkm-card umkm-facebook" target="_blank" style="display:none;">
                        <img src="https://cdn.simpleicons.org/facebook/207bf3"/>
                        <p>Facebook</p>
                    </a>
                </div>

                <div class="foto-umkm">
                    <img id="u-foto" src="" style="display:none; object-fit: cover; width: 100%; border-radius: 1rem;" />
                </div>

                <div id="u-produk" class="card-produk"></div>
                
            </div>
        </div>
        <div class="section-3-umkm">
            <p>Peta yang digunakan adalah <a class="license-map" href="https://leafletjs.com/" target="_blank" rel="noopener noreferrer">Leaflet</a> dan <a class="license-map" href="https://www.arcgis.com/home/item.html?id=10df2279f9684e4a9f6a7f08febac2a9" target="_blank" rel="noopener noreferrer">ESRI World Imagery</a></p>
        </div>
    </div>

    <?php include '../liquidglass.php'; ?>
    <?php include '../footer.php'; ?>
  </body>