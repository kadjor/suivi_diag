<h1>Gestion des clients (Admin)</h1>

<div class="page-actions">
    <a href="/admin" class="btn">Retour à l'administration</a>
    <a href="/clients/create" class="btn btn-primary">Ajouter un client</a>
</div>

<?php if (!empty($clients)): ?>
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
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clients as $client): ?>
        <tr>
            <td><?= htmlspecialchars($client['organization_name']) ?></td>
            <td><?= htmlspecialchars($client['contact_first_name'] . ' ' . $client['contact_last_name']) ?></td>
            <td><?= htmlspecialchars($client['contact_email']) ?></td>
            <td><?= htmlspecialchars($client['contact_phone']) ?></td>
            <td><?= htmlspecialchars($client['city']) ?></td>
            <td><?= $client['sites_count'] ?? 0 ?></td>
            <td><?= $client['orders_count'] ?? 0 ?></td>
            <td>
                <?php if ($client['active']): ?>
                    <span class="badge" style="background-color: #27ae60;">Actif</span>
                <?php else: ?>
                    <span class="badge" style="background-color: #95a5a6;">Inactif</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="/clients/<?= $client['id'] ?>" class="btn btn-sm">Voir</a>
                <a href="/clients/<?= $client['id'] ?>/edit" class="btn btn-sm">Modifier</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p class="no-data">Aucun client trouvé</p>
<?php endif; ?>
