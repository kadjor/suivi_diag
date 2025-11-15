<?php
$user = \Core\Auth::user();
$is_client = ($user['role_name'] === 'client');
?>

<div class="patrimoine-map-page">
    <div class="map-header">
        <h1>🗺️ Carte des Commandes</h1>
        <div class="map-search-bar">
            <input type="text"
                   id="mapSearchInput"
                   placeholder="🔍 Rechercher un groupe, une adresse, une ville..."
                   class="map-search-input">
            <button onclick="searchOnMap()" class="btn btn-primary">Rechercher</button>
            <button onclick="clearMap()" class="btn btn-secondary">🔄 Effacer</button>
        </div>
    </div>

    <div class="map-layout">
        <!-- Carte principale -->
        <div class="map-container">
            <div id="map"></div>

            <!-- Légende -->
            <div class="map-legend">
                <h4>États des commandes</h4>
                <div class="legend-item">
                    <span class="legend-dot" style="background: #95a5a6;"></span> En attente
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background: #3498db;"></span> Confirmée
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background: #f39c12;"></span> En cours
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background: #27ae60;"></span> Terminée
                </div>
                <div class="legend-item">
                    <span class="legend-dot" style="background: #e74c3c;"></span> Annulée
                </div>
            </div>

            <!-- Panneau d'information -->
            <div class="info-panel" id="infoPanel" style="display: none;">
                <button class="info-close" onclick="closeInfoPanel()">&times;</button>
                <div id="infoPanelContent"></div>
            </div>
        </div>
    </div>
</div>

<style>
.patrimoine-map-page {
    position: fixed;
    top: 60px;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    flex-direction: column;
}

.map-header {
    background: white;
    padding: 15px 20px;
    border-bottom: 2px solid #e0e0e0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.map-header h1 {
    margin: 0 0 15px 0;
    font-size: 24px;
    color: #2c3e50;
}

.map-search-bar {
    display: flex;
    gap: 10px;
    align-items: center;
    max-width: 800px;
}

.map-search-input {
    flex: 1;
    padding: 12px 20px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 15px;
    transition: border-color 0.3s;
}

.map-search-input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.map-layout {
    display: flex;
    flex: 1;
    overflow: hidden;
}

.map-container {
    flex: 1;
    position: relative;
}

#map {
    width: 100%;
    height: 100%;
}

/* Légende */
.map-legend {
    position: absolute;
    bottom: 20px;
    left: 20px;
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    z-index: 1000;
}

.map-legend h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #2c3e50;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 5px 0;
    font-size: 13px;
}

.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 3px rgba(0,0,0,0.3);
}

/* Info Panel */
.info-panel {
    position: absolute;
    top: 20px;
    right: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-width: 400px;
    max-height: 80%;
    overflow-y: auto;
    z-index: 1000;
}

.info-close {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #7f8c8d;
    z-index: 1;
}

#infoPanelContent {
    padding: 20px;
}

.order-info {
    margin-bottom: 20px;
}

.order-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px 8px 0 0;
    margin: -20px -20px 20px -20px;
}

.order-header h3 {
    margin: 0 0 10px 0;
}

