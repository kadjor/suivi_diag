<h1>Administration</h1>

<div class="admin-container">
    <!-- Quick Actions -->
    <div class="card">
        <h2>Actions rapides</h2>
        <div class="action-grid">
            <a href="/admin/users" class="action-card">
                <div class="action-icon">👥</div>
                <h3>Utilisateurs</h3>
                <p>Gérer les utilisateurs et leurs rôles</p>
            </a>

            <a href="/admin/referentials" class="action-card">
                <div class="action-icon">📋</div>
                <h3>Référentiels</h3>
                <p>Rôles et données de base</p>
            </a>

            <a href="/admin/statuses" class="action-card">
                <div class="action-icon">🎨</div>
                <h3>Statuts & Couleurs</h3>
                <p>Gérer les couleurs des statuts</p>
            </a>

            <a href="/admin/audit-logs" class="action-card">
                <div class="action-icon">📝</div>
                <h3>Journal d'audit</h3>
                <p>Consulter les logs d'activité</p>
            </a>

            <a href="/admin/settings" class="action-card">
                <div class="action-icon">⚙️</div>
                <h3>Paramètres</h3>
                <p>Configuration de l'application</p>
            </a>

            <a href="/deploy" class="action-card highlight">
                <div class="action-icon">🚀</div>
                <h3>Déploiement</h3>
                <p>Mise à jour depuis GitHub</p>
            </a>
        </div>
    </div>

    <!-- System Information -->
    <div class="card">
        <h2>Informations système</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Version PHP:</strong>
                <span><?= PHP_VERSION ?></span>
            </div>
            <div class="info-item">
                <strong>Serveur:</strong>
                <span><?= $_SERVER['SERVER_SOFTWARE'] ?? 'N/A' ?></span>
            </div>
            <div class="info-item">
                <strong>Base de données:</strong>
                <span>MySQL/MariaDB</span>
            </div>
            <div class="info-item">
                <strong>Timezone:</strong>
                <span><?= date_default_timezone_get() ?></span>
            </div>
        </div>
    </div>

    <!-- Data Management -->
    <div class="card">
        <h2>Gestion des données</h2>
        <div class="data-links">
            <a href="/clients" class="btn btn-sm">📁 Clients</a>
            <a href="/sites" class="btn btn-sm">🏢 Sites</a>
            <a href="/orders" class="btn btn-sm">📦 Commandes</a>
            <a href="/reports" class="btn btn-sm">📄 Rapports</a>
            <a href="/messages" class="btn btn-sm">💬 Messages</a>
        </div>
    </div>

    <!-- Quick Stats (if available) -->
    <?php
    // Try to get quick stats if models are available
    try {
        $userModel = new \Models\User();
        $clientModel = new \Models\Client();
        $orderModel = new \Models\Order();

        $totalUsers = $userModel->count();
        $totalClients = $clientModel->count();
        $totalOrders = $orderModel->count();
    } catch (\Exception $e) {
        // Stats not available
        $totalUsers = $totalClients = $totalOrders = null;
    }
    ?>

    <?php if ($totalUsers !== null): ?>
    <div class="card">
        <h2>Statistiques</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $totalUsers ?></div>
                <div class="stat-label">Utilisateurs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalClients ?></div>
                <div class="stat-label">Clients</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalOrders ?></div>
                <div class="stat-label">Commandes</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Important Links -->
    <div class="card full-width">
        <h2>Liens importants</h2>
        <ul class="links-list">
            <li><a href="/dashboard">🏠 Tableau de bord</a></li>
            <li><a href="/calendar">📅 Calendrier</a></li>
            <li><a href="/map">🗺️ Carte des sites</a></li>
            <li><a href="/profile">👤 Mon profil</a></li>
        </ul>
    </div>
</div>

<style>
.admin-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
}

.card.full-width {
    grid-column: 1 / -1;
}

.card h2 {
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
    color: #2c3e50;
}

/* Action Cards */
.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.action-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
}

.action-card:hover {
    background: #e9ecef;
    border-color: #3498db;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.action-card.highlight {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
}

.action-card.highlight h3,
.action-card.highlight p {
    color: white;
}

.action-card.highlight:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    border-color: #764ba2;
}

.action-icon {
    font-size: 48px;
    margin-bottom: 10px;
}

.action-card h3 {
    margin: 10px 0 5px 0;
    color: #2c3e50;
    font-size: 1.1em;
}

.action-card p {
    margin: 0;
    color: #666;
    font-size: 0.9em;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.info-item strong {
    display: block;
    margin-bottom: 5px;
    color: #666;
    font-size: 0.9em;
}

.info-item span {
    display: block;
    font-size: 1.05em;
    color: #2c3e50;
}

/* Data Links */
.data-links {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.data-links .btn {
    flex: 0 0 auto;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

.stat-number {
    font-size: 2.5em;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9em;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Links List */
.links-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
}

.links-list li {
    margin: 0;
}

.links-list a {
    display: block;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 4px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.2s ease;
}

.links-list a:hover {
    background: #e9ecef;
    color: #3498db;
    transform: translateX(5px);
}

@media (max-width: 768px) {
    .admin-container {
        grid-template-columns: 1fr;
    }

    .action-grid {
        grid-template-columns: 1fr;
    }
}
</style>
