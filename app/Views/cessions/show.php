<?php
use Core\Auth;

$statusColors = [
    'draft' => '#95a5a6',
    'pending_validation' => '#f39c12',
    'validated' => '#3498db',
    'in_progress' => '#9b59b6',
    'completed' => '#27ae60',
    'cancelled' => '#e74c3c'
];
$statusLabels = [
    'draft' => 'Brouillon',
    'pending_validation' => 'En attente de validation',
    'validated' => 'Validée',
    'in_progress' => 'Transfert en cours',
    'completed' => 'Terminée',
    'cancelled' => 'Annulée'
];
$status = $cession['status'];
$statusColor = $statusColors[$status] ?? '#95a5a6';
$statusLabel = $statusLabels[$status] ?? $status;
?>

<div class="page-header">
    <div style="display: flex; justify-content: space-between; align-items: start;">
        <div>
            <h1>Cession <?= htmlspecialchars($cession['cession_number']) ?></h1>
            <p class="subtitle"><?= htmlspecialchars($cession['title']) ?></p>
        </div>
        <div>
            <span class="badge" style="background-color: <?= $statusColor ?>; color: white; padding: 8px 16px; border-radius: 4px; font-size: 14px;">
                <?= htmlspecialchars($statusLabel) ?>
            </span>
        </div>
    </div>
</div>

<div class="actions-bar">
    <a href="/cessions" class="btn btn-secondary">← Retour à la liste</a>

    <?php if ($can_edit): ?>
    <button class="btn btn-primary" onclick="showAddSiteModal()">
        <i class="icon-plus"></i> Ajouter des sites
    </button>
    <?php endif; ?>

    <?php if ($can_edit && $cession['status'] === 'draft' && $cession['total_sites'] > 0): ?>
    <button class="btn btn-info" onclick="submitForValidation()">
        <i class="icon-send"></i> Soumettre pour validation
    </button>
    <?php endif; ?>

    <?php if ($can_validate): ?>
    <button class="btn btn-success" onclick="validateCession()">
        <i class="icon-check"></i> Valider la cession
    </button>
    <?php endif; ?>

    <?php if ($can_edit && $cession['status'] === 'validated'): ?>
    <button class="btn btn-primary" onclick="startTransfer()">
        <i class="icon-transfer"></i> Démarrer le transfert
    </button>
    <?php endif; ?>

    <?php if ($can_cancel): ?>
    <button class="btn btn-danger" onclick="cancelCession()">
        <i class="icon-times"></i> Annuler
    </button>
    <?php endif; ?>
</div>

<!-- Informations de la cession -->
<div class="info-grid">
    <div class="info-card">
        <h3>Informations générales</h3>
        <div class="info-row">
            <span class="label">Numéro:</span>
            <span class="value"><?= htmlspecialchars($cession['cession_number']) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Titre:</span>
            <span class="value"><?= htmlspecialchars($cession['title']) ?></span>
        </div>
        <?php if ($cession['description']): ?>
        <div class="info-row">
            <span class="label">Description:</span>
            <span class="value"><?= nl2br(htmlspecialchars($cession['description'])) ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="label">Date de création:</span>
            <span class="value"><?= date('d/m/Y à H:i', strtotime($cession['created_at'])) ?></span>
        </div>
        <?php if ($cession['effective_date']): ?>
        <div class="info-row">
            <span class="label">Date effective:</span>
            <span class="value"><?= date('d/m/Y', strtotime($cession['effective_date'])) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="info-card">
        <h3>Clients</h3>
        <div class="info-row">
            <span class="label">Client cédant:</span>
            <span class="value"><strong><?= htmlspecialchars($cession['from_client_name']) ?></strong></span>
        </div>
        <div class="info-row">
            <span class="label">Client cessionnaire:</span>
            <span class="value"><strong><?= htmlspecialchars($cession['to_client_name']) ?></strong></span>
        </div>
        <div class="info-row">
            <span class="label">Créé par:</span>
            <span class="value"><?= htmlspecialchars($cession['creator_first_name'] . ' ' . $cession['creator_last_name']) ?></span>
        </div>
        <?php if ($cession['validated_by']): ?>
        <div class="info-row">
            <span class="label">Validé par:</span>
            <span class="value"><?= htmlspecialchars($cession['validator_first_name'] . ' ' . $cession['validator_last_name']) ?></span>
        </div>
        <div class="info-row">
            <span class="label">Date validation:</span>
            <span class="value"><?= date('d/m/Y à H:i', strtotime($cession['validated_at'])) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="info-card">
        <h3>Progression</h3>
        <div class="info-row">
            <span class="label">Sites totaux:</span>
            <span class="value"><strong><?= $cession['total_sites'] ?></strong></span>
        </div>
        <div class="info-row">
            <span class="label">Sites transférés:</span>
            <span class="value"><strong><?= $cession['transferred_sites'] ?></strong></span>
        </div>
        <?php if ($cession['total_sites'] > 0): ?>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= ($cession['transferred_sites'] / $cession['total_sites']) * 100 ?>%"></div>
        </div>
        <p style="text-align: center; margin-top: 5px; font-size: 12px; color: #666;">
            <?= round(($cession['transferred_sites'] / $cession['total_sites']) * 100) ?>% complété
        </p>
        <?php endif; ?>
    </div>
