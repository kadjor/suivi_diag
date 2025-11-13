<h1><?= htmlspecialchars($client['organization_name'] ?? 'Client') ?></h1>

<div class="page-actions">
    <a href="/clients" class="btn">Retour à la liste</a>
    <a href="/clients/<?= $client['id'] ?>/edit" class="btn btn-primary">Modifier</a>
</div>

<div class="details-grid">
    <!-- Informations -->
    <div class="card">
        <h2>Informations</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Organisation:</strong>
                <span><?= htmlspecialchars($client['organization_name']) ?></span>
            </div>
            <div class="info-item">
                <strong>Contact:</strong>
                <span><?= htmlspecialchars($client['contact_name']) ?></span>
            </div>
            <div class="info-item">
                <strong>Email:</strong>
                <span><a href="mailto:<?= htmlspecialchars($client['email']) ?>"><?= htmlspecialchars($client['email']) ?></a></span>
            </div>
            <div class="info-item">
                <strong>Téléphone:</strong>
                <span><?= htmlspecialchars($client['phone'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>SIRET:</strong>
                <span><?= htmlspecialchars($client['siret'] ?? 'N/A') ?></span>
            </div>
        </div>
    </div>

    <!-- Adresse -->
    <div class="card">
        <h2>Adresse</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Adresse:</strong>
                <span><?= htmlspecialchars($client['address'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Ville:</strong>
                <span><?= htmlspecialchars($client['city'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Code postal:</strong>
                <span><?= htmlspecialchars($client['postal_code'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Pays:</strong>
                <span><?= htmlspecialchars($client['country'] ?? 'France') ?></span>
            </div>
        </div>
    </div>

    <!-- Notes -->
    <?php if ($client['notes']): ?>
    <div class="card full-width">
        <h2>Notes</h2>
        <p><?= nl2br(htmlspecialchars($client['notes'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- Sites / Patrimoine -->
    <div class="card full-width">
        <div class="sites-header">
            <h2>🏘️ Patrimoine (<?= count($sites ?? []) ?> lots)</h2>

            <?php if (!empty($sites)): ?>
            <div class="search-bar">
                <input type="text" id="siteSearch" placeholder="🔍 Rechercher par groupe, lot ou adresse..." class="search-input">
                <button onclick="expandAll()" class="btn btn-sm">📂 Tout déplier</button>
                <button onclick="collapseAll()" class="btn btn-sm">📁 Tout replier</button>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($sites)): ?>
        <div id="patrimonyTree"></div>
        <?php else: ?>
        <p class="no-data">Aucun site pour ce client</p>
        <?php endif; ?>
    </div>
</div>

<style>
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
}

.card.full-width {
    grid-column: 1 / -1;
}

.card h2 {
    margin-top: 0;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.info-item strong {
    display: block;
    margin-bottom: 5px;
    color: #666;
    font-size: 0.9em;
}

.info-item span {
    display: block;
    font-size: 1.05em;
}

.no-data {
    text-align: center;
    padding: 20px;
    color: #999;
}

/* Patrimoine hiérarchique */
.sites-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.search-bar {
    display: flex;
    gap: 10px;
    align-items: center;
    flex: 1;
    min-width: 300px;
}

.search-input {
    flex: 1;
    padding: 10px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 15px;
    transition: border-color 0.3s;
}

.search-input:focus {
    outline: none;
    border-color: #3498db;
}

.patrimony-group {
    margin-bottom: 15px;
    border: 2px solid #e3f2fd;
    border-radius: 8px;
    overflow: hidden;
    background: white;
}

.group-header {
    background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
    color: white;
    padding: 15px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s;
}

.group-header:hover {
    background: linear-gradient(135deg, #1976D2 0%, #1565C0 100%);
}

.group-header .toggle-icon {
    font-size: 1.2em;
    transition: transform 0.3s;
}

.group-header.collapsed .toggle-icon {
    transform: rotate(-90deg);
}

.group-info {
    display: flex;
    gap: 20px;
    align-items: center;
}

.group-badge {
    background: rgba(255,255,255,0.2);
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.9em;
}

.address-container {
    padding: 10px;
    background: #f8f9fa;
    border-left: 4px solid #2196F3;
    margin: 10px;
    border-radius: 4px;
}

.address-header {
    background: #e3f2fd;
    padding: 12px 15px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 4px;
    transition: background 0.3s;
}

.address-header:hover {
    background: #bbdefb;
}

.address-header .toggle-icon {
    transition: transform 0.3s;
}

.address-header.collapsed .toggle-icon {
    transform: rotate(-90deg);
}

.building-container {
    padding: 8px;
    margin: 8px 0 8px 20px;
    border-left: 3px solid #64B5F6;
    background: white;
}

.building-header {
    background: #f5f5f5;
    padding: 10px 12px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 4px;
    transition: background 0.3s;
}

.building-header:hover {
    background: #e0e0e0;
}

.building-header .toggle-icon {
    transition: transform 0.3s;
}

.building-header.collapsed .toggle-icon {
    transform: rotate(-90deg);
}

.entry-container {
    padding: 6px;
    margin: 6px 0 6px 20px;
    border-left: 2px solid #90CAF9;
}

.entry-header {
    background: #fafafa;
    padding: 8px 10px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 3px;
    font-size: 0.95em;
}

.entry-header:hover {
    background: #eeeeee;
}

.entry-header .toggle-icon {
    transition: transform 0.3s;
}

.entry-header.collapsed .toggle-icon {
    transform: rotate(-90deg);
}

.lot-list {
    padding: 8px 0 8px 30px;
}

.lot-item {
    padding: 10px 15px;
    margin: 5px 0;
    background: white;
    border: 1px solid #e0e0e0;
    border-left: 3px solid #42A5F5;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s;
}

.lot-item:hover {
    border-left-color: #2196F3;
    box-shadow: 0 2px 8px rgba(33, 150, 243, 0.2);
    transform: translateX(5px);
}

.lot-info {
    display: flex;
    gap: 20px;
    align-items: center;
    flex-wrap: wrap;
}

.lot-badge {
    background: #e3f2fd;
    color: #1976D2;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 0.85em;
    font-weight: 600;
}

.lot-details {
    display: flex;
    gap: 15px;
    font-size: 0.9em;
    color: #666;
}

.lot-detail-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.hidden {
    display: none !important;
}

.collapsed-content {
    display: none;
}

@media (max-width: 768px) {
    .sites-header {
        flex-direction: column;
        align-items: stretch;
    }

    .search-bar {
        flex-direction: column;
        min-width: 100%;
    }

    .lot-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<?php if (!empty($sites)): ?>
<script>
// Données des sites
const sitesData = <?= json_encode($sites) ?>;

// Construire la structure hiérarchique
function buildHierarchy(sites) {
    const hierarchy = {};

    sites.forEach(site => {
        const groupe = site.numero_groupe || 'Sans groupe';
        const address = site.address || 'Sans adresse';
        const batiment = site.numero_batiment || 'Sans bâtiment';
        const entree = site.numero_entree || 'Sans entrée';
        const lot = site.numero_lot || 'Sans lot';

        if (!hierarchy[groupe]) {
            hierarchy[groupe] = {
                nom: site.nom_groupe || groupe,
                addresses: {}
            };
        }

        if (!hierarchy[groupe].addresses[address]) {
            hierarchy[groupe].addresses[address] = {
                city: site.city,
                postal_code: site.postal_code,
                batiments: {}
            };
        }

        if (!hierarchy[groupe].addresses[address].batiments[batiment]) {
            hierarchy[groupe].addresses[address].batiments[batiment] = {
                entrees: {}
            };
        }

        if (!hierarchy[groupe].addresses[address].batiments[batiment].entrees[entree]) {
            hierarchy[groupe].addresses[address].batiments[batiment].entrees[entree] = {
                lots: []
            };
        }

        hierarchy[groupe].addresses[address].batiments[batiment].entrees[entree].lots.push(site);
    });

    return hierarchy;
}

// Afficher la hiérarchie
function renderHierarchy(hierarchy, searchTerm = '') {
    const container = document.getElementById('patrimonyTree');
    container.innerHTML = '';

    let groupIndex = 0;
    for (const [groupeNum, groupeData] of Object.entries(hierarchy)) {
        groupIndex++;
        const groupDiv = document.createElement('div');
        groupDiv.className = 'patrimony-group';
        groupDiv.dataset.groupe = groupeNum;

        let groupHasMatch = false;
        let addressesHTML = '';
        let addressIndex = 0;

        for (const [address, addressData] of Object.entries(groupeData.addresses)) {
            addressIndex++;
            let addressHasMatch = false;
            let batimentsHTML = '';
            let batimentIndex = 0;

            for (const [batiment, batimentData] of Object.entries(addressData.batiments)) {
                batimentIndex++;
                let batimentHasMatch = false;
                let entreesHTML = '';
                let entreeIndex = 0;

                for (const [entree, entreeData] of Object.entries(batimentData.entrees)) {
                    entreeIndex++;
                    let entreeHasMatch = false;
                    let lotsHTML = '';
                    let lotCount = 0;

                    entreeData.lots.forEach(lot => {
                        const matchesSearch = !searchTerm ||
                            (lot.numero_groupe && lot.numero_groupe.toLowerCase().includes(searchTerm)) ||
                            (lot.numero_lot && lot.numero_lot.toLowerCase().includes(searchTerm)) ||
                            (lot.address && lot.address.toLowerCase().includes(searchTerm)) ||
                            (lot.name && lot.name.toLowerCase().includes(searchTerm));

                        if (matchesSearch) {
                            lotCount++;
                            entreeHasMatch = true;
                            batimentHasMatch = true;
                            addressHasMatch = true;
                            groupHasMatch = true;

                            lotsHTML += `
                                <div class="lot-item">
                                    <div class="lot-info">
                                        <span class="lot-badge">Lot ${lot.numero_lot || 'N/A'}</span>
                                        <div class="lot-details">
                                            ${lot.numero_porte ? `<span class="lot-detail-item">🚪 ${lot.numero_porte}</span>` : ''}
                                            ${lot.niveau ? `<span class="lot-detail-item">📶 ${lot.niveau}</span>` : ''}
                                            ${lot.reference_pch ? `<span class="lot-detail-item">📋 ${lot.reference_pch}</span>` : ''}
                                            ${lot.name ? `<span class="lot-detail-item">🏷️ ${lot.name}</span>` : ''}
                                        </div>
                                    </div>
                                    <a href="/sites/${lot.id}" class="btn btn-sm">Voir</a>
                                </div>
                            `;
                        }
                    });

                    if (entreeHasMatch) {
                        entreesHTML += `
                            <div class="entry-container">
                                <div class="entry-header" onclick="toggleSection(this)">
                                    <span><strong>🚪 Entrée:</strong> ${entree} <span class="group-badge">${lotCount} lot(s)</span></span>
                                    <span class="toggle-icon">🔽</span>
                                </div>
                                <div class="entry-content">
                                    <div class="lot-list">
                                        ${lotsHTML}
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });

                if (batimentHasMatch) {
                    const batimentLotCount = batimentData.entrees ? Object.values(batimentData.entrees).reduce((sum, e) => sum + e.lots.length, 0) : 0;
                    batimentsHTML += `
                        <div class="building-container">
                            <div class="building-header" onclick="toggleSection(this)">
                                <span><strong>🏢 Bâtiment:</strong> ${batiment} <span class="group-badge">${batimentLotCount} lot(s)</span></span>
                                <span class="toggle-icon">🔽</span>
                            </div>
                            <div class="building-content">
                                ${entreesHTML}
                            </div>
                        </div>
                    `;
                }
            }

            if (addressHasMatch) {
                const addressLotCount = addressData.batiments ? Object.values(addressData.batiments).reduce((sum, b) =>
                    sum + Object.values(b.entrees).reduce((s, e) => s + e.lots.length, 0), 0) : 0;
                addressesHTML += `
                    <div class="address-container">
                        <div class="address-header" onclick="toggleSection(this)">
                            <div>
                                <strong>📍 ${address}</strong><br>
                                <small>${addressData.city || ''} ${addressData.postal_code || ''}</small>
                            </div>
                            <div>
                                <span class="group-badge">${addressLotCount} lot(s)</span>
                                <span class="toggle-icon">🔽</span>
                            </div>
                        </div>
                        <div class="address-content">
                            ${batimentsHTML}
                        </div>
                    </div>
                `;
            }
        }

        if (groupHasMatch) {
            const groupLotCount = Object.values(groupeData.addresses).reduce((sum, a) =>
                sum + Object.values(a.batiments).reduce((s, b) =>
                    s + Object.values(b.entrees).reduce((ss, e) => ss + e.lots.length, 0), 0), 0);

            groupDiv.innerHTML = `
                <div class="group-header" onclick="toggleSection(this)">
                    <div class="group-info">
                        <strong>📦 Groupe ${groupeNum}</strong>
                        ${groupeData.nom !== groupeNum ? `<span>(${groupeData.nom})</span>` : ''}
                        <span class="group-badge">${groupLotCount} lot(s)</span>
                    </div>
                    <span class="toggle-icon">🔽</span>
                </div>
                <div class="group-content">
                    ${addressesHTML}
                </div>
            `;
            container.appendChild(groupDiv);
        }
    }

    if (container.innerHTML === '') {
        container.innerHTML = '<p class="no-data">Aucun résultat trouvé</p>';
    }
}

// Toggle section
function toggleSection(header) {
    header.classList.toggle('collapsed');
    const content = header.nextElementSibling;
    if (content) {
        content.classList.toggle('collapsed-content');
    }
}

// Expand all
function expandAll() {
    document.querySelectorAll('.group-header, .address-header, .building-header, .entry-header').forEach(header => {
        header.classList.remove('collapsed');
        const content = header.nextElementSibling;
        if (content) {
            content.classList.remove('collapsed-content');
        }
    });
}

// Collapse all
function collapseAll() {
    document.querySelectorAll('.group-header, .address-header, .building-header, .entry-header').forEach(header => {
        header.classList.add('collapsed');
        const content = header.nextElementSibling;
        if (content) {
            content.classList.add('collapsed-content');
        }
    });
}

// Recherche
document.getElementById('siteSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase().trim();
    const hierarchy = buildHierarchy(sitesData);
    renderHierarchy(hierarchy, searchTerm);

    // Auto-expand tout lors d'une recherche
    if (searchTerm) {
        expandAll();
    }
});

// Initialiser l'affichage
const hierarchy = buildHierarchy(sitesData);
renderHierarchy(hierarchy);

// Tout replier par défaut pour une vue compacte
setTimeout(() => {
    document.querySelectorAll('.address-header, .building-header, .entry-header').forEach(header => {
        header.classList.add('collapsed');
        const content = header.nextElementSibling;
        if (content) {
            content.classList.add('collapsed-content');
        }
    });
}, 100);
</script>
<?php endif; ?>
