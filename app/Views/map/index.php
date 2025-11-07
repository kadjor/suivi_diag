<h1>Cartographie des sites</h1>

<div class="map-controls">
    <select id="diagnostic-filter">
        <option value="">Tous les diagnostics</option>
        <option value="amiante">Amiante</option>
        <option value="dpe">DPE</option>
        <option value="plomb">Plomb</option>
        <option value="termites">Termites</option>
    </select>
</div>

<div id="map" style="height: 600px; width: 100%;"></div>

<script>
// Initialiser la carte
const map = L.map('map').setView([48.8566, 2.3522], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

// Charger les sites via AJAX
fetch('/map/sites')
    .then(response => response.json())
    .then(data => {
        data.sites.forEach(site => {
            if (site.latitude && site.longitude) {
                const marker = L.marker([site.latitude, site.longitude]).addTo(map);
                marker.bindPopup(`<b>${site.name}</b><br>${site.address}`);
            }
        });
    });
</script>