</div>

<!-- Sites de la cession -->
<div class="sites-section">
    <h2>Sites concernés (<?= count($sites) ?>)</h2>

    <?php if (empty($sites)): ?>
    <div class="empty-state">
        <p>Aucun site ajouté à cette cession</p>
        <?php if ($can_edit): ?>
        <button class="btn btn-primary" onclick="showAddSiteModal()">Ajouter des sites</button>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Nom du site</th>
                <th>Adresse</th>
                <th>Référence PCH</th>
                <th>Type</th>
                <th>Diagnostics</th>
                <th>Statut transfert</th>
                <?php if ($can_edit): ?>
                <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sites as $site): ?>
            <tr>
                <td><strong><?= htmlspecialchars($site['name']) ?></strong></td>
                <td><?= htmlspecialchars($site['address'] . ', ' . $site['city']) ?></td>
                <td><?= htmlspecialchars($site['reference_pch'] ?? '-') ?></td>
                <td><?= htmlspecialchars($site['building_type'] ?? '-') ?></td>
                <td><?= $site['diagnostics_total'] ?? 0 ?></td>
                <td>
                    <?php
                    $transferStatusColors = [
                        'pending' => '#f39c12',
                        'transferred' => '#27ae60',
                        'failed' => '#e74c3c'
                    ];
                    $transferStatusLabels = [
                        'pending' => 'En attente',
                        'transferred' => 'Transféré',
                        'failed' => 'Échec'
                    ];
                    $transferStatus = $site['transfer_status'];
                    ?>
                    <span class="badge" style="background-color: <?= $transferStatusColors[$transferStatus] ?? '#95a5a6' ?>">
                        <?= $transferStatusLabels[$transferStatus] ?? $transferStatus ?>
                    </span>
                    <?php if ($site['transfer_date']): ?>
                    <br><small><?= date('d/m/Y H:i', strtotime($site['transfer_date'])) ?></small>
                    <?php endif; ?>
                </td>
                <?php if ($can_edit): ?>
                <td>
                    <?php if ($site['transfer_status'] === 'pending'): ?>
                    <button class="btn btn-sm btn-danger" onclick="removeSite(<?= $site['site_id'] ?>)">
                        Retirer
                    </button>
                    <?php else: ?>
                    -
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<!-- Timeline -->
<div class="timeline-section">
    <h2>Historique</h2>
    <?php if (empty($timeline)): ?>
    <p style="color: #999;">Aucun événement</p>
    <?php else: ?>
    <div class="timeline">
        <?php foreach ($timeline as $event): ?>
        <div class="timeline-item">
            <div class="timeline-marker"></div>
            <div class="timeline-content">
                <div class="timeline-header">
                    <strong><?= htmlspecialchars($event['event_type']) ?></strong>
                    <span class="timeline-date"><?= date('d/m/Y à H:i', strtotime($event['timestamp'])) ?></span>
                </div>
                <div class="timeline-body">
                    Par: <?= htmlspecialchars($event['first_name'] . ' ' . $event['last_name']) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Ajout de sites -->
<div id="addSiteModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ajouter des sites</h3>
            <button class="close-btn" onclick="closeAddSiteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="sitesList" class="sites-list">
                <p>Chargement des sites...</p>
            </div>
        </div>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 30px;
}

.subtitle {
    color: #666;
    font-size: 16px;
    margin-top: 5px;
}

