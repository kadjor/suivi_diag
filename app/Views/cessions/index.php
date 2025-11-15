<div class="page-header">
    <h1>Gestion des Cessions</h1>
    <p class="subtitle">Transfert de sites entre clients</p>
</div>

<div class="page-actions">
    <?php if (Auth::can('create_cessions')): ?>
    <a href="/cessions/create" class="btn btn-primary">
        <i class="icon-plus"></i> Nouvelle cession
    </a>
    <?php endif; ?>
</div>

<!-- Statistiques -->
<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-label">Brouillons</div>
        <div class="stat-value"><?= $stats['draft'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">En attente</div>
        <div class="stat-value"><?= $stats['pending_validation'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Validées</div>
        <div class="stat-value"><?= $stats['validated'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">En cours</div>
        <div class="stat-value"><?= $stats['in_progress'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Terminées</div>
        <div class="stat-value"><?= $stats['completed'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Annulées</div>
        <div class="stat-value"><?= $stats['cancelled'] ?? 0 ?></div>
    </div>
</div>

<!-- Filtres -->
<div class="filters" style="margin-bottom: 20px;">
    <form method="GET" action="/cessions" style="display: flex; gap: 10px; align-items: center;">
        <label for="status">Filtrer par statut :</label>
        <select name="status" id="status" onchange="this.form.submit()" style="padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
            <option value="">Tous les statuts</option>
            <option value="draft" <?= ($current_status ?? '') === 'draft' ? 'selected' : '' ?>>Brouillon</option>
            <option value="pending_validation" <?= ($current_status ?? '') === 'pending_validation' ? 'selected' : '' ?>>En attente</option>
            <option value="validated" <?= ($current_status ?? '') === 'validated' ? 'selected' : '' ?>>Validée</option>
            <option value="in_progress" <?= ($current_status ?? '') === 'in_progress' ? 'selected' : '' ?>>En cours</option>
            <option value="completed" <?= ($current_status ?? '') === 'completed' ? 'selected' : '' ?>>Terminée</option>
            <option value="cancelled" <?= ($current_status ?? '') === 'cancelled' ? 'selected' : '' ?>>Annulée</option>
        </select>
    </form>
</div>

<!-- Table des cessions -->
<table class="data-table">
    <thead>
        <tr>
            <th>Numéro</th>
            <th>Titre</th>
            <th>Client cédant</th>
            <th>Client cessionnaire</th>
            <th>Sites</th>
            <th>Statut</th>
            <th>Date création</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($cessions)): ?>
        <tr>
            <td colspan="8" style="text-align: center; padding: 40px;">
                <p style="color: #999;">Aucune cession trouvée</p>
                <?php if (Auth::can('create_cessions')): ?>
                <a href="/cessions/create" class="btn btn-primary" style="margin-top: 10px;">Créer une cession</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php else: ?>
        <?php foreach ($cessions as $cession): ?>
        <tr>
            <td>
                <a href="/cessions/<?= $cession['id'] ?>" style="font-weight: bold;">
                    <?= htmlspecialchars($cession['cession_number']) ?>
                </a>
            </td>
            <td><?= htmlspecialchars($cession['title']) ?></td>
            <td><?= htmlspecialchars($cession['from_client_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($cession['to_client_name'] ?? 'N/A') ?></td>
            <td>
                <span title="<?= $cession['transferred_sites'] ?> transférés sur <?= $cession['total_sites'] ?>">
                    <?= $cession['transferred_sites'] ?> / <?= $cession['total_sites'] ?>
                </span>
            </td>
            <td>
                <?php
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
                    'pending_validation' => 'En attente',
                    'validated' => 'Validée',
                    'in_progress' => 'En cours',
                    'completed' => 'Terminée',
                    'cancelled' => 'Annulée'
                ];
                $status = $cession['status'];
                $color = $statusColors[$status] ?? '#95a5a6';
                $label = $statusLabels[$status] ?? $status;
                ?>
                <span class="badge" style="background-color: <?= $color ?>; color: white; padding: 5px 10px; border-radius: 3px;">
                    <?= htmlspecialchars($label) ?>
                </span>
            </td>
            <td><?= date('d/m/Y', strtotime($cession['created_at'])) ?></td>
            <td>
                <a href="/cessions/<?= $cession['id'] ?>" class="btn btn-sm">Voir</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.stat-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.stat-label {
    font-size: 14px;
    color: #666;
    margin-bottom: 8px;
}

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #333;
}

.page-header {
    margin-bottom: 30px;
}

.page-header .subtitle {
    color: #666;
    font-size: 14px;
    margin-top: 5px;
}
</style>
