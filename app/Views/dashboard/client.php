<h1>🏠 Mon Espace Client</h1>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php else: ?>

<div class="dashboard-grid">
    <!-- Section: Statistiques rapides -->
    <div class="stats-section">
        <div class="stat-card">
            <div class="stat-icon">🏘️</div>
            <div class="stat-info">
                <div class="stat-value"><?= count($my_sites ?? []) ?></div>
                <div class="stat-label">Sites</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-info">
                <div class="stat-value"><?= count($my_orders ?? []) ?></div>
                <div class="stat-label">Commandes</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-info">
                <div class="stat-value"><?= count($my_diagnostics ?? []) ?></div>
                <div class="stat-label">Diagnostics</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📄</div>
            <div class="stat-info">
                <div class="stat-value"><?= count($pending_reports ?? []) ?></div>
                <div class="stat-label">Rapports en attente</div>
            </div>
        </div>
    </div>

    <!-- Section: Actions rapides -->
    <div class="quick-actions-section">
        <h2>⚡ Actions rapides</h2>
        <div class="action-grid">
            <a href="/orders/create" class="action-card action-primary">
                <div class="action-icon">➕</div>
                <div class="action-content">
                    <h3>Nouvelle commande</h3>
                    <p>Passer une nouvelle commande de diagnostic</p>
                </div>
            </a>

            <a href="#patrimoine" onclick="scrollToSection('patrimoine')" class="action-card">
                <div class="action-icon">🗺️</div>
                <div class="action-content">
                    <h3>Mon patrimoine</h3>
                    <p>Voir mes sites et leur localisation</p>
                </div>
            </a>

            <a href="#commandes" onclick="scrollToSection('commandes')" class="action-card">
                <div class="action-icon">📦</div>
                <div class="action-content">
                    <h3>Mes commandes</h3>
                    <p>Suivre l'état de mes commandes</p>
                </div>
            </a>

            <a href="#diagnostics" onclick="scrollToSection('diagnostics')" class="action-card">
                <div class="action-icon">🔍</div>
                <div class="action-content">
                    <h3>Mes diagnostics</h3>
                    <p>Consulter mes diagnostics réalisés</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Section: Mes commandes -->
    <div class="section-card" id="commandes">
        <div class="section-header">
            <h2>📦 Mes commandes récentes</h2>
            <a href="/orders" class="btn btn-sm">Voir tout</a>
        </div>

        <?php if (!empty($my_orders)): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Site / Adresse</th>
                        <th>Lot</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_orders as $order): ?>
                    <tr>
                        <td>
                            <a href="/orders/<?= $order['id'] ?>" class="order-link">
                                <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                            </a>
                        </td>
                        <td>
                            <?php if ($order['site_name']): ?>
                                <div class="site-info">
                                    <strong><?= htmlspecialchars($order['site_name']) ?></strong>
                                    <small><?= htmlspecialchars($order['site_address'] ?? '') ?></small>
                                </div>
                            <?php else: ?>
                                <div class="site-info">
                                    <span><?= htmlspecialchars($order['execution_address'] ?? 'N/A') ?></span>
                                    <small><?= htmlspecialchars($order['execution_city'] ?? '') ?></small>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($order['numero_lot'] ?? 'N/A') ?></td>
                        <td>
                            <span class="status-badge" style="background-color: <?= htmlspecialchars($order['status_color'] ?? '#ccc') ?>">
                                <?= htmlspecialchars($order['status_label'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                        <td>
                            <a href="/orders/<?= $order['id'] ?>" class="btn btn-sm">Voir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <p>Aucune commande pour le moment</p>
            <a href="/orders/create" class="btn btn-primary">Passer ma première commande</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Section: Cartographie du patrimoine -->
    <div class="section-card" id="patrimoine">
        <div class="section-header">
            <h2>🗺️ Cartographie de mon patrimoine</h2>
            <a href="/sites" class="btn btn-sm">Voir la liste complète</a>
        </div>

        <?php if (!empty($my_sites)): ?>
        <div class="map-container">
            <div id="patrimoineMap" style="height: 400px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <div style="text-align: center; color: #666;">
                    <div style="font-size: 48px; margin-bottom: 10px;">🗺️</div>
                    <p>Carte interactive du patrimoine</p>
                    <small><?= count($my_sites) ?> site(s) enregistré(s)</small>
                </div>
            </div>

            <div class="sites-summary">
                <h3>Répartition par ville</h3>
                <div class="city-list">
                    <?php
                    $cities = [];
                    foreach ($my_sites as $site) {
                        $city = $site['city'] ?? 'Non défini';
                        if (!isset($cities[$city])) {
                            $cities[$city] = 0;
                        }
                        $cities[$city]++;
                    }
                    arsort($cities);
                    foreach (array_slice($cities, 0, 5) as $city => $count):
                    ?>
                    <div class="city-item">
                        <span class="city-name">📍 <?= htmlspecialchars($city) ?></span>
                        <span class="city-count"><?= $count ?> site(s)</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🗺️</div>
            <p>Aucun site enregistré dans votre patrimoine</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Section: Diagnostics -->
    <div class="section-card" id="diagnostics">
        <div class="section-header">
            <h2>🔍 Mes diagnostics</h2>
        </div>

        <?php if (!empty($my_diagnostics)): ?>
        <div class="diagnostics-grid">
            <?php foreach (array_slice($my_diagnostics, 0, 6) as $diagnostic): ?>
            <div class="diagnostic-card">
                <div class="diagnostic-header">
                    <span class="diagnostic-type"><?= htmlspecialchars($diagnostic['type'] ?? 'Diagnostic') ?></span>
                    <span class="diagnostic-date"><?= date('d/m/Y', strtotime($diagnostic['created_at'])) ?></span>
                </div>
                <div class="diagnostic-body">
                    <p class="diagnostic-site"><?= htmlspecialchars($diagnostic['site_name'] ?? 'N/A') ?></p>
                    <p class="diagnostic-address"><?= htmlspecialchars($diagnostic['address'] ?? '') ?></p>
                </div>
                <div class="diagnostic-footer">
                    <?php if ($diagnostic['report_url'] ?? false): ?>
                        <a href="<?= htmlspecialchars($diagnostic['report_url']) ?>" class="btn btn-sm" target="_blank">
                            📄 Télécharger le rapport
                        </a>
                    <?php else: ?>
                        <span class="status-pending">En cours</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($my_diagnostics) > 6): ?>
        <div style="text-align: center; margin-top: 20px;">
            <a href="/diagnostics" class="btn">Voir tous les diagnostics (<?= count($my_diagnostics) ?>)</a>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <p>Aucun diagnostic réalisé pour le moment</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.dashboard-grid {
    display: flex;
    flex-direction: column;
    gap: 25px;
    padding-bottom: 30px;
}

/* Statistiques */
.stats-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.stat-card:nth-child(2) {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-card:nth-child(3) {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-card:nth-child(4) {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.stat-icon {
    font-size: 48px;
    opacity: 0.9;
}

.stat-value {
    font-size: 32px;
    font-weight: bold;
}

.stat-label {
    font-size: 14px;
    opacity: 0.9;
}

/* Actions rapides */
.quick-actions-section h2 {
    margin-bottom: 20px;
    color: #2c3e50;
}

.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.action-card {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    padding: 25px;
    display: flex;
    gap: 20px;
    align-items: center;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s;
}

.action-card:hover {
    border-color: #3498db;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
    transform: translateY(-3px);
}

.action-card.action-primary {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    border: none;
}

.action-card.action-primary:hover {
    box-shadow: 0 6px 16px rgba(52, 152, 219, 0.4);
}

.action-icon {
    font-size: 36px;
}

.action-content h3 {
    margin: 0 0 5px 0;
    font-size: 16px;
}

.action-content p {
    margin: 0;
    font-size: 13px;
    opacity: 0.8;
}

/* Sections */
.section-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.section-header h2 {
    margin: 0;
    color: #2c3e50;
}

/* Table */
.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #e0e0e0;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #f0f0f0;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.order-link {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
}

.order-link:hover {
    text-decoration: underline;
}

.site-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.site-info small {
    color: #7f8c8d;
    font-size: 12px;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: white;
}

/* Carte */
.map-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.sites-summary h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #2c3e50;
}

.city-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.city-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #3498db;
}

.city-count {
    font-weight: 600;
    color: #3498db;
}

/* Diagnostics */
.diagnostics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.diagnostic-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    transition: box-shadow 0.3s;
}

.diagnostic-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.diagnostic-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
}

.diagnostic-type {
    font-weight: 600;
    color: #3498db;
}

.diagnostic-date {
    font-size: 12px;
    color: #7f8c8d;
}

.diagnostic-body {
    margin-bottom: 15px;
}

.diagnostic-site {
    font-weight: 600;
    margin: 0 0 5px 0;
}

.diagnostic-address {
    font-size: 13px;
    color: #7f8c8d;
    margin: 0;
}

.diagnostic-footer {
    text-align: center;
}

.status-pending {
    color: #f39c12;
    font-size: 13px;
    font-style: italic;
}

/* États vides */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #95a5a6;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state p {
    margin: 0 0 20px 0;
    font-size: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-section {
        grid-template-columns: 1fr 1fr;
    }

    .action-grid {
        grid-template-columns: 1fr;
    }

    .map-container {
        grid-template-columns: 1fr;
    }

    .diagnostics-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function scrollToSection(sectionId) {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    return false;
}
</script>

<?php endif; ?>
