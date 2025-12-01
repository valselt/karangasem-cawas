document.addEventListener("DOMContentLoaded", () => {

    const map = L.map("umkmMap", {
        center: [-7.685, 110.765],
        zoom: 14,
        zoomControl: false,
        maxZoom: 18
    });

    // OSM
    const osm = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 18
    }).addTo(map);

    // SATELLITE
    const esriSat = L.tileLayer(
        "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}",
        { maxZoom: 18 }
    );

    window.umkmMap = map;
    window.osmLayer = osm;
    window.satLayer = esriSat;
    window.karangasemBounds = null; 

    // GEOJSON
    fetch("../data/geojson/karangasem.geojson")
        .then(res => res.json())
        .then(geo => {
            
            // ❗ UPDATE: MENAMBAHKAN STYLE DASH & WARNA
            const layer = L.geoJSON(geo, {
                style: {
                    color: "#ffcc00",
                    weight: 2,          
                    dashArray: '10, 10',
                    fillColor: "#ffcc00",  
                    fillOpacity: 0.2       
                }
            }).addTo(map);

            const bounds = layer.getBounds();
            map.fitBounds(bounds);

            window.karangasemBounds = bounds; 
        })
        .catch(err => console.error("Gagal load batas desa:", err));
});