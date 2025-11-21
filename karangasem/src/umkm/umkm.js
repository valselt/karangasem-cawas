document.addEventListener("DOMContentLoaded", () => {
    // ============================================================
    // FUNGSI PEMBANTU: MENUNGGU PETA SIAP
    // ============================================================
    function waitForMap(callback) {
        if (window.umkmMap && window.osmLayer && window.satLayer) {
            callback();
        } else {
            setTimeout(() => waitForMap(callback), 100);
        }
    }

    // JALANKAN LOGIKA UTAMA SETELAH PETA SIAP
    waitForMap(() => {
        const map = window.umkmMap;
        const osm = window.osmLayer;
        const esriSat = window.satLayer;

        // ===========================
        // 1. HELPER ICON
        // ===========================
        function createMarkerIcon(iconName) {
            return L.divIcon({
                className: "material-marker",
                html: `
                    <div class="marker-shell">
                        <span class="material-symbols-rounded">${iconName}</span>
                    </div>
                `,
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });
        }

        // ===========================
        // 2. LOGIKA UPDATE INFO PANEL (INTI PERMINTAAN ANDA)
        // ===========================
        function updateInfoPanel(data) {
            // A. Update Nama & Kontak
            document.getElementById('u-nama').textContent = data.nama_usaha;
            document.getElementById('u-kontak').innerHTML = `<span class="material-symbols-rounded">call</span><p>${data.kontak_usaha || '-'}</p>`;
            document.getElementById('u-kontak').href = `tel:${data.kontak_usaha}`;
            
            const elAlamat = document.getElementById('u-alamat');
            if (elAlamat) {
                elAlamat.textContent = data.alamat_usaha || '-'; 
            }

            // ==========================================
            // 1. LOGIKA WHATSAPP
            // ==========================================
            const btnWa = document.getElementById('u-wa');
            
            // Pastikan konversi ke integer agar aman
            const punyaWa = parseInt(data.punya_whatsapp);
            const waSama  = parseInt(data.no_wa_apakahsama);

            if (punyaWa === 1) {
                let nomorWaFinal = "";

                if (waSama === 1) {
                    // a. Jika sama, ambil dari kontak_usaha
                    nomorWaFinal = data.kontak_usaha;
                } else {
                    // b. Jika beda, ambil dari no_wa_berbeda
                    nomorWaFinal = data.no_wa_berbeda;
                }

                // PEMBERSIHAN NOMOR (Penting untuk Link WA)
                // Menghapus karakter selain angka
                if (nomorWaFinal) {
                    nomorWaFinal = nomorWaFinal.replace(/\D/g, ''); 
                    
                    // Jika diawali '0', hapus '0' depannya agar jadi 628xxx
                    if (nomorWaFinal.startsWith('0')) {
                        nomorWaFinal = nomorWaFinal.substring(1);
                    }
                    // Jika user tidak sengaja sudah input 62 di database, biarkan
                }

                btnWa.href = `https://wa.me/62${nomorWaFinal}`;
                btnWa.style.display = 'flex'; // Tampilkan tombol
            } else {
                btnWa.style.display = 'none'; // Sembunyikan tombol
            }

            // ==========================================
            // 2. LOGIKA INSTAGRAM
            // ==========================================
            const btnIg = document.getElementById('u-ig');
            const punyaIg = parseInt(data.punya_instagram);

            if (punyaIg === 1 && data.username_instagram) {
                // Hapus @ jika user tidak sengaja menginputnya
                const cleanUser = data.username_instagram.replace('@', '');
                
                btnIg.href = `https://instagram.com/${cleanUser}`;
                btnIg.style.display = 'flex';
            } else {
                btnIg.style.display = 'none';
            }
            
            // ==========================================
            // 3. LOGIKA FACEBOOK
            // ==========================================
            const btnFb = document.getElementById('u-fb');
            // Ambil data link_facebook dari database
            const linkFb = data.link_facebook; 

            // Cek apakah datanya ada dan tidak kosong
            if (linkFb && linkFb.trim() !== "") {
                btnFb.href = linkFb;
                btnFb.style.display = 'flex';
            } else {
                btnFb.style.display = 'none';
            }

            // B. Update Tombol Arah
            const btnDir = document.getElementById('u-direction');
            btnDir.href = `https://www.google.com/maps/dir/?api=1&destination=${data.latitude},${data.longitude}`;
            
            // C. Update Foto Utama
            const imgFoto = document.getElementById('u-foto');
            if (data.path_foto_usaha) {
                imgFoto.src = data.path_foto_usaha;
                imgFoto.style.display = 'block';
            } else {
                imgFoto.style.display = 'none';
            }

            // D. LOGIKA KATEGORI (Sesuai Permintaan)
            const divKat = document.getElementById('u-kategori');
            let katHTML = '';
            
            if (data.kategori_usaha === 'warung') {
                katHTML = `
                    <span class="material-symbols-rounded">store</span>
                    <p>Warung</p>
                `;
            } else if (data.kategori_usaha === 'pedagangkakilima') {
                katHTML = `
                    <span class="material-symbols-rounded">yakitori</span>
                    <p>Pedagang Kaki Lima</p>
                `;
            } else if (data.kategori_usaha === 'pengrajin') { // Asumsi 'pengrajin' untuk carpenter
                katHTML = `
                    <span class="material-symbols-rounded">carpenter</span>
                    <p>Pengrajin</p>
                `;
            } else {
                // Default jika ada kategori lain
                katHTML = `<span class="material-symbols-rounded">storefront</span><p>${data.kategori_usaha}</p>`;
            }
            divKat.innerHTML = katHTML;


            // E. LOGIKA QRIS (Sesuai Permintaan)
            const divQris = document.getElementById('u-qris');
            let qrisHTML = '<p>QRIS</p>';

            if (parseInt(data.qris) === 1) {
                // Hijau check_small
                qrisHTML += `<span class="material-symbols-rounded" style="color: #2ecc71; font-weight:bold;">check</span>`;
            } else {
                // Merah close (close_small jarang didukung font standar, pakai close)
                qrisHTML += `<span class="material-symbols-rounded" style="color: #e74c3c; font-weight:bold;">close</span>`;
            }
            divQris.innerHTML = qrisHTML;


           // F. LOGIKA PRODUK (Looping umkmproduk)
            const divProduk = document.getElementById('u-produk');
            divProduk.innerHTML = ''; // Kosongkan dulu

            if (data.produk && data.produk.length > 0) {
                data.produk.forEach(prod => {
                    const prodHTML = `
                        <div class="produk-item">
                            <img src="${prod.path_foto_produk}" alt="${prod.nama_produk}">
                            <div class="prod-info">
                                <p class="prod-name">${prod.nama_produk}</p>
                                <p class="prod-price">Rp ${new Intl.NumberFormat('id-ID').format(prod.harga_produk)}</p>
                            </div>
                        </div>
                    `;
                    divProduk.innerHTML += prodHTML;
                });
            } else {
                divProduk.innerHTML = '<p style="grid-column: 1/-1; text-align:center; color:#888;">Belum ada produk.</p>';
            }
        }


        // ===========================
        // 3. TOMBOL LOKASI SAYA
        // ===========================
        const locateBtn = document.getElementById("locateUserBtn");
        let userMarker = null;
        let userCircle = null;
        
        locateBtn.addEventListener("click", () => {
            const iconSpan = locateBtn.querySelector("span");
            const originalIcon = "my_location"; 
            
            iconSpan.textContent = "hourglass_top";
            iconSpan.classList.add("spin-animation");
            
            map.locate({ setView: true, maxZoom: 17 });
            
            function onLocationFound(e) {
                const radius = e.accuracy;
                if (userMarker) map.removeLayer(userMarker);
                if (userCircle) map.removeLayer(userCircle);
                
                userCircle = L.circle(e.latlng, {
                    radius: radius,
                    color: '#4285F4', fillColor: '#4285F4', fillOpacity: 0.15, weight: 1
                }).addTo(map);
                
                const blueDotIcon = L.divIcon({
                    className: "user-gps-marker", iconSize: [18, 18], iconAnchor: [9, 9], popupAnchor: [0, -10]
                });
                
                userMarker = L.marker(e.latlng, { icon: blueDotIcon, zIndexOffset: 1000 }).addTo(map);
                
                setTimeout(() => {
                    iconSpan.textContent = originalIcon;
                    iconSpan.classList.remove("spin-animation");
                }, 1000);
            }
            
            function onLocationError(e) {
                alert("Gagal menemukan lokasi: " + e.message);
                setTimeout(() => {
                    iconSpan.textContent = originalIcon;
                    iconSpan.classList.remove("spin-animation");
                }, 1000);
            }
            map.once("locationfound", onLocationFound);
            map.once("locationerror", onLocationError);
        });

        // ===========================
        // 4. RESET MAP & SATELLITE
        // ===========================
        document.getElementById("resetMapUmkm").addEventListener("click", () => {
            if (window.karangasemBounds) {
                map.fitBounds(window.karangasemBounds, { animate: true });
            } else {
                map.setView([-7.685, 110.765], 14);
            }
        });

        const toggleBtn = document.getElementById("toggleSatelliteUmkm");
        const toggleGroup = document.querySelector(".umkm-toggle");
        const mapContainer = document.getElementById("umkmMap");
        toggleBtn.addEventListener("click", () => {
            const isOn = toggleGroup.classList.toggle("satellite-on");
            if (isOn) {
                mapContainer.classList.add("satellite-on");
                map.removeLayer(osm);
                esriSat.addTo(map);
                toggleBtn.querySelector("span").textContent = "public";
            } else {
                mapContainer.classList.remove("satellite-on");
                map.removeLayer(esriSat);
                osm.addTo(map);
                toggleBtn.querySelector("span").textContent = "public_off";
            }
        });

        // ===========================
        // 5. LOAD DATA UMKM
        // ===========================
        const categoryIcon = {
            warung: "store",
            pedagangkakilima: "yakitori",
            pengrajin: "carpenter",
        };
        let allMarkers = [];

        fetch("get_umkm.php")
            .then((r) => r.json())
            .then((data) => {
                data.forEach((u) => {
                    const iconName = categoryIcon[u.kategori_usaha] || "store";
                    
                    const marker = L.marker([u.latitude, u.longitude], {
                        icon: createMarkerIcon(iconName),
                    }).addTo(map);
                    
                    marker.umkmData = u;
                    
                    // ❗ UPDATE DI SINI ❗
                    marker.on("click", () => {
                        // 1. Update Info Panel (Fungsi yang sudah ada)
                        updateInfoPanel(u);

                        // 2. Logika Scroll Otomatis (Hanya di HP)
                        if (window.innerWidth <= 768) {
                            // Beri sedikit jeda (100ms) agar terasa natural setelah klik
                            setTimeout(() => {
                                document.getElementById("info-umkm").scrollIntoView({ 
                                    behavior: 'smooth', // Gerakan halus
                                    block: 'start'      // Scroll ke bagian atas elemen
                                });
                            }, 100);
                        }
                    });

                    allMarkers.push(marker);
                });
            })
            .catch((err) => console.error("Gagal load UMKM:", err));

        // ===========================
        // 6. SEARCH FILTER
        // ===========================
        const searchInput = document.getElementById("searchUmkm");
        const clearSearch = document.getElementById("clearSearch");
        
        function filterMarkers(keyword) {
            const lowerKey = keyword.toLowerCase();
            allMarkers.forEach((marker) => {
                const data = marker.umkmData;
                const match =
                    data.nama_usaha.toLowerCase().includes(lowerKey) ||
                    data.kategori_usaha.toLowerCase().includes(lowerKey);
                if (match) {
                    map.addLayer(marker);
                } else {
                    map.removeLayer(marker);
                }
            });
        }
        searchInput.addEventListener("input", (e) => { filterMarkers(e.target.value); });
        clearSearch.addEventListener("click", () => {
            searchInput.value = "";
            filterMarkers("");
            searchInput.focus();
        });
    });
});