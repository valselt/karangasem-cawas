// GANTI BLOK 'DOMContentLoaded' LAMA ANDA DENGAN INI

document.addEventListener("DOMContentLoaded", () => {
  // --- KUMPULKAN SEMUA ELEMEN UPLOAD FILE ---
  const fileInput = document.querySelector("#bukti-foto");

  // Elemen Desktop
  const dropArea = document.querySelector(".file-drop-area");
  const fileTextDesktop = document.querySelector(".file-drop-text-desktop");

  // Elemen Mobile
  const fileButtonTextMobile = document.querySelector(
    ".file-button-text-mobile"
  );

  // Elemen Shared
  const fileNameDisplay = document.querySelector(".file-upload-filename");

  // --- FUNGSI UNTUK UPDATE TAMPILAN SAAT FILE DIPILIH ---
  function handleFileSelect(file) {
    if (file) {
      // tampilkan nama file
      fileNameDisplay.textContent = file.name;
      fileNameDisplay.style.display = "block";

      // sembunyikan teks instruksi
      if (fileTextDesktop) {
        fileTextDesktop.style.display = "none";
      }

      // ubah tombol mobile
      if (fileButtonTextMobile) {
        fileButtonTextMobile.textContent = "Ganti File";
      }
    }
  }

  // Pastikan semua elemen ada
  if (fileInput && dropArea && fileButtonTextMobile && fileNameDisplay) {
    // --- EVENT LISTENER UNTUK KLIK / PILIH FILE (Desktop & Mobile) ---
    fileInput.addEventListener("change", () => {
      // Ambil file dari input
      const file = fileInput.files[0];
      handleFileSelect(file);
    });

    // --- EVENT LISTENERS UNTUK DRAG & DROP (Hanya Desktop) ---

    dropArea.addEventListener("dragover", (event) => {
      event.preventDefault();
      dropArea.classList.add("drag-over");
    });

    dropArea.addEventListener("dragleave", () => {
      dropArea.classList.remove("drag-over");
    });

    dropArea.addEventListener("drop", (event) => {
      event.preventDefault();
      dropArea.classList.remove("drag-over");

      // Ambil file dari drop event
      const file = event.dataTransfer.files[0];

      // Masukkan file ke input asli & update tampilan
      fileInput.files = event.dataTransfer.files;
      handleFileSelect(file);
    });
  }

  // =========================
  // GPS BUTTON (TOGGLE)
  // =========================

  const gpsBtn = document.querySelector("#ambil-gps");
  const gpsInput = document.querySelector("#koordinat_gps");

  if (gpsBtn && gpsInput) {
    gpsBtn.addEventListener("click", () => {
      if (!navigator.geolocation) {
        return;
      }

      // === Jika tombol sedang aktif → matikan kembali ===
      if (gpsBtn.classList.contains("active")) {
        gpsBtn.classList.remove("active");
        gpsInput.value = ""; // hapus koordinat
        return; // selesai
      }

      // === Jika tombol belum aktif → ambil lokasi ===
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          // RAW KOORDINAT
          const latRaw = pos.coords.latitude;
          const longRaw = pos.coords.longitude;
          const accuracy = pos.coords.accuracy; // dalam meter

          console.log("RAW GPS:", latRaw, longRaw, "Accuracy:", accuracy);

          // FORMAT 15 DIGIT (jika mau konsisten 15 digit)
          const lat = latRaw.toFixed(15);
          const long = longRaw.toFixed(15);

          gpsInput.value = `${lat},${long}`;

          // Aktifkan tampilan tombol
          gpsBtn.classList.add("active");
        },
        (err) => {
          console.log("GPS ERROR:", err);
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0,
        }
      );
    });
  }

  // ===========================
  // PROSES UPLOAD DENGAN PROGRESS BAR
  // ===========================

  const form = document.querySelector(".form-laporan");
  const kirimBtn = document.querySelector("#kirim-laporan");

  const progressBars = document.querySelectorAll(".upload-progress");
  const barFill = document.querySelectorAll(".upload-progress-bar");

  if (form && kirimBtn) {
    kirimBtn.addEventListener("click", (e) => {
      e.preventDefault(); // cegah submit biasa

      // =======================================
      // VALIDASI FORM WAJIB DIISI
      // =======================================
      const nama = document.getElementById("nama_lengkap").value.trim();
      const email = document.getElementById("email").value.trim();
      const nomor = document.getElementById("nomor").value.trim();
      const alamat = document.getElementById("alamat").value.trim();
      const rw = document.getElementById("rw").value.trim();
      const pesan = document.getElementById("pesan").value.trim();
      const file = document.getElementById("bukti-foto").files[0];

      let missing = [];

      if (!nama) missing.push("Nama");
      if (!nomor) missing.push("Nomor Telepon");
      if (!alamat) missing.push("Alamat");
      if (!rw) missing.push("RW");
      if (!pesan) missing.push("Keluhan");
      if (!file) missing.push("Upload Gambar");

      if (missing.length > 0) {
          document.getElementById("warning-text").innerHTML =
            "Anda Belum Mengisi:<br><b>" + missing.join(", ") + "</b>";

          const overlay = document.getElementById("popup-overlay");
          const warn = document.getElementById("popup-warning");

          overlay.style.display = "block";
          warn.style.display = "block";

          setTimeout(() => warn.classList.add("show"), 10);

          document.getElementById("popup-warning-btn").onclick = () => {
              warn.classList.remove("show");
              setTimeout(() => {
                  warn.style.display = "none";
                  overlay.style.display = "none";
              }, 200);
          };

          return; // HENTIKAN SUBMIT FORM
      }


      // Tampilkan progress bar
      progressBars.forEach((p) => (p.style.display = "block"));

      let formData = new FormData(form);

      let xhr = new XMLHttpRequest();
      xhr.open("POST", "proses-lapordesa.php", true);

      // UPDATE PROGRESS BAR
      xhr.upload.onprogress = function (event) {
        if (event.lengthComputable) {
          let percent = Math.round((event.loaded / event.total) * 100);

          barFill.forEach((b) => (b.style.width = percent + "%"));
        }
      };

      // JIKA SELESAI
      xhr.onload = function () {
        if (xhr.status == 200) {
          // Reset progress bar
          barFill.forEach((b) => (b.style.width = "100%"));

          // === TAMPILKAN POPUP SUKSES ===
          document.getElementById("popup-overlay").style.display = "block";
          const popup = document.getElementById("popup-success");
          popup.style.display = "block";

          // Animasi fade + scale
          setTimeout(() => popup.classList.add("show"), 10);

          // Tombol kembali
          document.getElementById("popup-close-btn").onclick = () => {
            window.location.href = "/lapordesa/";
          };
        } else {
          alert("Upload gagal. Coba lagi.");
        }
      };

      xhr.onerror = function () {
        alert("Terjadi masalah koneksi.");
      };

      xhr.send(formData);
    });
  }
});
