<h1>Tableau de bord - Administrateur</h1>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Commandes totales</h3>
        <p class="stat-number"><?= $total_orders ?? 0 ?></p>
    </div>
    <div class="stat-card">
        <h3>En attente</h3>
        <p class="stat-number"><?= $pending_orders ?? 0 ?></p>
    </div>
    <div class="stat-card">
        <h3>En cours</h3>
        <p class="stat-number"><?= $in_progress_orders ?? 0 ?></p>
    </div>
    <div class="stat-card">
        <h3>Terminées</h3>
        <p class="stat-number"><?= $completed_orders ?? 0 ?></p>
    </div>
</div>

<div class="dashboard-content">
    <div class="recent-orders">
        <h2>Commandes récentes</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Client</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders ?? [] as $order): ?>
                <tr>
                    <td><a href="/orders/<?= $order['id'] ?>"><?= htmlspecialchars($order['order_number']) ?></a></td>
                    <td><?= htmlspecialchars($order['client_name'] ?? 'N/A') ?></td>
                    <td><span class="badge" style="background-color: <?= $order['status_color'] ?? '#999' ?>"><?= htmlspecialchars($order['status_label'] ?? 'N/A') ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
