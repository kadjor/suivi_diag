<?php
$user = \Core\Auth::user();
$is_client = ($user['role_name'] === 'client');
?>

<div class="patrimoine-map-page">
    <div class="map-header">
        <h1>🗺️ Carte du Patrimoine</h1>
        <div class="map-search-bar">
            <input type="text"
                   id="mapSearchInput"
                   placeholder="🔍 Rechercher un groupe, une adresse, une ville..."
                   class="map-search-input">
            <button onclick="searchOnMap()" class="btn btn-primary">Rechercher</button>
            <button onclick="clearMap()" class="btn btn-secondary">🔄 Effacer</button>
        </div>
    </div>

    <!-- Panneau latéral -->
    <div class="map-layout">
        <div class="map-sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Navigation</h2>
                <p style="color: #7f8c8d; font-size: 13px; margin-top: 10px;">Utilisez la barre de recherche ci-dessus pour afficher les groupes</p>
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

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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

// Structures de données
let activeMarkers = [];
let siteMarkers = {}; // Marqueurs de sites individuels
let cityGroups = {};
let currentLevel = 'groups'; // 'groups' ou 'sites'
let currentGroup = null;
let searchCircle = null;

// Icônes personnalisées
const createCustomIcon = (color, icon, size = 40) => {
    return L.divIcon({
        className: 'custom-marker',
        html: `<div style="background-color: ${color}; width: ${size}px; height: ${size}px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: ${size/2}px; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.4); font-weight: bold;">${icon}</div>`,
        iconSize: [size, size],
        iconAnchor: [size/2, size]
    });
};

// Grouper les sites par numéro de groupe
const groupedSites = {};
sites.forEach(site => {
    const groupKey = site.numero_groupe || 'Sans groupe';
    if (!groupedSites[groupKey]) {
        groupedSites[groupKey] = {
            sites: [],
            name: site.nom_groupe || groupKey
        };
    }
    groupedSites[groupKey].sites.push(site);
});

console.log(`📍 Carte du patrimoine prête. ${sites.length} sites disponibles dans ${Object.keys(groupedSites).length} groupes.`);

// Fonction de recherche
function searchOnMap() {
    const searchTerm = document.getElementById('mapSearchInput').value.trim().toLowerCase();

    if (!searchTerm) {
        alert('Veuillez entrer un groupe, une adresse ou une ville');
        return;
    }

    // Effacer les anciens marqueurs
    clearMap();

    // Chercher les groupes correspondants
    const matchingGroups = {};
    Object.keys(groupedSites).forEach(groupNum => {
        const groupData = groupedSites[groupNum];
        const groupSites = groupData.sites;

        // Vérifier si le groupe correspond à la recherche
        const groupMatches =
            groupNum.toLowerCase().includes(searchTerm) ||
            groupData.name.toLowerCase().includes(searchTerm);

        // Vérifier si un site du groupe correspond
        const siteMatches = groupSites.some(site => {
            return (
                (site.address && site.address.toLowerCase().includes(searchTerm)) ||
                (site.city && site.city.toLowerCase().includes(searchTerm)) ||
                (site.numero_lot && site.numero_lot.toLowerCase().includes(searchTerm))
            );
        });

        if (groupMatches || siteMatches) {
            matchingGroups[groupNum] = groupData;
        }
    });

    if (Object.keys(matchingGroups).length === 0) {
        alert('Aucun groupe trouvé pour cette recherche');
        return;
    }

    // Afficher les groupes trouvés
    let allMatchingSites = [];
    Object.keys(matchingGroups).forEach(groupNum => {
        const groupData = matchingGroups[groupNum];
        const groupSites = groupData.sites.filter(s => s.latitude && s.longitude);

        if (groupSites.length === 0) return;
        allMatchingSites = allMatchingSites.concat(groupSites);

        // Calculer le centre du groupe
        const avgLat = groupSites.reduce((sum, s) => sum + parseFloat(s.latitude), 0) / groupSites.length;
        const avgLng = groupSites.reduce((sum, s) => sum + parseFloat(s.longitude), 0) / groupSites.length;

        // Créer le marqueur du groupe
        const groupMarker = L.marker([avgLat, avgLng], {
            icon: createCustomIcon('#e74c3c', '📦', 50)
        });

        groupMarker.groupData = {
            numero: groupNum,
            name: groupData.name,
            sites: groupSites,
            count: groupSites.length
        };

        // Tooltip
        groupMarker.bindTooltip(`
            <strong>📦 Groupe ${groupNum}</strong><br>
            ${groupData.name !== groupNum ? groupData.name + '<br>' : ''}
            <strong>${groupSites.length} lot(s)</strong>
        `, {
            permanent: false,
            direction: 'top'
        });

        // Clic sur le groupe = zoomer et afficher les sites du groupe
        groupMarker.on('click', () => {
            zoomToGroup(groupNum, groupSites);
        });

        groupMarker.addTo(map);
        activeMarkers.push(groupMarker);
    });

    // Zoomer sur les résultats
    if (activeMarkers.length > 0) {
        const group = L.featureGroup(activeMarkers);
        map.fitBounds(group.getBounds().pad(0.15));
    }

    console.log(`✅ ${Object.keys(matchingGroups).length} groupe(s) trouvé(s) pour "${searchTerm}"`);
}