.order-status {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.order-details {
    display: flex;
    flex-direction: column;
    gap: 10px;
    font-size: 14px;
}

.order-details strong {
    color: #2c3e50;
    display: block;
    margin-bottom: 5px;
}

.order-actions {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

/* Responsive */
@media (max-width: 768px) {
    .map-search-bar {
        flex-direction: column;
        width: 100%;
    }

    .map-search-input {
        width: 100%;
    }

    .map-legend {
        bottom: 10px;
        left: 10px;
        font-size: 12px;
    }

    .info-panel {
        max-width: calc(100% - 40px);
    }
}
</style>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Données des commandes
const orders = <?= json_encode($orders ?? []) ?>;
const sites = <?= json_encode($sites ?? []) ?>;

// Initialisation de la carte (France, vue large)
const map = L.map('map').setView([46.603354, 1.888334], 6);

// Tuiles OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Marqueurs actifs
let activeMarkers = [];
let searchCircle = null;

// Couleurs selon le statut
const statusColors = {
    'pending': '#95a5a6',
    'confirmed': '#3498db',
    'in_progress': '#f39c12',
    'completed': '#27ae60',
    'cancelled': '#e74c3c'
};

// Icône personnalisée
const createOrderIcon = (color) => {
    return L.divIcon({
        className: 'custom-marker',
        html: `<div style="background-color: ${color}; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.4);"></div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });
};

// Fonction de recherche
function searchOnMap() {
    const searchTerm = document.getElementById('mapSearchInput').value.trim().toLowerCase();

    if (!searchTerm) {
        alert('Veuillez entrer un groupe, une adresse ou une ville');
        return;
    }

    // Effacer les anciens marqueurs
    clearMap();

    // Chercher les commandes correspondantes
    const matchingOrders = orders.filter(order => {
        return (
            (order.numero_groupe && order.numero_groupe.toLowerCase().includes(searchTerm)) ||
            (order.execution_address && order.execution_address.toLowerCase().includes(searchTerm)) ||
            (order.execution_city && order.execution_city.toLowerCase().includes(searchTerm)) ||
            (order.site_address && order.site_address.toLowerCase().includes(searchTerm)) ||
            (order.site_city && order.site_city.toLowerCase().includes(searchTerm))
        );
    });

    if (matchingOrders.length === 0) {
        alert('Aucune commande trouvée pour cette recherche');
        return;
    }

    // Trouver le centre des commandes trouvées
    const validOrders = matchingOrders.filter(o => o.latitude && o.longitude);

    if (validOrders.length === 0) {
        alert('Aucune coordonnée GPS disponible pour ces commandes');
        return;
    }

    const avgLat = validOrders.reduce((sum, o) => sum + parseFloat(o.latitude), 0) / validOrders.length;
    const avgLng = validOrders.reduce((sum, o) => sum + parseFloat(o.longitude), 0) / validOrders.length;

    // Zoomer sur la zone
    map.setView([avgLat, avgLng], 15);

    // Dessiner un cercle de 300m
    if (searchCircle) {
        map.removeLayer(searchCircle);
    }
    searchCircle = L.circle([avgLat, avgLng], {
        color: '#3498db',
        fillColor: '#3498db',
        fillOpacity: 0.1,
        radius: 300
    }).addTo(map);

    // Afficher toutes les commandes dans un rayon de 300m
    orders.forEach(order => {
        if (!order.latitude || !order.longitude) return;

        const distance = map.distance([avgLat, avgLng], [order.latitude, order.longitude]);

        if (distance <= 300) {
            displayOrderMarker(order);
        }
    });

    console.log(`✅ ${activeMarkers.length} commandes affichées dans un rayon de 300m`);
}

// Afficher un marqueur de commande
function displayOrderMarker(order) {
    const color = statusColors[order.status_code] || statusColors['pending'];

    const marker = L.marker([order.latitude, order.longitude], {
        icon: createOrderIcon(color)
    });

    marker.orderData = order;

    // Tooltip
    marker.bindTooltip(`
        <strong>${order.order_number}</strong><br>
        ${order.status_label || 'N/A'}<br>
        ${order.execution_address || order.site_address || 'N/A'}
    `);

    // Clic pour afficher les détails
    marker.on('click', () => {
        showOrderInfo(order);
    });

    marker.addTo(map);
    activeMarkers.push(marker);
}

// Afficher les infos d'une commande
function showOrderInfo(order) {
    const panel = document.getElementById('infoPanel');
    const content = document.getElementById('infoPanelContent');

    const statusColor = statusColors[order.status_code] || statusColors['pending'];

    content.innerHTML = `
        <div class="order-info">
            <div class="order-header">
                <h3>📦 ${order.order_number}</h3>
                <span class="order-status" style="background-color: ${statusColor}; color: white;">
                    ${order.status_label || 'N/A'}
                </span>
            </div>

            <div class="order-details">
                <div>
                    <strong>Client</strong>
                    ${order.client_name || 'N/A'}
                </div>

                <div>
                    <strong>Adresse</strong>
                    ${order.execution_address || order.site_address || 'N/A'}<br>
                    ${order.execution_city || order.site_city || ''} ${order.execution_postal_code || order.site_postal_code || ''}
                </div>

                ${order.numero_lot ? `
                <div>
                    <strong>Lot</strong>
                    ${order.numero_lot}
                </div>
                ` : ''}

                ${order.numero_groupe ? `
                <div>
                    <strong>Groupe</strong>
                    ${order.numero_groupe}
                </div>
                ` : ''}

                ${order.requested_date ? `
                <div>
                    <strong>Date demandée</strong>
                    ${new Date(order.requested_date).toLocaleDateString('fr-FR')}
                </div>
                ` : ''}

                ${order.assigned_technician ? `
                <div>
                    <strong>Technicien</strong>
                    ${order.assigned_technician}
                </div>
                ` : ''}
            </div>

            <div class="order-actions">
                <a href="/orders/${order.id}" class="btn btn-primary" style="display: inline-block; width: 100%; text-align: center; padding: 12px; text-decoration: none;">
                    Voir la commande complète →
                </a>
            </div>
        </div>
    `;

    panel.style.display = 'block';
}

// Effacer la carte
function clearMap() {
    activeMarkers.forEach(m => map.removeLayer(m));
    activeMarkers = [];

    if (searchCircle) {
        map.removeLayer(searchCircle);
        searchCircle = null;
    }

    closeInfoPanel();

    // Retour à la vue France
    map.setView([46.603354, 1.888334], 6);
}

// Fermer le panneau d'info
function closeInfoPanel() {
    document.getElementById('infoPanel').style.display = 'none';
}

// Recherche avec Entrée
document.getElementById('mapSearchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchOnMap();
    }
});

console.log(`📍 Carte des commandes prête. ${orders.length} commandes disponibles.`);
</script>
