<h1>Sites</h1>

<div class="page-actions">
    <a href="/sites/create" class="btn btn-primary">Nouveau site</a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Client</th>
            <th>Adresse</th>
            <th>Ville</th>
            <th>Diagnostics</th>
            <th>Dernier diagnostic</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sites ?? [] as $site): ?>
        <tr>
            <td><a href="/sites/<?= $site['id'] ?>"><?= htmlspecialchars($site['name']) ?></a></td>
            <td><?= htmlspecialchars($site['client_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($site['address'] ?? '') ?></td>
            <td><?= htmlspecialchars($site['city'] ?? '') ?></td>
            <td><?= $site['diagnostics_count'] ?? 0 ?></td>
            <td><?= $site['last_diagnostic_date'] ? date('d/m/Y', strtotime($site['last_diagnostic_date'])) : '-' ?></td>
            <td>
                <a href="/sites/<?= $site['id'] ?>" class="btn btn-sm">Voir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if (empty($sites)): ?>
<p class="no-data">Aucun site enregistré</p>
<?php endif; ?>