.actions-bar {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.info-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.info-card h3 {
    font-size: 16px;
    margin-bottom: 15px;
    color: #333;
    border-bottom: 2px solid #3498db;
    padding-bottom: 8px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row .label {
    color: #666;
    font-weight: 500;
}

.info-row .value {
    color: #333;
    text-align: right;
}

.progress-bar {
    width: 100%;
    height: 20px;
    background: #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 10px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    transition: width 0.3s ease;
}

.sites-section, .timeline-section {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.sites-section h2, .timeline-section h2 {
    font-size: 18px;
    margin-bottom: 20px;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #999;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -24px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #3498db;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #3498db;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.timeline-date {
    color: #666;
    font-size: 13px;
}

.timeline-body {
    color: #666;
    font-size: 14px;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.modal-header h3 {
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
}

.close-btn:hover {
    color: #333;
}

.modal-body {
    padding: 20px;
    overflow-y: auto;
}

.sites-list {
    max-height: 400px;
    overflow-y: auto;
}

.site-item {
    padding: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.site-item:hover {
    background: #f8f9fa;
    border-color: #3498db;
}

.btn {
    padding: 10px 20px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background-color: #3498db;
    color: white;
}

.btn-success {
    background-color: #27ae60;
    color: white;
}

.btn-danger {
    background-color: #e74c3c;
    color: white;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.btn-secondary {
    background-color: #95a5a6;
    color: white;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 12px;
    color: white;
}
</style>

<script>
const cessionId = <?= $cession['id'] ?>;
const fromClientId = <?= $cession['from_client_id'] ?>;

async function submitForValidation() {
    if (!confirm('Voulez-vous vraiment soumettre cette cession pour validation ?')) {
        return;
    }

    try {
        const response = await fetch(`/cessions/${cessionId}/submit`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        const result = await response.json();

        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Erreur de communication avec le serveur');
    }
}

async function validateCession() {
    if (!confirm('Voulez-vous vraiment valider cette cession ?')) {
        return;
    }

    try {
        const response = await fetch(`/cessions/${cessionId}/validate`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        const result = await response.json();

        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Erreur de communication avec le serveur');
    }
}

async function startTransfer() {
    if (!confirm('Voulez-vous vraiment démarrer le transfert des sites ?')) {
        return;
    }

    try {
        const response = await fetch(`/cessions/${cessionId}/start-transfer`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        const result = await response.json();

        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Erreur de communication avec le serveur');
    }
}

async function cancelCession() {
    const reason = prompt('Raison de l\'annulation:');
    if (!reason) {
        return;
    }

    try {
        const response = await fetch(`/cessions/${cessionId}/cancel`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({cancellation_reason: reason})
        });
        const result = await response.json();

        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Erreur de communication avec le serveur');
    }
}

async function showAddSiteModal() {
    const modal = document.getElementById('addSiteModal');
    const sitesList = document.getElementById('sitesList');

    modal.style.display = 'flex';
    sitesList.innerHTML = '<p>Chargement des sites...</p>';

    try {
        const response = await fetch(`/api/cessions/sites-by-client?client_id=${fromClientId}`);
        const result = await response.json();

        if (result.success && result.sites) {
            if (result.sites.length === 0) {
                sitesList.innerHTML = '<p>Aucun site disponible pour ce client</p>';
            } else {
                sitesList.innerHTML = result.sites.map(site => `
                    <div class="site-item" onclick="addSite(${site.id})">
                        <strong>${escapeHtml(site.name)}</strong><br>
                        <small>${escapeHtml(site.address + ', ' + site.city)}</small>
                    </div>
                `).join('');
            }
        } else {
            sitesList.innerHTML = '<p>Erreur lors du chargement des sites</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        sitesList.innerHTML = '<p>Erreur de communication avec le serveur</p>';
    }
}

function closeAddSiteModal() {
    document.getElementById('addSiteModal').style.display = 'none';
}

async function addSite(siteId) {
    try {
        const response = await fetch(`/cessions/${cessionId}/add-site`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({site_id: siteId})
        });
        const result = await response.json();

        if (result.success) {
            alert(result.message);
            closeAddSiteModal();
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Erreur de communication avec le serveur');
    }
}

async function removeSite(siteId) {
    if (!confirm('Voulez-vous vraiment retirer ce site de la cession ?')) {
        return;
    }

    try {
        const response = await fetch(`/cessions/${cessionId}/remove-site`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({site_id: siteId})
        });
        const result = await response.json();

        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Erreur de communication avec le serveur');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
