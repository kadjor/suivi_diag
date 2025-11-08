<div class="page-header">
    <div class="page-header-content">
        <h1>🗺️ Cartographie des diagnostics</h1>
        <div class="breadcrumb">
            <a href="/dashboard">Tableau de bord</a>
            <span>/</span>
            <span class="current">Cartographie</span>
        </div>
    </div>
</div>

<!-- Barre de recherche d'adresse -->
<div class="search-container">
    <div class="search-box">
        <input type="text" 
               id="address-search" 
               placeholder="🔍 Rechercher une adresse..." 
               class="search-input">
        <div id="address-results" class="address-results"></div>
    </div>
    <div class="search-info">
        <span id="sites-count"><?= count($sites) ?></span> sites localisés
    </div>
</div>

<!-- Carte -->
<div id="map" class="map-container"></div>

<!-- Liste des commandes à proximité -->
<div id="nearby-orders-container" class="nearby-container" style="display: none;">
    <h3>📍 Commandes à proximité</h3>
    <div id="nearby-orders-list"></div>
</div>

<!-- Légende -->
<div class="map-legend">
    <h4>Légende</h4>
    <div class="legend-item">
        <span class="marker-icon" style="background: #3498db;">📍</span>
        <span>Sites avec commandes</span>
    </div>
    <div class="legend-item">
        <span class="marker-icon" style="background: #95a5a6;">📍</span>
        <span>Sites sans commande</span>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
.page-header {
    background: white;
    padding: 20px;
    margin: -20px -20px 20px;
    border-bottom: 1px solid #e0e0e0;
}

.page-header-content h1 {
    margin: 0 0 10px 0;
    font-size: 24px;
    color: #2c3e50;
}

.breadcrumb {
    font-size: 14px;
    color: #666;
}

.breadcrumb a {
    color: #3498db;
    text-decoration: none;
}

.breadcrumb span.current {
    color: #95a5a6;
}

.search-container {
    display: flex;
    gap: 20px;
    align-items: center;
    margin-bottom: 20px;
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-box {
    flex: 1;
    position: relative;
}

.search-input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 15px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.address-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #3498db;
    border-top: none;
    border-radius: 0 0 6px 6px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.address-results.active {
    display: block;
}

.address-result-item {
    padding: 12px 15px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}

.address-result-item:hover {
    background: #f8f9fa;
}

.address-result-item:last-child {
    border-bottom: none;
}

.search-info {
    color: #7f8c8d;
    font-size: 14px;
}

.search-info span {
    font-weight: bold;
    color: #3498db;
    font-size: 18px;
}

.map-container {
    height: 600px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    margin-bottom: 20px;
}

.nearby-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.nearby-container h3 {
    margin-top: 0;
    color: #2c3e50;
}

#nearby-orders-list {
    display: grid;
    gap: 15px;
}

.order-card {
    padding: 15px;
    border-left: 4px solid #3498db;
    background: #f8f9fa;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.order-card:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.order-number {
    font-weight: bold;
    color: #2c3e50;
    font-size: 16px;
}

.order-distance {
    background: #3498db;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.order-info {
    font-size: 14px;
    color: #7f8c8d;
    margin-bottom: 5px;
}

.order-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    color: white;
    font-size: 12px;
    font-weight: 600;
}

.map-legend {
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
}

.map-legend h4 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #2c3e50;
    font-size: 14px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    font-size: 13px;
}

.marker-icon {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: white;
    font-size: 16px;
}

@media (max-width: 768px) {
    .search-container {
        flex-direction: column;
        align-items: stretch;
    }

    .map-container {
        height: 400px;
    }

    .map-legend {
        position: static;
        margin-top: 20px;
    }
}
</style>

<script>
const sites = <?= json_encode($sites) ?>;
const userRole = '<?= $user_role ?>';

