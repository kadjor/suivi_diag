<h1>Tableau de bord - Secrétariat</h1>

<div class="dashboard-actions">
    <a href="/orders/create" class="btn btn-primary">Nouvelle commande</a>
    <a href="/calendar" class="btn btn-secondary">Planning</a>
</div>

<div class="dashboard-content">
    <section>
        <h2>Commandes en attente AR</h2>
        <table class="data-table">
            <thead>
                <tr><th>Référence</th><th>Client</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($pending_orders ?? [] as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['reference']) ?></td>
                    <td><?= htmlspecialchars($order['client_name']) ?></td>
                    <td><?= htmlspecialchars($order['created_at']) ?></td>
                    <td><a href="/orders/<?= $order['id'] ?>" class="btn btn-sm">Traiter</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
