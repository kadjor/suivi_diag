<?php
$user = \Core\Auth::user();
$is_client = ($user['role_name'] === 'client');
?>

<div class="patrimoine-map-page">
    <div class="map-header">
        <h1>🗺️ Carte du Patrimoine</h1>
        <div class="map-controls">
            <button onclick="resetMap()" class="btn btn-secondary">🔄 Réinitialiser</button>
            <button onclick="toggleClustering()" class="btn btn-secondary" id="clusterBtn">
                📍 Regrouper
            </button>
        </div>
    </div>

    <!-- Panneau latéral -->
    <div class="map-layout">
        <div class="map-sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Navigation</h2>
                <input type="text" id="searchInput" placeholder="🔍 Rechercher un site, ville, groupe..." class="search-input">
            </div>

            <div class="sidebar-content">
                <!-- Navigation par villes -->
                <div class="nav-section">
                    <h3 onclick="toggleSection('cities')" class="section-toggle">
                        <span id="cities-icon">▼</span> Villes (<?= count($cities ?? []) ?>)
                    </h3>
                    <div id="cities-list" class="nav-list">
                        <?php
                        $cities_grouped = [];
                        foreach ($sites ?? [] as $site) {
                            $city = $site['city'] ?? 'Non défini';
                            if (!isset($cities_grouped[$city])) {
                                $cities_grouped[$city] = [];
                            }
                            $cities_grouped[$city][] = $site;
                        }
                        arsort($cities_grouped);

                        foreach ($cities_grouped as $city => $city_sites):
                        ?>
                        <div class="nav-item city-item" onclick="focusOnCity('<?= htmlspecialchars($city, ENT_QUOTES) ?>')">
                            <span class="nav-icon">🏙️</span>
                            <span class="nav-label"><?= htmlspecialchars($city) ?></span>
                            <span class="nav-count"><?= count($city_sites) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Navigation par groupes -->
                <div class="nav-section">
                    <h3 onclick="toggleSection('groups')" class="section-toggle">
                        <span id="groups-icon">▼</span> Groupes (<?= count($groups ?? []) ?>)
                    </h3>
                    <div id="groups-list" class="nav-list">
                        <?php
                        $groups_data = [];
                        foreach ($sites ?? [] as $site) {
                            $group = $site['numero_groupe'] ?? 'Sans groupe';
                            if (!isset($groups_data[$group])) {
                                $groups_data[$group] = [
                                    'sites' => [],
                                    'name' => $site['nom_groupe'] ?? $group
                                ];
                            }
                            $groups_data[$group]['sites'][] = $site;
                        }
                        ksort($groups_data);

                        foreach ($groups_data as $groupNum => $groupInfo):
                        ?>
                        <div class="nav-item group-item" onclick="focusOnGroup('<?= htmlspecialchars($groupNum, ENT_QUOTES) ?>')">
                            <span class="nav-icon">📦</span>
                            <span class="nav-label">
                                <?= htmlspecialchars($groupNum) ?>
                                <?php if ($groupInfo['name'] !== $groupNum): ?>
                                <small>(<?= htmlspecialchars($groupInfo['name']) ?>)</small>
                                <?php endif; ?>
                            </span>
                            <span class="nav-count"><?= count($groupInfo['sites']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="map-container">
            <div id="map"></div>

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
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.map-header h1 {
    margin: 0;
    font-size: 24px;
    color: #2c3e50;
}

.map-controls {
    display: flex;
    gap: 10px;
}

.map-layout {
    display: flex;
    flex: 1;
    overflow: hidden;
}

/* Sidebar */
.map-sidebar {
    width: 350px;
    background: white;
    border-right: 2px solid #e0e0e0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 2px solid #e0e0e0;
}

.sidebar-header h2 {
    margin: 0 0 15px 0;
    font-size: 18px;
    color: #2c3e50;
}

.search-input {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
}

.search-input:focus {
    outline: none;
    border-color: #3498db;
}

.sidebar-content {
    flex: 1;
    overflow-y: auto;
    padding: 10px 0;
}

.nav-section {
    margin-bottom: 10px;
}

.section-toggle {
    padding: 12px 20px;
    margin: 0;
    background: #f8f9fa;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    user-select: none;
    transition: background 0.3s;
}

.section-toggle:hover {
    background: #e9ecef;
}

.section-toggle span {
    display: inline-block;
    transition: transform 0.3s;
}

.nav-list {
    max-height: 400px;
    overflow-y: auto;
}

.nav-list.collapsed {
    max-height: 0;
    overflow: hidden;
}

.nav-item {
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.3s;
    border-left: 3px solid transparent;
}

.nav-item:hover {
    background: #e3f2fd;
    border-left-color: #3498db;
}

.nav-icon {
    font-size: 18px;
}

.nav-label {
    flex: 1;
    font-size: 14px;
    color: #2c3e50;
}

.nav-label small {
    display: block;
    font-size: 11px;
    color: #7f8c8d;
    margin-top: 2px;
}

.nav-count {
    background: #3498db;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}

/* Carte */
.map-container {
    flex: 1;
    position: relative;
}

#map {
    width: 100%;
    height: 100%;
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

.building-info {
    margin-bottom: 20px;
}

.building-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px 8px 0 0;
    margin: -20px -20px 20px -20px;
}

.building-header h3 {
    margin: 0 0 10px 0;
}

.building-address {
    font-size: 14px;
    opacity: 0.9;
}

.lots-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.lot-card {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 12px;
    transition: box-shadow 0.3s;
}

.lot-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.lot-number {
    font-weight: 600;
    color: #3498db;
    margin-bottom: 8px;
}

.lot-details {
    font-size: 13px;
    color: #7f8c8d;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.lot-reports {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #f0f0f0;
}

.report-link {
    display: inline-block;
    padding: 6px 12px;
    background: #3498db;
    color: white;
    border-radius: 4px;
    text-decoration: none;
    font-size: 12px;
    margin: 2px;
}

.report-link:hover {
    background: #2980b9;
}

/* Responsive */
@media (max-width: 768px) {
    .map-sidebar {
        position: absolute;
        left: -350px;
        z-index: 999;
        box-shadow: 2px 0 8px rgba(0,0,0,0.1);
        transition: left 0.3s;
    }

    .map-sidebar.open {
        left: 0;
    }

    .info-panel {
        max-width: calc(100% - 40px);
    }
}
</style>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
// Données des sites
const sites = <?= json_encode($sites ?? []) ?>;

// Initialisation de la carte
const map = L.map('map').setView([48.8566, 2.3522], 6); // France par défaut

// Tuiles OpenStreetMap
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

// Groupes de marqueurs
let markerClusterGroup = L.markerClusterGroup({
    chunkedLoading: true,
    spiderfyOnMaxZoom: true,
    showCoverageOnHover: false,
    zoomToBoundsOnClick: true
});

let markers = [];
let groupMarkers = {};
let cityMarkers = {};

// Icônes personnalisées
const createCustomIcon = (color, icon) => {
    return L.divIcon({
        className: 'custom-marker',
        html: `<div style="background-color: ${color}; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">${icon}</div>`,
        iconSize: [35, 35],
        iconAnchor: [17, 35]
    });
};

// Créer les marqueurs pour chaque site
sites.forEach(site => {
    if (!site.latitude || !site.longitude) return;

    const marker = L.marker([site.latitude, site.longitude], {
        icon: createCustomIcon('#3498db', '🏢')
    });

    marker.siteData = site;

    // Popup simple au survol
    marker.bindTooltip(`
        <strong>${site.numero_groupe || 'N/A'}</strong><br>
        ${site.address || 'N/A'}<br>
        ${site.city || 'N/A'}
    `);

    // Clic pour afficher détails
    marker.on('click', () => {
        showBuildingInfo(site);
    });

    markers.push(marker);
    markerClusterGroup.addLayer(marker);

    // Grouper par groupe
    const groupKey = site.numero_groupe || 'Sans groupe';
    if (!groupMarkers[groupKey]) {
        groupMarkers[groupKey] = [];
    }
    groupMarkers[groupKey].push(marker);

    // Grouper par ville
    const cityKey = site.city || 'Non défini';
    if (!cityMarkers[cityKey]) {
        cityMarkers[cityKey] = [];
    }
    cityMarkers[cityKey].push(marker);
});

map.addLayer(markerClusterGroup);

// Ajuster la vue pour afficher tous les marqueurs
if (markers.length > 0) {
    const group = L.featureGroup(markers);
    map.fitBounds(group.getBounds().pad(0.1));
}

// Toggle clustering
let clusteringEnabled = true;
function toggleClustering() {
    if (clusteringEnabled) {
        map.removeLayer(markerClusterGroup);
        markers.forEach(m => m.addTo(map));
        document.getElementById('clusterBtn').textContent = '📍 Dégrouper';
    } else {
        markers.forEach(m => map.removeLayer(m));
        map.addLayer(markerClusterGroup);
        document.getElementById('clusterBtn').textContent = '📍 Regrouper';
    }
    clusteringEnabled = !clusteringEnabled;
}

// Focus sur une ville
function focusOnCity(cityName) {
    const cityMs = cityMarkers[cityName] || [];
    if (cityMs.length === 0) return;

    const group = L.featureGroup(cityMs);
    map.fitBounds(group.getBounds().pad(0.2));

    // Highlight temporairement
    cityMs.forEach(m => {
        m.setIcon(createCustomIcon('#e74c3c', '🏙️'));
        setTimeout(() => {
            m.setIcon(createCustomIcon('#3498db', '🏢'));
        }, 2000);
    });
}

// Focus sur un groupe
function focusOnGroup(groupNum) {
    const groupMs = groupMarkers[groupNum] || [];
    if (groupMs.length === 0) return;

    const group = L.featureGroup(groupMs);
    map.fitBounds(group.getBounds().pad(0.2));
    map.setZoom(Math.min(map.getZoom(), 16));

    // Highlight
    groupMs.forEach(m => {
        m.setIcon(createCustomIcon('#f39c12', '📦'));
        setTimeout(() => {
            m.setIcon(createCustomIcon('#3498db', '🏢'));
        }, 2000);
    });

    // Afficher infos du groupe
    showGroupInfo(groupNum, groupMs);
}

// Afficher infos d'un bâtiment
function showBuildingInfo(site) {
    const panel = document.getElementById('infoPanel');
    const content = document.getElementById('infoPanelContent');

    let reportsHTML = '';
    if (site.reports && site.reports.length > 0) {
        reportsHTML = `
            <div class="lot-reports">
                <strong>📄 Rapports disponibles:</strong><br>
                ${site.reports.map(r => `
                    <a href="${r.url}" target="_blank" class="report-link">
                        ${r.name}
                    </a>
                `).join('')}
            </div>
        `;
    } else {
        reportsHTML = '<div class="lot-reports"><em>Aucun rapport disponible</em></div>';
    }

    content.innerHTML = `
        <div class="building-info">
            <div class="building-header">
                <h3>🏢 ${site.numero_groupe || 'Site'} - Lot ${site.numero_lot || 'N/A'}</h3>
                <div class="building-address">
                    ${site.address || 'N/A'}<br>
                    ${site.postal_code || ''} ${site.city || 'N/A'}
                </div>
            </div>

            <div class="lot-details">
                ${site.numero_batiment ? `<div><strong>Bâtiment:</strong> ${site.numero_batiment}</div>` : ''}
                ${site.numero_entree ? `<div><strong>Entrée:</strong> ${site.numero_entree}</div>` : ''}
                ${site.numero_porte ? `<div><strong>Porte:</strong> ${site.numero_porte}</div>` : ''}
                ${site.niveau ? `<div><strong>Niveau:</strong> ${site.niveau}</div>` : ''}
                ${site.reference_pch ? `<div><strong>Réf. PCH:</strong> ${site.reference_pch}</div>` : ''}
            </div>

            ${reportsHTML}

            <div style="margin-top: 15px;">
                <a href="/sites/${site.id}" class="btn btn-primary" style="display: inline-block; padding: 10px 20px; text-decoration: none;">
                    Voir le site complet →
                </a>
            </div>
        </div>
    `;

    panel.style.display = 'block';
}

// Afficher infos d'un groupe
function showGroupInfo(groupNum, groupMarkers) {
    const panel = document.getElementById('infoPanel');
    const content = document.getElementById('infoPanelContent');

    const sitesData = groupMarkers.map(m => m.siteData);

    content.innerHTML = `
        <div class="building-info">
            <div class="building-header">
                <h3>📦 Groupe ${groupNum}</h3>
                <div class="building-address">${sitesData.length} lot(s)</div>
            </div>

            <div class="lots-list">
                ${sitesData.map(site => `
                    <div class="lot-card" onclick="showBuildingInfo(${JSON.stringify(site).replace(/"/g, '&quot;')})">
                        <div class="lot-number">Lot ${site.numero_lot || 'N/A'}</div>
                        <div class="lot-details">
                            <div>${site.address || 'N/A'}</div>
                            <div>${site.city || 'N/A'}</div>
                            ${site.numero_batiment ? `<div>Bât. ${site.numero_batiment}</div>` : ''}
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;

    panel.style.display = 'block';
}

// Fermer le panneau d'info
function closeInfoPanel() {
    document.getElementById('infoPanel').style.display = 'none';
}

// Réinitialiser la carte
function resetMap() {
    if (markers.length > 0) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
    closeInfoPanel();
}

// Toggle sections
function toggleSection(sectionId) {
    const list = document.getElementById(sectionId + '-list');
    const icon = document.getElementById(sectionId + '-icon');

    if (list.classList.contains('collapsed')) {
        list.classList.remove('collapsed');
        icon.textContent = '▼';
    } else {
        list.classList.add('collapsed');
        icon.textContent = '▶';
    }
}

// Recherche
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase().trim();

    if (!searchTerm) {
        // Réafficher tous
        document.querySelectorAll('.nav-item').forEach(item => {
            item.style.display = 'flex';
        });
        return;
    }

    // Filtrer
    document.querySelectorAll('.nav-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>
