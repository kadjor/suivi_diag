/**
 * Gestion de la cartographie avec Leaflet
 */

let map = null;
let markers = [];
let markerCluster = null;

function initMap(elementId = 'map', options = {}) {
    const defaultOptions = {
        center: [48.8566, 2.3522], // Paris par défaut
        zoom: 13,
        maxZoom: 18,
        minZoom: 5
    };

    const mapOptions = { ...defaultOptions, ...options };

    map = L.map(elementId).setView(mapOptions.center, mapOptions.zoom);

    // Ajouter les tuiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: mapOptions.maxZoom,
        minZoom: mapOptions.minZoom
    }).addTo(map);

    return map;
}

function addMarker(lat, lng, popupContent, options = {}) {
    const marker = L.marker([lat, lng], options).addTo(map);

    if (popupContent) {
        marker.bindPopup(popupContent);
    }

    markers.push(marker);
    return marker;
}

function clearMarkers() {
    markers.forEach(marker => marker.remove());
    markers = [];
}

function loadSites(filters = {}) {
    const params = new URLSearchParams(filters);

    fetch(`/map/sites?${params}`)
        .then(response => response.json())
        .then(data => {
            clearMarkers();

            if (data.sites && data.sites.length > 0) {
                data.sites.forEach(site => {
                    if (site.latitude && site.longitude) {
                        const popupContent = `
                            <div class="site-popup">
                                <h3>${escapeHtml(site.name)}</h3>
                                <p>${escapeHtml(site.address)}</p>
                                <p>${escapeHtml(site.postal_code)} ${escapeHtml(site.city)}</p>
                                <a href="/sites/${site.id}" class="btn btn-sm">Voir détails</a>
                            </div>
                        `;

                        addMarker(site.latitude, site.longitude, popupContent);
                    }
                });

                // Ajuster la vue pour afficher tous les marqueurs
                if (markers.length > 0) {
                    const group = new L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            }
        })
        .catch(error => {
            console.error('Error loading sites:', error);
            showNotification('Erreur lors du chargement des sites', 'error');
        });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// Géocodage d'une adresse
async function geocodeAddress(address) {
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data && data.length > 0) {
            return {
                latitude: parseFloat(data[0].lat),
                longitude: parseFloat(data[0].lon)
            };
        }

        return null;
    } catch (error) {
        console.error('Geocoding error:', error);
        return null;
    }
}
