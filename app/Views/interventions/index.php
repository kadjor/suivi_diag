<h1>Interventions</h1>

<div class="page-actions">
    <a href="/dashboard" class="btn">Retour au tableau de bord</a>
</div>

<?php if (!empty($interventions)): ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Commande</th>
            <th>Site</th>
            <th>Technicien</th>
            <th>Date prévue</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($interventions as $intervention): ?>
        <tr>
            <td>
                <a href="/orders/<?= $intervention['order_id'] ?>">
                    <?= htmlspecialchars($intervention['order_number'] ?? 'N/A') ?>
                </a>
            </td>
            <td><?= htmlspecialchars($intervention['site_name'] ?? 'N/A') ?></td>
            <td>
                <?= htmlspecialchars(($intervention['tech_first_name'] ?? '') . ' ' . ($intervention['tech_last_name'] ?? '')) ?>
            </td>
            <td><?= $intervention['scheduled_date'] ? date('d/m/Y', strtotime($intervention['scheduled_date'])) : 'N/A' ?></td>
            <td>
                <?php
                $statusColors = [
                    'scheduled' => '#3498db',
                    'in_progress' => '#f39c12',
                    'completed' => '#27ae60',
                    'cancelled' => '#95a5a6'
                ];
                $color = $statusColors[$intervention['status']] ?? '#34495e';
                ?>
                <span class="badge" style="background-color: <?= $color ?>;">
                    <?= htmlspecialchars($intervention['status']) ?>
                </span>
            </td>
            <td>
                <button class="btn btn-sm" onclick="updateStatus(<?= $intervention['id'] ?>)">
                    Changer statut
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p class="no-data">Aucune intervention trouvée</p>
<?php endif; ?>

<script>
function updateStatus(interventionId) {
    const status = prompt('Nouveau statut (scheduled, in_progress, completed, cancelled):');
    if (status) {
        fetch('/interventions/update-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `intervention_id=${interventionId}&status=${status}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Statut mis à jour');
                location.reload();
            } else {
                alert('Erreur: ' + data.error);
            }
        });
    }
}
</script>
