<h1>Commande #<?= htmlspecialchars($order['order_number']) ?></h1>

<div class="page-actions">
    <a href="/orders" class="btn">Retour à la liste</a>
    <?php if ($can_edit): ?>
    <a href="/orders/<?= $order['id'] ?>/edit" class="btn btn-primary">Modifier</a>
    <?php endif; ?>
</div>

<div class="details-grid">
    <!-- Informations générales -->
    <div class="card">
        <h2>Informations générales</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Numéro de commande:</strong>
                <span><?= htmlspecialchars($order['order_number']) ?></span>
            </div>
            <div class="info-item">
                <strong>Client:</strong>
                <span><?= htmlspecialchars($order['client_name'] ?? 'N/A') ?></span>
            </div>
            <div class="info-item">
                <strong>Statut:</strong>
                <span class="badge" style="background-color: <?= htmlspecialchars($order['status_color'] ?? '#95a5a6') ?>;">
                    <?= htmlspecialchars($order['status_label'] ?? 'N/A') ?>
                </span>
            </div>
            <div class="info-item">
                <strong>Date de commande:</strong>
                <span><?= date('d/m/Y', strtotime($order['order_date'])) ?></span>
            </div>
            <div class="info-item">
                <strong>Date souhaitée:</strong>
                <span><?= $order['desired_date'] ? date('d/m/Y', strtotime($order['desired_date'])) : 'N/A' ?></span>
            </div>
            <div class="info-item">
                <strong>Technicien assigné:</strong>
                <span>
                    <?php if ($order['technician_first_name']): ?>
                        <?= htmlspecialchars($order['technician_first_name'] . ' ' . $order['technician_last_name']) ?>
                    <?php else: ?>
                        Non assigné
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Description -->
    <?php if (!empty($order['description'])): ?>
    <div class="card">
        <h2>Description</h2>
        <p><?= nl2br(htmlspecialchars($order['description'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- Notes -->
    <?php if (!empty($order['notes'])): ?>
    <div class="card">
        <h2>Notes internes</h2>
        <p><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- Timeline des événements -->
    <div class="card full-width">
        <h2>Historique (<?= count($events ?? []) ?> événements)</h2>

        <?php if (!empty($events)): ?>
        <div class="timeline">
            <?php foreach ($events as $event): ?>
            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <strong><?= htmlspecialchars($event['event_type'] ?? 'Événement') ?></strong>
                        <span class="timeline-date"><?= date('d/m/Y H:i', strtotime($event['timestamp'])) ?></span>
                    </div>
                    <?php if (!empty($event['description'])): ?>
                    <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($event['first_name'])): ?>
                    <small class="timeline-user">
                        Par: <?= htmlspecialchars($event['first_name'] . ' ' . $event['last_name']) ?>
                    </small>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="no-data">Aucun événement enregistré</p>
        <?php endif; ?>
    </div>

    <!-- Actions rapides -->
    <div class="card full-width">
        <h2>Actions</h2>
        <div class="action-buttons">
            <a href="/orders/<?= $order['id'] ?>/messages" class="btn">💬 Messages</a>
            <a href="/reports?order_id=<?= $order['id'] ?>" class="btn">📄 Rapports</a>
            <?php if ($can_edit): ?>
            <a href="/interventions?order_id=<?= $order['id'] ?>" class="btn">🔧 Interventions</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.page-actions {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
}

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
    color: #2c3e50;
}

.card p {
    line-height: 1.6;
    color: #2c3e50;
    margin: 0;
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
    color: #2c3e50;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    color: white;
    font-size: 0.85em;
    font-weight: 500;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -26px;
    top: 5px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #3498db;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #3498db;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border-left: 3px solid #3498db;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.timeline-header strong {
    color: #2c3e50;
    font-size: 1.05em;
}

.timeline-date {
    color: #666;
    font-size: 0.9em;
}

.timeline-content p {
    margin: 8px 0;
    color: #2c3e50;
}

.timeline-user {
    display: block;
    margin-top: 8px;
    color: #666;
    font-style: italic;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.no-data {
    text-align: center;
    padding: 20px;
    color: #999;
}

@media (max-width: 768px) {
    .details-grid {
        grid-template-columns: 1fr;
    }

    .timeline-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
