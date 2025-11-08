<h1>Gestion des utilisateurs</h1>

<div class="page-actions">
    <a href="/admin" class="btn">Retour à l'administration</a>
    <a href="/users/create" class="btn btn-primary">Ajouter un utilisateur</a>
</div>

<?php if (!empty($users)): ?>
<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom d'utilisateur</th>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Client</th>
            <th>Actif</th>
            <th>Dernière connexion</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
                <span class="badge" style="background-color: #3498db;">
                    <?= htmlspecialchars($user['role_label'] ?? $user['role_name'] ?? 'N/A') ?>
                </span>
            </td>
            <td><?= htmlspecialchars($user['client_name'] ?? 'N/A') ?></td>
            <td>
                <?php if ($user['active']): ?>
                    <span class="badge" style="background-color: #27ae60;">Actif</span>
                <?php else: ?>
                    <span class="badge" style="background-color: #95a5a6;">Inactif</span>
                <?php endif; ?>
            </td>
            <td><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais' ?></td>
            <td>
                <a href="/users/<?= $user['id'] ?>/edit" class="btn btn-sm">Modifier</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p class="no-data">Aucun utilisateur trouvé</p>
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

.no-data {
    text-align: center;
    padding: 40px;
    color: #999;
    background: white;
    border-radius: 8px;
}
</style>
