// FUNGSI GLOBAL POPUP
let currentPopupAction = null;

function closePopup() {
  const overlay = document.getElementById("custom-popup");
  if (overlay) overlay.classList.remove("show");
  currentPopupAction = null; 
}

function confirmAction() {
  if (currentPopupAction) {
    currentPopupAction(); 
  }
  closePopup();
}

document.addEventListener("DOMContentLoaded", function () {
  // ============================================================
  // 1. LOGIC DARK MODE / LIGHT MODE
  // ============================================================
  const toggleBtn = document.getElementById("theme-toggle");
  const themeIcon = document.getElementById("theme-icon");
  const logoImg = document.getElementById("sidebar-logo");
  const body = document.body;

  const logoLight = "https://cdn.ivanaldorino.web.id/karangasem/websiteutama/karangasem-color.png";
  const logoDark = "https://cdn.ivanaldorino.web.id/karangasem/websiteutama/karangasem-white.png";

  function setTheme(themeName) {
    body.setAttribute("data-theme", themeName);
    localStorage.setItem("theme", themeName);

    if (themeName === "dark") {
      if (logoImg) logoImg.src = logoDark;
      if (themeIcon) themeIcon.textContent = "light_mode";
    } else {
      if (logoImg) logoImg.src = logoLight;
      if (themeIcon) themeIcon.textContent = "dark_mode";
    }
  }

  const savedTheme = localStorage.getItem("theme") || "light";
  setTheme(savedTheme);

  if (toggleBtn) {
    toggleBtn.addEventListener("click", function () {
      const currentTheme = body.getAttribute("data-theme");
      setTheme(currentTheme === "dark" ? "light" : "dark");
    });
  }

  // ============================================================
  // 2. LOGIC POPUP NOTIFICATION
  // ============================================================
  function showPopup(type, title, message, actionCallback = null) {
    const overlay = document.getElementById("custom-popup");
    const box = overlay.querySelector(".popup-box");
    const iconContainer = document.getElementById("popup-icon-container");
    const titleEl = document.getElementById("popup-title");
    const msgEl = document.getElementById("popup-message");
    const btnContainer = document.getElementById("popup-buttons");

    box.classList.remove("popup-type-success", "popup-type-error", "popup-type-warning");
    btnContainer.innerHTML = ""; 
    currentPopupAction = actionCallback; 

    let iconHTML = "";
    if (type === "success") {
      box.classList.add("popup-type-success");
      iconHTML = '<span class="material-symbols-rounded">done_outline</span>';
    } else if (type === "error") {
      box.classList.add("popup-type-error");
      iconHTML = '<span class="material-symbols-rounded">warning</span>'; 
    } else {
      box.classList.add("popup-type-warning");
      iconHTML = '<span class="material-symbols-rounded">priority_high</span>'; 
    }

    iconContainer.innerHTML = iconHTML;
    titleEl.textContent = title;
    
    // Menggunakan innerHTML agar tag HTML (<b>, <ul>, <li>) terbaca
    msgEl.innerHTML = message; 

    if (actionCallback) {
      const btnCancel = document.createElement("button");
      btnCancel.className = "btn btn-secondary"; 
      btnCancel.textContent = "Batal";
      btnCancel.onclick = closePopup;

      const btnConfirm = document.createElement("button");
      btnConfirm.className = "btn btn-danger"; 
      btnConfirm.textContent = "Ya";
      btnConfirm.onclick = confirmAction;

      btnContainer.appendChild(btnCancel);
      btnContainer.appendChild(btnConfirm);
    } else {
      const btnClose = document.createElement("button");
      btnClose.className = "btn-popup-close"; 
      btnClose.textContent = "Tutup";
      btnClose.onclick = closePopup;
      btnContainer.appendChild(btnClose);
    }

    overlay.classList.add("show");
  }

  const statusEl = document.getElementById("status-message");
  if (statusEl) {
      const status = statusEl.getAttribute("data-status");
      if (status === "success_acc") showPopup("success", "Berhasil!", "UMKM telah disetujui dan aktif.");
      else if (status === "success_add") showPopup("success", "Berhasil!", "Data berhasil ditambahkan.");
      else if (status === "success_edit") showPopup("success", "Tersimpan!", "Perubahan data berhasil disimpan.");
      else if (status === "success_deactivate") showPopup("success", "Non-Aktif!", "UMKM berhasil dinonaktifkan.");
      else if (status === "error") showPopup("error", "Gagal!", "Terjadi kesalahan saat memproses data.");
      else if (status === "success_tanggapan") showPopup("success", "Terkirim!", "Tanggapan Anda berhasil disimpan.");
      else if (status === "error_hak_akses") showPopup("error", "Akses Ditolak", "Anda tidak berhak menanggapi laporan ini saat ini.");
      else if (status === "success_archive") showPopup("success", "Dibersihkan!", "Laporan yang selesai telah diarsipkan.");
      else if (status === "success_selesai") showPopup("success", "Selesai & Diarsipkan", "Laporan telah selesai dan dipindahkan ke menu Arsip.");

      const url = new URL(window.location);
      url.searchParams.delete("status");
      window.history.replaceState({}, "", url);
  }

  // Event Listeners Global (Delete, Acc, Nonaktif)
  document.querySelectorAll(".btn-delete").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault(); 
      const url = this.getAttribute("href"); 
      const confirmMsg = this.getAttribute("data-confirm") || "Apakah Anda yakin ingin menghapus data ini?";
      showPopup("warning", "Konfirmasi Hapus", confirmMsg, () => { window.location.href = url; });
    });
  });

  document.querySelectorAll(".btn-acc").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const url = this.getAttribute("href");
      showPopup("success", "Terima UMKM?", "UMKM ini akan ditampilkan di website utama.", () => { window.location.href = url; });
    });
  });

  document.querySelectorAll(".btn-nonaktif").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const url = this.getAttribute("href");
      showPopup("warning", "Non-Aktifkan UMKM?", "UMKM ini tidak akan tampil di website utama.", () => { window.location.href = url; });
    });
  });

  // ============================================================
  // 3. HELPER: GET FIELD NAME (UNTUK VALIDASI)
  // ============================================================
  function getFieldName(input) {
      let label = null;
      // Cari label di parent/container terdekat
      let wrapper = input.closest('.flex-grow-1, .flex-grow-2, div[style*="margin-bottom"], .form-row > div');
      
      // Khusus untuk Upload Area
      if (input.closest('.upload-area')) {
          wrapper = input.closest('.upload-area').parentElement;
      }

      if (wrapper) {
          label = wrapper.querySelector('label');
      }

      if (label) {
          return label.innerText.replace('*', '').trim();
      }
      return "Data Input";
  }

  // ============================================================
  // 4. VALIDASI FORM UMKM
  // ============================================================
  const formUmkm = document.getElementById('form-umkm');
  if (formUmkm) {
      formUmkm.addEventListener('submit', function(e) {
          let missingFields = []; 

          // A. Check Standard Required
          const requiredInputs = formUmkm.querySelectorAll('[required]');
          requiredInputs.forEach(input => {
              let isEmpty = false;
              if (input.type === 'file') {
                  if (input.files.length === 0) isEmpty = true;
              } else {
                  if (!input.value.trim()) isEmpty = true;
              }

              if (isEmpty) {
                  let fieldName = getFieldName(input);
                  if (!missingFields.includes(fieldName)) missingFields.push(fieldName);
                  input.style.borderColor = "#e74c3c"; 
              } else {
                  input.style.borderColor = ""; 
              }
          });

          // B. Check Map
          const lat = document.getElementById('input-lat').value;
          const lng = document.getElementById('input-lng').value;
          if (!lat || !lng || lat == 0 || lng == 0) {
              missingFields.push("Lokasi Usaha (Klik Peta)");
              const mapContainer = document.getElementById('map-container');
              if(mapContainer) mapContainer.style.borderColor = "#e74c3c";
          } else {
              const mapContainer = document.getElementById('map-container');
              if(mapContainer) mapContainer.style.borderColor = "";
          }

          if (missingFields.length > 0) {
              e.preventDefault(); 
              let listHTML = "<ul style='text-align:left; margin-top:10px; color:#555;'>";
              missingFields.forEach(field => { listHTML += `<li style="margin-bottom:5px;"><b>${field}</b> belum diisi.</li>`; });
              listHTML += "</ul>";
              showPopup('error', 'Data Belum Lengkap', `Mohon lengkapi bagian berikut:${listHTML}`);
          }
      });
  }

  // ============================================================
  // 5. VALIDASI FORM POTENSI DESA (BARU)
  // ============================================================
  const formPotensi = document.getElementById('form-potensi');
  if (formPotensi) {
      formPotensi.addEventListener('submit', function(e) {
          let missingFields = []; 

          // A. Check Standard Required (Nama, Jenis, Deskripsi, Foto)
          const requiredInputs = formPotensi.querySelectorAll('[required]');
          requiredInputs.forEach(input => {
              let isEmpty = false;
              if (input.type === 'file') {
                  if (input.files.length === 0) isEmpty = true;
              } else {
                  if (!input.value.trim()) isEmpty = true;
              }

              if (isEmpty) {
                  let fieldName = getFieldName(input);
                  if (!missingFields.includes(fieldName)) missingFields.push(fieldName);
                  input.style.borderColor = "#e74c3c"; 
              } else {
                  input.style.borderColor = ""; 
              }
          });

          // B. Check Kondisional berdasarkan Jenis
          const jenisSelect = document.getElementById('select-jenis');
          const jenisVal = jenisSelect ? jenisSelect.value : '';

          if (jenisVal === 'tempat') {
              // Validasi Peta
              const lat = document.getElementById('input-lat').value;
              const lng = document.getElementById('input-lng').value;
              const mapEl = document.getElementById('map-container');
              
              if (!lat || !lng || lat == 0) {
                  missingFields.push("Lokasi (Klik Peta)");
                  if(mapEl) mapEl.style.borderColor = "#e74c3c";
              } else {
                  if(mapEl) mapEl.style.borderColor = "";
              }
          } else if (jenisVal === 'budaya') {
              // Validasi Link
              const linkInput = document.getElementById('input-link');
              if (linkInput && !linkInput.value.trim()) {
                  missingFields.push("Link Video/Website");
                  linkInput.style.borderColor = "#e74c3c";
              } else if(linkInput) {
                  linkInput.style.borderColor = "";
              }
          }

          if (missingFields.length > 0) {
              e.preventDefault(); 
              let listHTML = "<ul style='text-align:left; margin-top:10px; color:#555;'>";
              missingFields.forEach(field => { listHTML += `<li style="margin-bottom:5px;"><b>${field}</b> belum diisi.</li>`; });
              listHTML += "</ul>";
              showPopup('error', 'Data Belum Lengkap', `Mohon lengkapi bagian berikut:${listHTML}`);
          }
      });
  }

  // ============================================================
  // 6. LOGIC PETA & FORM LAINNYA (TETAP SAMA)
  // ============================================================
  const jenisSelect = document.getElementById("select-jenis");
  const containerTempat = document.getElementById("container-tempat");
  const containerBudaya = document.getElementById("container-budaya");
  const mapContainer = document.getElementById("map-container"); 
  let map = null;
  let marker = null;

  // Logic Potensi Desa (Toggle Tampilan)
  if (jenisSelect) {
      if (jenisSelect.value === "tempat") {
        if (containerTempat) containerTempat.classList.remove("d-none");
        initMap();
      } else if (jenisSelect.value === "budaya") {
        if (containerBudaya) containerBudaya.classList.remove("d-none");
      }

      jenisSelect.addEventListener("change", function () {
        const val = this.value;
        if (containerTempat) containerTempat.classList.add("d-none");
        if (containerBudaya) containerBudaya.classList.add("d-none");

        if (val === "tempat") {
          if (containerTempat) containerTempat.classList.remove("d-none");
          initMap();
        } else if (val === "budaya") {
          if (containerBudaya) containerBudaya.classList.remove("d-none");
        }
      });
  } 
  // Logic UMKM
  else if (mapContainer) {
      initMap();
  }

  function initMap() {
    if (!document.getElementById("map-container")) return;
    
    if (map) {
      setTimeout(() => {
        map.invalidateSize();
      }, 100);
      return;
    }

    let startLat = -7.795;
    let startLng = 110.7;
    let startZoom = 14;

    const inputLat = document.getElementById("input-lat");
    const inputLng = document.getElementById("input-lng");
    const existingLat = inputLat ? inputLat.value : "";
    const existingLng = inputLng ? inputLng.value : "";

    if (existingLat && existingLng) {
      startLat = parseFloat(existingLat);
      startLng = parseFloat(existingLng);
      startZoom = 17;
    }

    map = L.map("map-container").setView([startLat, startLng], startZoom);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "© OpenStreetMap",
    }).addTo(map);

    if (existingLat && existingLng) {
      marker = L.marker([startLat, startLng]).addTo(map);
    }

    map.on("click", function (e) {
      const lat = e.latlng.lat;
      const lng = e.latlng.lng;
      if (inputLat) inputLat.value = lat;
      if (inputLng) inputLng.value = lng;
      if (marker) {
        marker.setLatLng(e.latlng);
      } else {
        marker = L.marker(e.latlng).addTo(map);
      }
      const mapEl = document.getElementById('map-container');
      if(mapEl) mapEl.style.borderColor = "";
    });

    fetch("karangasem.geojson")
      .then((res) => res.json())
      .then((geojsonData) => {
        const layer = L.geoJSON(geojsonData, {
          style: { color: "#3498db", weight: 2, fillOpacity: 0.1 },
        }).addTo(map);
        if (!existingLat) map.fitBounds(layer.getBounds());
      })
      .catch((err) => console.log("GeoJSON info: " + err.message));
  }

  // ============================================================
  // 7. DRAG & DROP & REORDER
  // ============================================================
  function setupUploadArea(areaId, inputId, textId) {
        const area = document.getElementById(areaId);
        const input = document.getElementById(inputId);
        const text = document.getElementById(textId);

        if(!area || !input) return;

        ['dragenter', 'dragover'].forEach(eventName => {
            area.addEventListener(eventName, (e) => {
                e.preventDefault();
                area.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            area.addEventListener(eventName, (e) => {
                e.preventDefault();
                area.classList.remove('dragover');
            });
        });

        area.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                input.files = files;
                if(text) text.innerHTML = `File Terpilih: <strong>${files[0].name}</strong>`;
                input.style.borderColor = "";
            }
        });

        input.addEventListener('change', function() {
            if (this.files.length > 0) {
                if(text) text.innerHTML = `File Terpilih: <strong>${this.files[0].name}</strong>`;
                input.style.borderColor = "";
            }
        });
  }

  setupUploadArea('upload-area', 'input-foto', 'upload-text');
  setupUploadArea('upload-area-usaha', 'input-foto-usaha', 'upload-text-usaha');
  setupUploadArea('upload-area-produk', 'input-foto-produk', 'upload-text-produk');

  // Logic Sortable Table (Reorder)
  const sortableList = document.getElementById("sortable-list");
  let draggedItem = null;
  if (sortableList) {
    const rows = sortableList.querySelectorAll(".draggable-row");
    rows.forEach((row) => {
      row.addEventListener("dragstart", function () {
        draggedItem = row;
        setTimeout(() => row.classList.add("dragging"), 0);
      });
      row.addEventListener("dragend", function () {
        setTimeout(() => {
          row.classList.remove("dragging");
          draggedItem = null;
          saveNewOrder();
        }, 0);
      });
    });
    sortableList.addEventListener("dragover", function (e) {
      e.preventDefault();
      const afterElement = getDragAfterElement(sortableList, e.clientY);
      if (afterElement == null) sortableList.appendChild(draggedItem);
      else sortableList.insertBefore(draggedItem, afterElement);
    });
  }
  function getDragAfterElement(container, y) {
    const draggableElements = [
      ...container.querySelectorAll(".draggable-row:not(.dragging)"),
    ];
    return draggableElements.reduce(
      (closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset)
          return { offset: offset, element: child };
        else return closest;
      },
      { offset: Number.NEGATIVE_INFINITY }
    ).element;
  }
  function saveNewOrder() {
    const rows = document.querySelectorAll("#sortable-list .draggable-row");
    let orderData = [];
    rows.forEach((row, index) => {
      orderData.push({ id: row.getAttribute("data-id"), urutan: index + 1 });
    });
    fetch("index.php?action=reorder_potensi", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(orderData),
    });
  }
});