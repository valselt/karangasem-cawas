// FUNGSI GLOBAL POPUP (Agar bisa dipanggil tombol di dalam HTML string)
// Kita simpan callback action secara global sementara
let currentPopupAction = null;

function closePopup() {
  const overlay = document.getElementById("custom-popup");
  if (overlay) overlay.classList.remove("show");
  currentPopupAction = null; // Reset action
}

function confirmAction() {
  if (currentPopupAction) {
    currentPopupAction(); // Jalankan fungsi simpanan (misal: redirect hapus)
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

  const logoLight =
    "https://cdn.ivanaldorino.web.id/karangasem/websiteutama/karangasem-color.png";
  const logoDark =
    "https://cdn.ivanaldorino.web.id/karangasem/websiteutama/karangasem-white.png";

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
  // 2. LOGIC POPUP NOTIFICATION (MODERN + CONFIRM)
  // ============================================================

  function showPopup(type, title, message, actionCallback = null) {
    const overlay = document.getElementById("custom-popup");
    const box = overlay.querySelector(".popup-box");
    const iconContainer = document.getElementById("popup-icon-container");
    const titleEl = document.getElementById("popup-title");
    const msgEl = document.getElementById("popup-message");
    const btnContainer = document.getElementById("popup-buttons");

    // 1. Reset Kelas Warna & Konten
    box.classList.remove(
      "popup-type-success",
      "popup-type-error",
      "popup-type-warning"
    );
    btnContainer.innerHTML = ""; // Kosongkan tombol lama
    currentPopupAction = actionCallback; // Simpan aksi

    // 2. Tentukan Icon & Warna
    let iconHTML = "";
    if (type === "success") {
      box.classList.add("popup-type-success");
      iconHTML = '<span class="material-symbols-rounded">done_outline</span>';
    } else if (type === "error") {
      box.classList.add("popup-type-error");
      iconHTML = '<span class="material-symbols-rounded">warning</span>'; // Ikon Gagal (Merah)
    } else {
      // Warning / Default (Kuning)
      box.classList.add("popup-type-warning");
      iconHTML = '<span class="material-symbols-rounded">priority_high</span>'; // Ikon Tanda Seru
    }

    // 3. Isi Teks
    iconContainer.innerHTML = iconHTML;
    titleEl.textContent = title;
    msgEl.textContent = message;

    // 4. Render Tombol
    if (actionCallback) {
      // --- MODE KONFIRMASI (Ada Action) ---
      // Tombol Batal
      const btnCancel = document.createElement("button");
      btnCancel.className = "btn btn-secondary"; // Class CSS abu-abu
      btnCancel.textContent = "Batal";
      btnCancel.onclick = closePopup;

      // Tombol Ya
      const btnConfirm = document.createElement("button");
      btnConfirm.className = "btn btn-danger"; // Class CSS merah
      btnConfirm.textContent = "Ya, Hapus";
      btnConfirm.onclick = confirmAction;

      btnContainer.appendChild(btnCancel);
      btnContainer.appendChild(btnConfirm);
    } else {
      // --- MODE NOTIFIKASI BIASA (Hanya Tutup) ---
      const btnClose = document.createElement("button");
      btnClose.className = "btn-popup-close"; // Class CSS tombol bulat outline
      btnClose.textContent = "Tutup";
      btnClose.onclick = closePopup;
      btnContainer.appendChild(btnClose);
    }

    // 5. Tampilkan
    overlay.classList.add("show");
  }

  // Handler Notifikasi dari PHP (URL Parameter)
  const statusEl = document.getElementById("status-message");
  if (statusEl) {
    const status = statusEl.getAttribute("data-status");

    if (status === "success_acc")
      showPopup("success", "Berhasil!", "UMKM telah disetujui dan aktif.");
    else if (status === "success_add")
      showPopup(
        "success",
        "Berhasil!",
        "Data Potensi Desa berhasil ditambahkan."
      );
    else if (status === "success_edit")
      showPopup("success", "Tersimpan!", "Perubahan data berhasil disimpan.");
    else if (status === "error")
      showPopup("error", "Gagal!", "Terjadi kesalahan saat memproses data.");

    const url = new URL(window.location);
    url.searchParams.delete("status");
    window.history.replaceState({}, "", url);
  }

  // Handler Tombol Hapus (Override Confirm Bawaan)
  document.querySelectorAll(".btn-delete").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault(); // Matikan link asli
      const url = this.getAttribute("href"); // Ambil link hapus
      const confirmMsg =
        this.getAttribute("data-confirm") ||
        "Apakah Anda yakin ingin menghapus data ini?";

      // Panggil Popup dengan Callback Redirect
      showPopup("warning", "Konfirmasi Hapus", confirmMsg, () => {
        window.location.href = url; // Redirect manual
      });
    });
  });

  // Handler Tombol Terima UMKM (Sama seperti hapus, perlu konfirmasi)
  document.querySelectorAll(".btn-acc").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const url = this.getAttribute("href");

      showPopup(
        "success",
        "Terima UMKM?",
        "UMKM ini akan ditampilkan di website utama.",
        () => {
          window.location.href = url;
        }
      );
    });
  });

  // ============================================================
  // 3. LOGIC FORM & PETA (LEAFLET JS) - TETAP SAMA
  // ============================================================
  const jenisSelect = document.getElementById("select-jenis");
  const containerTempat = document.getElementById("container-tempat");
  const containerBudaya = document.getElementById("container-budaya");
  let map = null;
  let marker = null;

  if (jenisSelect && jenisSelect.value === "tempat") {
    if (containerTempat) containerTempat.classList.remove("d-none");
    initMap();
  } else if (jenisSelect && jenisSelect.value === "budaya") {
    if (containerBudaya) containerBudaya.classList.remove("d-none");
  }

  if (jenisSelect) {
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
  // 4. DRAG & DROP & REORDER - TETAP SAMA
  // ============================================================
  const uploadArea = document.getElementById("upload-area");
  const inputFoto = document.getElementById("input-foto");
  const uploadText = document.getElementById("upload-text");

  if (uploadArea && inputFoto) {
    ["dragenter", "dragover"].forEach((eventName) => {
      uploadArea.addEventListener(eventName, (e) => {
        e.preventDefault();
        uploadArea.classList.add("dragover");
      });
    });
    ["dragleave", "drop"].forEach((eventName) => {
      uploadArea.addEventListener(eventName, (e) => {
        e.preventDefault();
        uploadArea.classList.remove("dragover");
      });
    });
    uploadArea.addEventListener("drop", (e) => {
      const files = e.dataTransfer.files;
      if (files.length > 0) {
        inputFoto.files = files;
        updateFileName(files[0].name);
      }
    });
    inputFoto.addEventListener("change", function () {
      if (this.files.length > 0) updateFileName(this.files[0].name);
    });
    function updateFileName(name) {
      if (uploadText)
        uploadText.innerHTML = `File Terpilih: <strong>${name}</strong>`;
    }
  }

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
