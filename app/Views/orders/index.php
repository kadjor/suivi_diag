<h1>Commandes</h1>

<div class="page-actions">
    <a href="/orders/create" class="btn btn-primary">Nouvelle commande</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Référence</th>
            <th>Client</th>
            <th>Site</th>
            <th>Statut</th>
            <th>Priorité</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders ?? [] as $order): ?>
        <tr>
            <td><a href="/orders/<?= $order['id'] ?>"><?= htmlspecialchars($order['order_number']) ?></a></td>
            <td><?= htmlspecialchars($order['client_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($order['site_name'] ?? '-') ?></td>
            <td><span class="badge" style="background-color: <?= $order['status_color'] ?? '#999' ?>"><?= htmlspecialchars($order['status_label'] ?? 'N/A') ?></span></td>
            <td><?= htmlspecialchars($order['priority'] ?? 'normal') ?></td>
            <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
            <td>
                <a href="/orders/<?= $order['id'] ?>" class="btn btn-sm">Voir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
