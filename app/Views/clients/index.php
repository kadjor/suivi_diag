<h1>Clients</h1>

<div class="page-actions">
    <a href="/clients/create" class="btn btn-primary">Nouveau client</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Organisation</th>
            <th>Contact</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Ville</th>
            <th>Sites</th>
            <th>Commandes</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clients ?? [] as $client): ?>
        <tr>
            <td><a href="/clients/<?= $client['id'] ?>"><?= htmlspecialchars($client['organization_name']) ?></a></td>
            <td><?= htmlspecialchars($client['contact_name']) ?></td>
            <td><?= htmlspecialchars($client['email']) ?></td>
            <td><?= htmlspecialchars($client['phone'] ?? '-') ?></td>
            <td><?= htmlspecialchars($client['city'] ?? '') ?></td>
            <td><?= $client['sites_count'] ?? 0 ?></td>
            <td><?= $client['orders_count'] ?? 0 ?></td>
            <td>
                <a href="/clients/<?= $client['id'] ?>" class="btn btn-sm">Voir</a>
                <a href="/clients/<?= $client['id'] ?>/edit" class="btn btn-sm">Modifier</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if (empty($clients)): ?>
<p class="no-data">Aucun client enregistré</p>
<?php endif; ?>
