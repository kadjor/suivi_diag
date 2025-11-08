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

    <!-- Sites -->
    <div class="card full-width">
        <h2>Sites (<?= count($sites ?? []) ?>)</h2>

        <?php if (!empty($sites)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Adresse</th>
                    <th>Ville</th>
                    <th>Référence PCH</th>
                    <th>Diagnostics</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sites as $site): ?>
                <tr>
                    <td><a href="/sites/<?= $site['id'] ?>"><?= htmlspecialchars($site['name']) ?></a></td>
                    <td><?= htmlspecialchars($site['address'] ?? '') ?></td>
                    <td><?= htmlspecialchars($site['city'] ?? '') ?></td>
                    <td><?= htmlspecialchars($site['reference_pch'] ?? 'N/A') ?></td>
                    <td><?= $site['diagnostics_count'] ?? 0 ?></td>
                    <td>
                        <a href="/sites/<?= $site['id'] ?>" class="btn btn-sm">Voir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
</style>