// Fonction pour zoomer sur un groupe et afficher ses bâtiments
function zoomToGroup(groupNum, groupSites) {
    currentLevel = 'sites';
    currentGroup = groupNum;

    // Masquer tous les marqueurs de groupes actifs
    activeMarkers.forEach(m => map.removeLayer(m));
    activeMarkers = [];

    // Créer et afficher les marqueurs de sites pour ce groupe
    groupSites.forEach(site => {
        if (!site.latitude || !site.longitude) return;

        const siteMarker = L.marker([site.latitude, site.longitude], {
            icon: createCustomIcon('#3498db', '🏢', 35)
        });

        siteMarker.siteData = site;

        siteMarker.bindTooltip(`
            <strong>Lot ${site.numero_lot || 'N/A'}</strong><br>
            ${site.address || 'N/A'}<br>
            ${site.city || 'N/A'}
        `);

        siteMarker.on('click', () => {
            showBuildingInfo(site);
        });

        siteMarker.addTo(map);
        activeMarkers.push(siteMarker);
    });

    // Zoomer sur les sites
    if (activeMarkers.length > 0) {
        const group = L.featureGroup(activeMarkers);
        map.fitBounds(group.getBounds().pad(0.15));
    }

    // Afficher infos du groupe dans le panneau
    showGroupInfo(groupNum, groupSites);
}

// Effacer la carte
function clearMap() {
    // Supprimer tous les marqueurs actifs
    activeMarkers.forEach(m => map.removeLayer(m));
    activeMarkers = [];

    // Supprimer le cercle de recherche
    if (searchCircle) {
        map.removeLayer(searchCircle);
        searchCircle = null;
    }

    closeInfoPanel();

    // Retour à la vue France
    map.setView([48.8566, 2.3522], 6);

    currentLevel = 'groups';
    currentGroup = null;
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
function showGroupInfo(groupNum, groupSites) {
    const panel = document.getElementById('infoPanel');
    const content = document.getElementById('infoPanelContent');

    content.innerHTML = `
        <div class="building-info">
            <div class="building-header">
                <h3>📦 Groupe ${groupNum}</h3>
                <div class="building-address">${groupSites.length} lot(s)</div>
            </div>

            <div class="lots-list">
                ${groupSites.map(site => `
                    <div class="lot-card" onclick='showBuildingInfo(${JSON.stringify(site)})'>
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

// Toggle sections (pour le sidebar)
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

// Recherche avec Entrée
document.getElementById('mapSearchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchOnMap();
    }
});
</script>
