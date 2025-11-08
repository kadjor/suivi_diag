<h1><?= htmlspecialchars($site['name'] ?? 'Site') ?></h1>

<div class="page-actions">
    <a href="/sites" class="btn">Retour à la liste</a>
    <a href="/sites/<?= $site['id'] ?>/edit" class="btn btn-primary">Modifier</a>
</div>

<div class="details-grid">
    <!-- Informations générales -->
    <div class="card">
        <h2>Informations générales</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Nom:</strong>
                <span><?= htmlspecialchars($site['name']) ?></span>
            </div>
            <div class="info-item">
                <strong>Référence PCH:</strong>
                <span><?= htmlspecialchars($site['reference_pch'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Type de bâtiment:</strong>
                <span><?= htmlspecialchars($site['building_type'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Année de construction:</strong>
                <span><?= $site['construction_year'] ?? 'N/A' ?></span>
            </div>
            <div class="info-item">
                <strong>Surface:</strong>
                <span><?= $site['surface'] ? number_format($site['surface']) . ' m²' : 'N/A' ?></span>
            </div>
        </div>
    </div>

    <!-- Adresse -->
    <div class="card">
        <h2>Adresse</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Adresse:</strong>
                <span><?= htmlspecialchars($site['address']) ?></span>
            </div>
            <div class="info-item">
                <strong>Ville:</strong>
                <span><?= htmlspecialchars($site['city']) ?></span>
            </div>
            <div class="info-item">
                <strong>Code postal:</strong>
                <span><?= htmlspecialchars($site['postal_code']) ?></span>
            </div>
            <?php if ($site['latitude'] && $site['longitude']): ?>
            <div class="info-item">
                <strong>Coordonnées GPS:</strong>
                <span><?= $site['latitude'] ?>, <?= $site['longitude'] ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Diagnostics -->
    <div class="card full-width">
        <h2>Diagnostics (<?= count($diagnostics ?? []) ?>)</h2>

        <?php if (!empty($diagnostics)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Référence</th>
                    <th>Validité</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($diagnostics as $diag): ?>
                <tr>
                    <td><?= htmlspecialchars($diag['type_name'] ?? $diag['type_code'] ?? 'N/A') ?></td>
                    <td><?= date('d/m/Y', strtotime($diag['date'])) ?></td>
                    <td>
                        <span class="badge" style="background-color:
                            <?= $diag['status'] == 'ok' ? '#27ae60' : ($diag['status'] == 'critical' ? '#e74c3c' : '#f39c12') ?>">
                            <?= htmlspecialchars($diag['status']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($diag['reference_number'] ?? 'N/A') ?></td>
                    <td><?= $diag['valid_until'] ? date('d/m/Y', strtotime($diag['valid_until'])) : 'N/A' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">Aucun diagnostic pour ce site</p>
        <?php endif; ?>
    </div>

    <!-- Commandes -->
    <div class="card full-width">
        <h2>Bons de commande (<?= count($orders ?? []) ?>)</h2>

        <?php if (!empty($orders)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>N° Commande</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>
                        <a href="/orders/<?= $order['id'] ?>">
                            <?= htmlspecialchars($order['order_number']) ?>
                        </a>
                    </td>
                    <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                    <td>
                        <span class="badge" style="background-color: <?= htmlspecialchars($order['status_color'] ?? '#95a5a6') ?>">
                            <?= htmlspecialchars($order['status_label'] ?? 'N/A') ?>
                        </span>
                    </td>
                    <td>
                        <a href="/orders/<?= $order['id'] ?>" class="btn btn-small">Voir détails</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">Aucune commande pour ce site</p>
        <?php endif; ?>
    </div>

    <!-- Rapports -->
    <div class="card full-width">
        <h2>Rapports de diagnostic (<?= count($reports ?? []) ?>)</h2>

        <?php if (!empty($reports)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nom du fichier</th>
                    <th>Commande</th>
                    <th>Téléversé par</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                <tr>
                    <td><?= htmlspecialchars($report['filename']) ?></td>
                    <td>
                        <a href="/orders/<?= $report['order_id'] ?>">
                            <?= htmlspecialchars($report['order_number']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($report['first_name'] . ' ' . $report['last_name']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($report['uploaded_at'])) ?></td>
                    <td>
                        <a href="/reports/<?= $report['id'] ?>/download" class="btn btn-small btn-primary" download>
                            📥 Télécharger
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">Aucun rapport pour ce site</p>
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
</style>
