document.addEventListener("DOMContentLoaded", () => {

  // =============================
  // 1. Inisialisasi Peta
  // =============================
  const map = L.map("desaMap", {
    center: [-7.685, 110.765], // kira-kira Cawas
    zoom: 14,
    zoomControl: false,
    maxZoom: 18
  });

  // =============================
  // 2. Basemap OSM (Default)
  // =============================
  const osm = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 18,
    attribution: "&copy; OpenStreetMap"
  }).addTo(map);

  // =============================
  // 3. Basemap ESRI World Imagery
  // =============================
  const esriSat = L.tileLayer(
    "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}", 
    {
      maxZoom: 18,
      attribution: "Tiles © Esri"
    }
  );

  let satelliteOn = false;

  // =============================
  // 4. Load GeoJSON batas desa
  // =============================
  fetch("data/geojson/karangasem.geojson")
    .then(res => res.json())
    .then(geo => {

      const geoLayer = L.geoJSON(geo, {
        style: {
          color: "#ffcc00",
          weight: 3,
          fillColor: "#ffcc0055",
          fillOpacity: 0.4
        }
      }).addTo(map);

      map.fitBounds(geoLayer.getBounds());
    });

    let karangasemBounds = null;

    fetch("data/geojson/karangasem.geojson")
      .then(res => res.json())
      .then(geo => {

        const geoLayer = L.geoJSON(geo, {
          style: {
            color: "#ffcc00",
            weight: 3,
            fillColor: "#ffcc0055",
            fillOpacity: 0.1
          }
        }).addTo(map);

        karangasemBounds = geoLayer.getBounds();
        map.fitBounds(karangasemBounds);
    });


  // =============================
  // 5. Toggle Button
  // =============================
  // ambil tombol dan ikon span
  const toggleBtn = document.getElementById("toggleSatellite");
  const toggleIcon = toggleBtn.querySelector(".material-symbols-rounded");

  // ambil grup tombol
  const toggleGroup = document.querySelector(".map-toggle-group");

  // EVENT TOGGLE SATELLITE
  toggleBtn.addEventListener("click", () => {
      satelliteOn = !satelliteOn;

      if (satelliteOn) {

          // switch to satellite
          if (map.hasLayer(osm)) map.removeLayer(osm);
          esriSat.addTo(map);

          toggleBtn.classList.add("active");
          toggleBtn.setAttribute("aria-pressed", "true");
          toggleBtn.querySelector(".material-symbols-rounded").textContent = "public";

          // Reset Map jadi putih
          toggleGroup.classList.add("satellite-on");

      } else {

          // switch back to OSM
          if (map.hasLayer(esriSat)) map.removeLayer(esriSat);
          osm.addTo(map);

          toggleBtn.classList.remove("active");
          toggleBtn.setAttribute("aria-pressed", "false");
          toggleBtn.querySelector(".material-symbols-rounded").textContent = "public_off";

          // Kembalikan icon reset ke hitam
          toggleGroup.classList.remove("satellite-on");
      }
  });

  // =============================
  // 6. Reset Button
  // =============================
  const resetBtn = document.getElementById("resetMap");

  resetBtn.addEventListener("click", () => {
    if (karangasemBounds) {
      map.fitBounds(karangasemBounds, { animate: true });
    }
  });

  // =============================
  // 7. ICON MATERIAL YOU
  // =============================
  function createMaterialIcon(iconText) {
      return L.divIcon({
          className: "material-marker",
          html: `
              <div class="marker-shell">
                  <span class="material-symbols-rounded">${iconText}</span>
              </div>
          `,
          iconSize: [20, 20],
          iconAnchor: [14, 14]
      });
  }

  const iconJenis = {
      "tempat-penting": createMaterialIcon("location_on"),
      "tempat-tourist": createMaterialIcon("map_pin_heart")
  };


  // =============================
  // 8. LOAD DATA PIN DARI DATABASE
  // =============================
  fetch("get_lokasi.php")
      .then(res => res.json())
      .then(data => {

          data.forEach(loc => {

              let marker = L.marker([loc.latitude, loc.longitude], {
                  icon: iconJenis[loc.jenis]
              }).addTo(map);

              // Popup custom
              const popupHTML = `
                  <div class="popup-box">
                      <div class="popup-header">
                          <span class="popup-title">${loc.nama}</span>
                          <a class="popup-direction" 
                            href="https://www.google.com/maps?daddr=${loc.latitude},${loc.longitude}" 
                            target="_blank">
                            <span class="material-symbols-rounded">assistant_direction</span>
                          </a>
                      </div>
                      <img class="popup-img" src="${loc.path_foto}" />
                  </div>
              `;

              marker.bindPopup(popupHTML);

              let hoverTimeout = null;

              marker.on("mouseover", function () {
                  clearTimeout(hoverTimeout);
                  this.openPopup();

                  // Ambil popup DOM setelah terbuka
                  this.getPopup().once("add", () => {
                      const popupEl = document.querySelector(".leaflet-popup");
                      if (!popupEl) return;

                      // Ketika mouse masuk popup → jangan tutup
                      popupEl.addEventListener("mouseenter", () => {
                          clearTimeout(hoverTimeout);
                      });

                      // Ketika mouse keluar popup
                      popupEl.addEventListener("mouseleave", () => {
                          hoverTimeout = setTimeout(() => {
                              map.closePopup();
                          }, 150);
                      });
                  });
              });

              // Ketika mouse keluar marker
              marker.on("mouseout", function () {
                  hoverTimeout = setTimeout(() => {
                      map.closePopup();
                  }, 150);
              });


          });

      })
      .catch(err => console.error("Error loading lokasi_umum:", err));


    



});
