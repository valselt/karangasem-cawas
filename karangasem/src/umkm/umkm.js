document.addEventListener("DOMContentLoaded", () => {
    function waitForMap(callback) {
        if (window.umkmMap && window.osmLayer && window.satLayer) {
            callback();
        } else {
            setTimeout(() => waitForMap(callback), 100);
        }
    }

    waitForMap(() => {
        const map = window.umkmMap;
        const osm = window.osmLayer;
        const esriSat = window.satLayer;

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

        function updateInfoPanel(data) {

            document.getElementById('u-nama').textContent = data.nama_usaha;
            document.getElementById('u-kontak').innerHTML = `<span class="material-symbols-rounded">call</span><p>${data.kontak_usaha || '-'}</p>`;
            document.getElementById('u-kontak').href = `tel:${data.kontak_usaha}`;
            
            const elAlamat = document.getElementById('u-alamat');
            if (elAlamat) {
                elAlamat.textContent = data.alamat_usaha || '-'; 
            }

            const btnWa = document.getElementById('u-wa');
            
            const punyaWa = parseInt(data.punya_whatsapp);
            const waSama  = parseInt(data.no_wa_apakahsama);

            if (punyaWa === 1) {
                let nomorWaFinal = "";

                if (waSama === 1) {
                    nomorWaFinal = data.kontak_usaha;
                } else {
                    nomorWaFinal = data.no_wa_berbeda;
                }

                if (nomorWaFinal) {
                    nomorWaFinal = nomorWaFinal.replace(/\D/g, ''); 
                    
                    if (nomorWaFinal.startsWith('0')) {
                        nomorWaFinal = nomorWaFinal.substring(1);
                    }
                }

                btnWa.href = `https://wa.me/62${nomorWaFinal}`;
                btnWa.style.display = 'flex';
            } else {
                btnWa.style.display = 'none';
            }

            const btnIg = document.getElementById('u-ig');
            const punyaIg = parseInt(data.punya_instagram);

            if (punyaIg === 1 && data.username_instagram) {
                const cleanUser = data.username_instagram.replace('@', '');
                
                btnIg.href = `https://instagram.com/${cleanUser}`;
                btnIg.style.display = 'flex';
            } else {
                btnIg.style.display = 'none';
            }
            
            const btnFb = document.getElementById('u-fb');
            const linkFb = data.link_facebook; 

            if (linkFb && linkFb.trim() !== "") {
                btnFb.href = linkFb;
                btnFb.style.display = 'flex';
            } else {
                btnFb.style.display = 'none';
            }

            const btnDir = document.getElementById('u-direction');
            btnDir.href = `https://www.google.com/maps/dir/?api=1&destination=${data.latitude},${data.longitude}`;
            
            const imgFoto = document.getElementById('u-foto');
            if (data.path_foto_usaha) {
                imgFoto.src = data.path_foto_usaha;
                imgFoto.style.display = 'block';
            } else {
                imgFoto.style.display = 'none';
            }

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
            } else if (data.kategori_usaha === 'pengrajin') {
                katHTML = `
                    <span class="material-symbols-rounded">carpenter</span>
                    <p>Pengrajin</p>
                `;
            } else {
                katHTML = `<span class="material-symbols-rounded">storefront</span><p>${data.kategori_usaha}</p>`;
            }
            divKat.innerHTML = katHTML;


            const divQris = document.getElementById('u-qris');
            let qrisHTML = '<p>QRIS</p>';

            if (parseInt(data.qris) === 1) {
                qrisHTML += `<span class="material-symbols-rounded" style="color: #2ecc71; font-weight:bold;">check</span>`;
            } else {
                qrisHTML += `<span class="material-symbols-rounded" style="color: #e74c3c; font-weight:bold;">close</span>`;
            }
            divQris.innerHTML = qrisHTML;


            const divProduk = document.getElementById('u-produk');
            divProduk.innerHTML = '';

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
                    
                    marker.on("click", () => {
                        updateInfoPanel(u);

                        if (window.innerWidth <= 768) {
                            setTimeout(() => {
                                document.getElementById("info-umkm").scrollIntoView({ 
                                    behavior: 'smooth', 
                                    block: 'start'      
                                });
                            }, 100);
                        }
                    });

                    allMarkers.push(marker);
                });
            })
            .catch((err) => console.error("Gagal load UMKM:", err));

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