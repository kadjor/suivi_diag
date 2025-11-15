<h1>Sites</h1>

<div class="page-actions">
    <a href="/sites/create" class="btn btn-primary">Nouveau site</a>
    <a href="/sites/import" class="btn btn-success">Importer depuis Excel</a>
</div>

<!-- Panneau de gestion des sites -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header">
        <h3>🗑️ Gestion des sites par client</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div style="flex: 1;">
                <label for="clientSelect" style="display: block; margin-bottom: 5px; font-weight: bold;">
                    Sélectionner un client :
                </label>
                <select id="clientSelect" class="form-control" style="width: 100%;">
                    <option value="">-- Choisir un client --</option>
                    <?php foreach ($clients ?? [] as $client): ?>
                        <option value="<?= $client['id'] ?>">
                            <?= htmlspecialchars($client['organization_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; align-items: flex-end; gap: 10px;">
                <button onclick="loadClientSites()" class="btn btn-primary">Charger les sites</button>
                <button onclick="deleteAllClientSites()" class="btn btn-danger" id="deleteAllBtn" disabled>
                    Vider tous les sites
                </button>
                <button onclick="deleteSelectedSites()" class="btn btn-warning" id="deleteSelectedBtn" disabled>
                    Supprimer sélection
                </button>
            </div>
        </div>

        <div id="clientSitesContainer" style="display: none; margin-top: 20px;">
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong id="clientName"></strong>
                        <span id="siteCount" style="margin-left: 15px; color: #666;"></span>
                    </div>
                    <button onclick="selectAllSites()" class="btn btn-sm">Tout sélectionner</button>
                </div>
            </div>

            <div id="sitesListContainer" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px;">
                <!-- Les sites seront chargés ici -->
            </div>
        </div>
    </div>
</div>

<h2>Tous les sites</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Client</th>
            <th>Adresse</th>
            <th>Ville</th>
            <th>Diagnostics</th>
            <th>Dernier diagnostic</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sites ?? [] as $site): ?>
        <tr>
            <td><a href="/sites/<?= $site['id'] ?>"><?= htmlspecialchars($site['name'] ?? 'Sans nom') ?></a></td>
            <td><?= htmlspecialchars($site['client_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($site['address'] ?? '') ?></td>
            <td><?= htmlspecialchars($site['city'] ?? '') ?></td>
            <td><?= $site['diagnostics_count'] ?? 0 ?></td>
            <td><?= $site['last_diagnostic_date'] ? date('d/m/Y', strtotime($site['last_diagnostic_date'])) : '-' ?></td>
            <td>
                <a href="/sites/<?= $site['id'] ?>" class="btn btn-sm">Voir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if (empty($sites)): ?>
<p class="no-data">Aucun site enregistré</p>
<?php endif; ?>

<style>
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    background: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #dee2e6;
    border-radius: 8px 8px 0 0;
}

.card-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.card-body {
    padding: 20px;
}

.site-item {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
    gap: 15px;
    background: white;
}

.site-item:hover {
    background: #f8f9fa;
}

.site-item:last-child {
    border-bottom: none;
}

.site-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.site-info {
    flex: 1;
}

.site-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
}

.site-details {
    color: #666;
    font-size: 13px;
}
</style>

<script>
let currentClientId = null;
let loadedSites = [];

function loadClientSites() {
    const clientSelect = document.getElementById('clientSelect');
    const clientId = clientSelect.value;

    if (!clientId) {
        alert('Veuillez sélectionner un client');
        return;
    }

    currentClientId = clientId;
    const clientName = clientSelect.options[clientSelect.selectedIndex].text;

    fetch('/api/sites/by-client/' + clientId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadedSites = data.sites;
                displaySites(data.sites, clientName);
            } else {
                alert('Erreur: ' + data.error);
            }
        })
        .catch(error => {
            alert('Erreur lors du chargement des sites: ' + error);
        });
}

function displaySites(sites, clientName) {
    const container = document.getElementById('clientSitesContainer');
    const listContainer = document.getElementById('sitesListContainer');
    const clientNameEl = document.getElementById('clientName');
    const siteCountEl = document.getElementById('siteCount');

    clientNameEl.textContent = clientName;
    siteCountEl.textContent = `${sites.length} site(s)`;

    if (sites.length === 0) {
        listContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #999;">Aucun site pour ce client</div>';
        container.style.display = 'block';
        document.getElementById('deleteAllBtn').disabled = true;
        document.getElementById('deleteSelectedBtn').disabled = true;
        return;
    }

    let html = '';
    sites.forEach(site => {
        html += `
            <div class="site-item">
                <input type="checkbox" value="${site.id}" class="site-checkbox" onchange="updateButtons()">
                <div class="site-info">
                    <div class="site-name">${escapeHtml(site.name || 'Sans nom')}</div>
                    <div class="site-details">
                        ${escapeHtml(site.address || '')} - ${escapeHtml(site.city || '')} ${escapeHtml(site.postal_code || '')}
                        ${site.numero_groupe ? '• Groupe: ' + escapeHtml(site.numero_groupe) : ''}
                        ${site.numero_lot ? '• Lot: ' + escapeHtml(site.numero_lot) : ''}
                    </div>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = html;
    container.style.display = 'block';
    document.getElementById('deleteAllBtn').disabled = false;
    updateButtons();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function selectAllSites() {
    const checkboxes = document.querySelectorAll('.site-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);

    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
    });

    updateButtons();
}

function updateButtons() {
    const checkedCount = document.querySelectorAll('.site-checkbox:checked').length;
    document.getElementById('deleteSelectedBtn').disabled = checkedCount === 0;
}

function deleteAllClientSites() {
    if (!currentClientId) return;

    const count = loadedSites.length;
    if (!confirm(`Êtes-vous sûr de vouloir supprimer TOUS les ${count} sites de ce client ?\n\nCette action est irréversible !`)) {
        return;
    }

    const formData = new FormData();
    formData.append('client_id', currentClientId);

    fetch('/sites/delete-by-client', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById('clientSitesContainer').style.display = 'none';
            document.getElementById('clientSelect').value = '';
            currentClientId = null;
            loadedSites = [];
            // Recharger la page pour mettre à jour la liste complète
            window.location.reload();
        } else {
            alert('Erreur: ' + data.error);
        }
    })
    .catch(error => {
        alert('Erreur lors de la suppression: ' + error);
    });
}

function deleteSelectedSites() {
    const checkedBoxes = document.querySelectorAll('.site-checkbox:checked');
    const siteIds = Array.from(checkedBoxes).map(cb => cb.value);

    if (siteIds.length === 0) {
        alert('Veuillez sélectionner au moins un site');
        return;
    }

    if (!confirm(`Êtes-vous sûr de vouloir supprimer ${siteIds.length} site(s) ?\n\nCette action est irréversible !`)) {
        return;
    }

    const formData = new FormData();
    siteIds.forEach(id => formData.append('site_ids[]', id));

    fetch('/sites/delete-bulk', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Recharger les sites du client
            loadClientSites();
            // Recharger la page pour mettre à jour la liste complète
            setTimeout(() => window.location.reload(), 1000);
        } else {
            alert('Erreur: ' + data.error);
        }
    })
    .catch(error => {
        alert('Erreur lors de la suppression: ' + error);
    });
}
</script>
