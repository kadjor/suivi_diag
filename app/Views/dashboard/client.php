<h1>Tableau de bord - Client</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php else: ?>

<div class="dashboard-content">
    <section class="my-orders">
        <h2>Mes commandes</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Site</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($my_orders ?? [] as $order): ?>
                <tr>
                    <td><a href="/orders/<?= $order['id'] ?>"><?= htmlspecialchars($order['reference']) ?></a></td>
                    <td><?= htmlspecialchars($order['site_name']) ?></td>
                    <td><span class="status-badge <?= $order['status'] ?>"><?= htmlspecialchars($order['status']) ?></span></td>
                    <td><?= htmlspecialchars($order['created_at']) ?></td>
                    <td><a href="/orders/<?= $order['id'] ?>" class="btn btn-sm">Voir</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="quick-links">
        <h2>Accès rapide</h2>
        <div class="link-grid">
            <a href="/map" class="quick-link-card">
                <h3>Cartographie</h3>
                <p>Voir mes sites sur la carte</p>
            </a>
            <a href="/reports" class="quick-link-card">
                <h3>Rapports</h3>
                <p>Télécharger mes rapports</p>
            </a>
            <a href="/sites" class="quick-link-card">
                <h3>Mes sites</h3>
                <p>Gérer mes sites</p>
            </a>
        </div>
    </section>
</div>

<?php endif; ?>
