<h1>Gestion des utilisateurs</h1>

<div class="page-actions">
    <a href="/dashboard" class="btn">Retour au tableau de bord</a>
    <a href="/users/create" class="btn btn-primary">Ajouter un utilisateur</a>
</div>

<?php if (!empty($users)): ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Nom d'utilisateur</th>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Client</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
                <span class="badge" style="background-color: #3498db;">
                    <?= htmlspecialchars($user['role_label'] ?? $user['role_name'] ?? 'N/A') ?>
                </span>
            </td>
            <td><?= htmlspecialchars($user['client_name'] ?? '-') ?></td>
            <td>
                <?php if ($user['active']): ?>
                    <span class="badge" style="background-color: #27ae60;">Actif</span>
                <?php else: ?>
                    <span class="badge" style="background-color: #95a5a6;">Inactif</span>
                <?php endif; ?>
            </td>
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