// Initialiser la carte
const map = L.map('map').setView([46.603354, 1.888334], 6); // Centre de la France

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Ajouter les markers pour chaque site
sites.forEach(site => {
    const markerColor = site.orders_count > 0 ? '#3498db' : '#95a5a6';
    
    const marker = L.marker([site.latitude, site.longitude], {
        icon: L.divIcon({
            className: 'custom-marker',
            html: `<div style="background: ${markerColor}; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">📍</div>`,
            iconSize: [30, 30]
        })
    }).addTo(map);

    marker.bindPopup(`
        <div style="min-width: 200px;">
            <h4 style="margin: 0 0 10px 0;">${site.name}</h4>
            <p style="margin: 5px 0;"><strong>Client:</strong> ${site.client_name}</p>
            <p style="margin: 5px 0;"><strong>Adresse:</strong> ${site.address}, ${site.postal_code} ${site.city}</p>
            <p style="margin: 5px 0;"><strong>Commandes:</strong> ${site.orders_count}</p>
            <a href="/sites/${site.id}" style="display: inline-block; margin-top: 10px; padding: 5px 10px; background: #3498db; color: white; text-decoration: none; border-radius: 4px;">Voir le site</a>
        </div>
    `);
});

// Autocomplétion adresse
let searchTimeout;
const addressSearch = document.getElementById('address-search');
const addressResults = document.getElementById('address-results');

addressSearch.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value;

    if (query.length < 3) {
        addressResults.classList.remove('active');
        return;
    }

    searchTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`/map/search-address?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.features && data.features.length > 0) {
                addressResults.innerHTML = data.features.map(feature => `
                    <div class="address-result-item" 
                         data-lat="${feature.geometry.coordinates[1]}" 
                         data-lng="${feature.geometry.coordinates[0]}"
                         data-label="${feature.properties.label}">
                        ${feature.properties.label}
                    </div>
                `).join('');
                addressResults.classList.add('active');

                // Gérer le clic sur un résultat
                document.querySelectorAll('.address-result-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const lat = parseFloat(this.dataset.lat);
                        const lng = parseFloat(this.dataset.lng);
                        const label = this.dataset.label;

                        // Centrer la carte
                        map.setView([lat, lng], 13);

                        // Ajouter un marker temporaire
                        L.marker([lat, lng], {
                            icon: L.divIcon({
                                className: 'search-marker',
                                html: '<div style="background: #e74c3c; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.4);">📌</div>',
                                iconSize: [40, 40]
                            })
                        }).addTo(map).bindPopup(`<strong>${label}</strong>`).openPopup();

                        // Charger les commandes à proximité
                        loadNearbyOrders(lat, lng);

                        addressResults.classList.remove('active');
                        addressSearch.value = label;
                    });
                });
            } else {
                addressResults.classList.remove('active');
            }
        } catch (error) {
            console.error('Erreur recherche adresse:', error);
        }
    }, 300);
});

// Cacher les résultats si clic ailleurs
document.addEventListener('click', function(e) {
    if (!addressSearch.contains(e.target) && !addressResults.contains(e.target)) {
        addressResults.classList.remove('active');
    }
});

// Charger les commandes à proximité
async function loadNearbyOrders(lat, lng) {
    try {
        const response = await fetch(`/map/nearby-orders?lat=${lat}&lng=${lng}&radius=50`);
        const data = await response.json();

        const container = document.getElementById('nearby-orders-container');
        const list = document.getElementById('nearby-orders-list');

        if (data.orders && data.orders.length > 0) {
            list.innerHTML = data.orders.map(order => `
                <div class="order-card" onclick="window.location.href='/orders/${order.id}'">
                    <div class="order-header">
                        <span class="order-number">Commande #${order.order_number}</span>
                        <span class="order-distance">${parseFloat(order.distance).toFixed(1)} km</span>
                    </div>
                    <div class="order-info">
                        <strong>Client:</strong> ${order.client_name}
                    </div>
                    <div class="order-info">
                        <strong>Site:</strong> ${order.site_name} - ${order.address}
                    </div>
                    <div class="order-info">
                        <strong>Date:</strong> ${new Date(order.created_at).toLocaleDateString('fr-FR')}
                    </div>
                    <span class="order-status" style="background: ${order.status_color || '#95a5a6'};">
                        ${order.status_label || 'N/A'}
                    </span>
                </div>
            `).join('');
            container.style.display = 'block';
        } else {
            list.innerHTML = '<p>Aucune commande à proximité</p>';
            container.style.display = 'block';
        }
    } catch (error) {
        console.error('Erreur chargement commandes:', error);
    }
}
</script>
