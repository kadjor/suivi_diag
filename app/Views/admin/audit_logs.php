<h1>Journal d'audit</h1>

<div class="page-actions">
    <a href="/admin" class="btn">Retour à l'administration</a>
</div>

<?php if (!empty($logs)): ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Utilisateur</th>
            <th>Action</th>
            <th>Table</th>
            <th>ID Enregistrement</th>
            <th>Détails</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
            <td><?= htmlspecialchars($log['username'] ?? 'Système') ?></td>
            <td>
                <?php
                $actionColors = [
                    'create' => '#27ae60',
                    'update' => '#f39c12',
                    'delete' => '#e74c3c',
                    'login' => '#3498db',
                    'logout' => '#95a5a6'
                ];
                $color = $actionColors[$log['action']] ?? '#34495e';
                ?>
                <span class="badge" style="background-color: <?= $color ?>;">
                    <?= htmlspecialchars($log['action']) ?>
                </span>
            </td>
            <td><?= htmlspecialchars($log['table_name'] ?? 'N/A') ?></td>
            <td><?= $log['record_id'] ?? 'N/A' ?></td>
            <td>
                <?php if (!empty($log['details'])): ?>
                <details>
                    <summary>Voir détails</summary>
                    <pre><?= htmlspecialchars($log['details']) ?></pre>
                </details>
                <?php else: ?>
                N/A
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p class="no-data">Aucun log d'audit trouvé</p>
<?php endif; ?>

<style>
.page-actions {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.data-table th,
.data-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.data-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    color: white;
    font-size: 0.85em;
    font-weight: 500;
}

details summary {
    cursor: pointer;
    color: #3498db;
    user-select: none;
}

details pre {
    margin-top: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 0.85em;
    overflow-x: auto;
}

.no-data {
    text-align: center;
    padding: 40px;
    color: #999;
    background: white;
    border-radius: 8px;
}
</style>
