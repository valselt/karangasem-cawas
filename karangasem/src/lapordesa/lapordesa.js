document.addEventListener("DOMContentLoaded", () => {
  console.log("Javascript Lapor Desa Dimuat...");

  const ticketDisplay = document.getElementById("last-ticket-display");
  const ticketText = document.getElementById("ticket-number-text");
  const savedTicket = localStorage.getItem("lapor_desa_last_ticket");

  if (savedTicket && ticketDisplay && ticketText) {
      ticketText.textContent = savedTicket;
      ticketDisplay.style.display = "flex";
  }

  const copyBtn = document.getElementById("copy-ticket-btn");
  
  if (copyBtn && ticketText) {
    copyBtn.addEventListener("click", () => {
      const textToCopy = ticketText.textContent;

      navigator.clipboard.writeText(textToCopy).then(() => {
        
        const iconSpan = copyBtn.querySelector("span");
        const originalIcon = iconSpan.textContent;
        
        iconSpan.textContent = "check";
        iconSpan.style.color = "#2e7d32";

        setTimeout(() => {
          iconSpan.textContent = "content_copy";
          iconSpan.style.color = "";
        }, 2000);
        
      }).catch(err => {
        console.error("Gagal menyalin: ", err);
        alert("Gagal menyalin tiket. Silakan salin manual.");
      });
    });
  }

  const gpsBtn = document.querySelector("#ambil-gps");
  const gpsInput = document.querySelector("#koordinat_gps");
  const gpsIcon = gpsBtn ? gpsBtn.querySelector("span") : null;

  if (gpsBtn && gpsInput) {
    gpsBtn.addEventListener("click", () => {
      console.log("Tombol GPS Ditekan");

      if (!navigator.geolocation) {
        alert("Browser Anda tidak mendukung fitur GPS.");
        return;
      }

      if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && !window.location.hostname.startsWith('127.0.0.')) {
         alert("⚠️ FITUR DIBLOKIR BROWSER!\n\nFitur GPS hanya bisa digunakan jika website diakses menggunakan HTTPS (Gembok Hijau).\n\nSilakan akses ulang website ini menggunakan https://...");
         return;
      }

      if (gpsBtn.classList.contains("active")) {
        gpsBtn.classList.remove("active");
        gpsInput.value = "";
        if (gpsIcon) gpsIcon.textContent = "my_location";
        return;
      }

      if (gpsIcon) gpsIcon.textContent = "hourglass_top";
      gpsBtn.style.cursor = "wait";

      navigator.geolocation.getCurrentPosition(
        (pos) => {
          const lat = pos.coords.latitude.toFixed(8);
          const long = pos.coords.longitude.toFixed(8);
          const akurasi = Math.round(pos.coords.accuracy);

          console.log(`Lokasi Ditemukan: ${lat}, ${long} (Akurasi: ${akurasi}m)`);
          gpsInput.value = `${lat},${long}`;
          gpsBtn.classList.add("active");
          gpsBtn.style.cursor = "pointer";
          if (gpsIcon) gpsIcon.textContent = "check";
        },
        (err) => {
          console.error("GPS Error:", err);
          gpsBtn.style.cursor = "pointer";
          if (gpsIcon) gpsIcon.textContent = "my_location";

          if (err.code === 1) {
             alert("GAGAL: Izin lokasi ditolak.\nSilakan izinkan akses lokasi di pengaturan browser/HP Anda.");
          } else if (err.code === 2) {
             alert("GAGAL: Sinyal GPS tidak ditemukan.\nPastikan GPS di HP aktif dan coba geser ke area terbuka.");
          } else if (err.code === 3) {
             alert("GAGAL: Waktu habis (Timeout) saat mencari sinyal GPS.");
          } else {
             alert("GAGAL mengambil lokasi: " + err.message);
          }
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 0,
        }
      );
    });
  } else {
    console.error("Elemen tombol GPS (#ambil-gps) tidak ditemukan di HTML!");
  }

  const fileInput = document.querySelector("#bukti-foto");
  const dropArea = document.querySelector(".file-drop-area");
  const fileTextDesktop = document.querySelector(".file-drop-text-desktop");
  const fileButtonTextMobile = document.querySelector(".file-button-text-mobile");
  const fileNameDisplay = document.querySelector(".file-upload-filename");

  function handleFileSelect(file) {
    if (file) {
      if (fileNameDisplay) {
        fileNameDisplay.textContent = "File: " + file.name;
        fileNameDisplay.style.display = "block";
      }
      if (fileTextDesktop) fileTextDesktop.style.display = "none";
      if (fileButtonTextMobile) fileButtonTextMobile.textContent = "Ganti Foto";
    }
  }

  if (fileInput) {
    fileInput.addEventListener("change", () => handleFileSelect(fileInput.files[0]));

    if (dropArea) {
      ["dragenter", "dragover"].forEach((eventName) => {
        dropArea.addEventListener(eventName, (e) => {
          e.preventDefault();
          dropArea.classList.add("drag-over");
        });
      });
      ["dragleave", "drop"].forEach((eventName) => {
        dropArea.addEventListener(eventName, (e) => {
          e.preventDefault();
          dropArea.classList.remove("drag-over");
        });
      });
      dropArea.addEventListener("drop", (e) => {
        const file = e.dataTransfer.files[0];
        fileInput.files = e.dataTransfer.files;
        handleFileSelect(file);
      });
    }
  }

  const form = document.querySelector(".form-laporan");
  const kirimBtn = document.querySelector("#kirim-laporan");
  const progressBars = document.querySelectorAll(".upload-progress");
  const barFill = document.querySelectorAll(".upload-progress-bar");

  if (form && kirimBtn) {
    kirimBtn.addEventListener("click", (e) => {
      const nama = document.getElementById("nama_lengkap").value.trim();
      const nomor = document.getElementById("nomor").value.trim();
      const alamat = document.getElementById("alamat").value.trim();
      const pesan = document.getElementById("pesan").value.trim();
      const file = document.getElementById("bukti-foto").files[0];

      const gps = document.getElementById("koordinat_gps").value.trim(); 

      let missing = [];
      if (!nama) missing.push("Nama");
      if (!nomor) missing.push("Nomor HP");
      if (!alamat) missing.push("Alamat");
      if (!pesan) missing.push("Keluhan");
      if (!file) missing.push("Foto Bukti");
      if (!gps) missing.push("Titik Lokasi (GPS)"); 

      if (missing.length > 0) {
        e.preventDefault();
        document.getElementById("warning-text").innerHTML = "Anda Belum Mengisi:<br><b>" + missing.join(", ") + "</b>";
        
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
        return;
      }

      e.preventDefault();
      progressBars.forEach((p) => (p.style.display = "block"));

      let formData = new FormData(form);
      let xhr = new XMLHttpRequest();
      
      xhr.open("POST", "proses-lapordesa.php", true);
      xhr.upload.onprogress = function (event) {
        if (event.lengthComputable) {
          let percent = Math.round((event.loaded / event.total) * 100);
          barFill.forEach((b) => (b.style.width = percent + "%"));
        }
      };

      xhr.onload = function () {
        if (xhr.status == 200) {
          barFill.forEach((b) => (b.style.width = "100%"));
          const tiketBaru = xhr.responseText.trim();
          if(tiketBaru.startsWith("#")) {
             localStorage.setItem("lapor_desa_last_ticket", tiketBaru);
          }
          document.getElementById("popup-overlay").style.display = "block";
          
          const popupTitle = document.querySelector("#popup-success h2");
          popupTitle.innerHTML = "Laporan Terkirim!<br><span style='font-size:0.8em; color:#666;'>Tiket: " + tiketBaru + "</span>";

          const popup = document.getElementById("popup-success");
          popup.style.display = "block";
          setTimeout(() => popup.classList.add("show"), 10);

          document.getElementById("popup-close-btn").onclick = () => {
            window.location.reload();
          };
        } else {
          alert("Gagal mengirim laporan. Server Error: " + xhr.status);
        }
      };

      xhr.onerror = function () {
        alert("Terjadi kesalahan koneksi internet.");
      };

      xhr.send(formData);
    });
  }

  const btnCekLaporan = document.querySelector(".other-button");
  const popupInput = document.getElementById("popup-ticket-input");
  const popupResult = document.getElementById("popup-ticket-result");
  const overlay = document.getElementById("popup-overlay");
  
  const inputField = document.getElementById("input-ticket-field");
  const btnSubmitTicket = document.getElementById("btn-submit-ticket");
  const btnCloseTicket = document.getElementById("btn-close-ticket");
  const btnCloseResult = document.getElementById("btn-close-result");
  const timelineContent = document.getElementById("timeline-content");

  if (btnCekLaporan) {
      btnCekLaporan.addEventListener("click", (e) => {
          e.preventDefault();
          overlay.style.display = "block";
          popupInput.classList.add("show");
          
          const savedTicket = localStorage.getItem("lapor_desa_last_ticket");
          if(savedTicket) inputField.value = savedTicket;
          
          inputField.focus();
      });
  }

  function checkTicketStatus() {
      const ticketVal = inputField.value.trim();
      if (!ticketVal) {
          alert("Silakan masukkan nomor tiket!");
          return;
      }

      btnSubmitTicket.textContent = "Mencari...";
      btnSubmitTicket.disabled = true;

      const xhr = new XMLHttpRequest();
      xhr.open("POST", "cek-laporan.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      
      xhr.onload = function() {
          btnSubmitTicket.textContent = "CEK STATUS";
          btnSubmitTicket.disabled = false;

          if (xhr.status === 200) {
              popupInput.classList.remove("show");
              popupResult.classList.add("show");
              timelineContent.innerHTML = xhr.responseText;
          } else {
              alert("Tiket tidak ditemukan atau terjadi kesalahan.");
          }
      };

      xhr.send("ticket=" + encodeURIComponent(ticketVal));
  }

  if (btnSubmitTicket) {
      btnSubmitTicket.addEventListener("click", checkTicketStatus);
  }

  function closeAllPopups() {
      overlay.style.display = "none";
      popupInput.classList.remove("show");
      popupResult.classList.remove("show");
  }

  if (btnCloseTicket) btnCloseTicket.addEventListener("click", closeAllPopups);
  if (btnCloseResult) btnCloseResult.addEventListener("click", closeAllPopups);
  if (overlay) overlay.addEventListener("click", () => {
      if (popupInput.classList.contains("show") || popupResult.classList.contains("show")) {
          closeAllPopups();
      }
  });
});