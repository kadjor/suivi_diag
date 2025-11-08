<h1>Gestion des référentiels</h1>

<div class="page-actions">
    <a href="/admin" class="btn">Retour à l'administration</a>
</div>

<div class="referentials-container">
    <!-- Roles -->
    <div class="card">
        <h2>Rôles utilisateurs</h2>

        <?php if (!empty($roles)): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Label</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?= $role['id'] ?></td>
                    <td><code><?= htmlspecialchars($role['name']) ?></code></td>
                    <td><?= htmlspecialchars($role['label']) ?></td>
                    <td><?= htmlspecialchars($role['description'] ?? 'N/A') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">Aucun rôle trouvé</p>
        <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="card">
        <h2>À propos des référentiels</h2>
        <p>Les référentiels contiennent les données de base utilisées par l'application :</p>
        <ul>
            <li><strong>Rôles :</strong> Définissent les niveaux d'accès et permissions des utilisateurs</li>
            <li><strong>Statuts :</strong> États possibles pour les commandes et interventions (définis en base)</li>
            <li><strong>Types de diagnostics :</strong> Catégories de diagnostics immobiliers (définis en base)</li>
        </ul>
        <p class="info-box">
            ℹ️ La modification des référentiels peut impacter le fonctionnement de l'application.
            Veuillez procéder avec précaution.
        </p>
    </div>
</div>

<style>
.page-actions {
    margin-bottom: 20px;
}

.referentials-container {
    display: grid;
    gap: 20px;
}

.card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
}

.card h2 {
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
    color: #2c3e50;
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

.data-table code {
    padding: 2px 6px;
    background: #f8f9fa;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

.no-data {
    text-align: center;
    padding: 40px;
    color: #999;
}

.card p {
    line-height: 1.6;
    color: #2c3e50;
}

.card ul {
    line-height: 1.8;
    color: #2c3e50;
}

.info-box {
    margin-top: 20px;
    padding: 15px;
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    border-radius: 4px;
}
</style>
