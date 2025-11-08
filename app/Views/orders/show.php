<div class="page-header">
    <div class="page-header-content">
        <h1>📦 Commande #<?= htmlspecialchars($order['order_number']) ?></h1>
        <div class="status-badge-large" style="background-color: <?= htmlspecialchars($order['status_color'] ?? '#95a5a6') ?>;">
            <?= htmlspecialchars($order['status_label'] ?? 'N/A') ?>
        </div>
    </div>
    <div class="page-actions">
        <a href="/orders" class="btn btn-secondary">← Retour</a>
        <?php if ($can_edit): ?>
        <a href="/orders/<?= $order['id'] ?>/edit" class="btn btn-primary">✏️ Modifier</a>
        <?php endif; ?>
    </div>
</div>

<!-- Navigation par onglets -->
<div class="tabs-container">
    <div class="tabs">
        <button class="tab-btn active" data-tab="details">📋 Détails</button>
        <button class="tab-btn" data-tab="reports">📄 Rapports (<?= count($reports ?? []) ?>)</button>
        <button class="tab-btn" data-tab="interventions">🔧 Interventions (<?= count($interventions ?? []) ?>)</button>
        <button class="tab-btn" data-tab="timeline">⏱️ Historique</button>
    </div>
</div>

<!-- Tab content continues... -->
<!-- For brevity, adding simplified version -->
<div class="tab-content active" id="tab-details">
    <div class="card">
        <div class="card-header"><h2>Informations</h2></div>
        <div class="card-body">
            <p><strong>Client:</strong> <?= htmlspecialchars($order['client_name'] ?? 'N/A') ?></p>
            <p><strong>Site:</strong> <?= htmlspecialchars($order['site_name'] ?? 'N/A') ?></p>
            <p><strong>Date création:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
        </div>
    </div>
</div>

<div class="tab-content" id="tab-reports">
    <div class="card">
        <div class="card-header"><h2>📄 Rapports</h2></div>
        <div class="card-body">
            <?php if (!empty($reports)): ?>
                <?php foreach ($reports as $report): ?>
                    <div class="report-item">
                        📄 <?= htmlspecialchars($report['file_name'] ?? 'Rapport') ?>
                        <a href="/reports/<?= $report['id'] ?>/download" class="btn btn-sm">Télécharger</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun rapport</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="tab-content" id="tab-interventions">
    <div class="card">
        <div class="card-header"><h2>🔧 Interventions</h2></div>
        <div class="card-body">
            <?php if (!empty($interventions)): ?>
                <?php foreach ($interventions as $intervention): ?>
                    <div class="intervention-item">
                        🔧 <?= date('d/m/Y', strtotime($intervention['scheduled_date'])) ?>
                        - <?= htmlspecialchars($intervention['type'] ?? 'Intervention') ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune intervention</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="tab-content" id="tab-timeline">
    <div class="card">
        <div class="card-header"><h2>⏱️ Historique</h2></div>
        <div class="card-body">
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                    <div class="timeline-item">
                        <strong><?= htmlspecialchars($event['event_type'] ?? 'Événement') ?></strong>
                        <span><?= date('d/m/Y H:i', strtotime($event['timestamp'])) ?></span>
                        <?php if (!empty($event['description'])): ?>
                        <p><?= htmlspecialchars($event['description']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun événement</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabName = btn.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');
    });
});
</script>
