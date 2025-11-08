<div class="page-header">
    <div class="page-header-content">
        <h1>📄 Rapports de diagnostic</h1>
        <div class="breadcrumb">
            <a href="/dashboard">Tableau de bord</a>
            <span>/</span>
            <span class="current">Rapports</span>
        </div>
    </div>
</div>

<!-- Statistiques -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
            <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
            <div class="stat-label">Rapports totaux</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💾</div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format(($stats['total_size'] ?? 0) / 1048576, 2) ?> Mo</div>
            <div class="stat-label">Espace utilisé</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-content">
            <div class="stat-value"><?= $stats['orders_with_reports'] ?? 0 ?></div>
            <div class="stat-label">Commandes avec rapports</div>
        </div>
    </div>
</div>

<!-- Barre de recherche -->
<div class="search-bar-container">
    <form method="GET" action="/reports" class="search-form">
        <div class="search-input-group">
            <input type="text"
                   name="search"
                   class="search-input"
                   placeholder="Rechercher par nom de fichier, commande, client..."
                   value="<?= htmlspecialchars($searchTerm ?? '') ?>">
            <button type="submit" class="btn btn-primary">
                🔍 Rechercher
            </button>
            <?php if (!empty($searchTerm)): ?>
                <a href="/reports" class="btn btn-secondary">
                    ✕ Effacer
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Liste des rapports -->
<div class="card">
    <div class="card-header">
        <h2>📋 Liste des rapports
            <?php if (!empty($searchTerm)): ?>
                <small>(Résultats pour "<?= htmlspecialchars($searchTerm) ?>")</small>
            <?php endif; ?>
        </h2>
    </div>
    <div class="card-body no-padding">
        <?php if (!empty($reports)): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Fichier</th>
                        <th>Taille</th>
                        <th>Téléversé par</th>
                        <th>Date</th>
                        <th>📥</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                    <tr>
                        <td>
                            <a href="/orders/<?= $report['order_id'] ?>" class="link-primary">
                                <?= htmlspecialchars($report['order_number'] ?? 'N/A') ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($report['client_name'] ?? 'N/A') ?></td>
                        <td>
                            <div class="file-info">
                                <span class="file-icon">📄</span>
                                <span class="file-name"><?= htmlspecialchars($report['file_name'] ?? $report['filename'] ?? 'N/A') ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="file-size"><?= number_format(($report['file_size'] ?? 0) / 1024, 2) ?> Ko</span>
                        </td>
                        <td>
                            <?= htmlspecialchars(trim(($report['uploader_first_name'] ?? '') . ' ' . ($report['uploader_last_name'] ?? ''))) ?: 'N/A' ?>
                        </td>
                        <td>
                            <div class="date-display">
                                <?= date('d/m/Y', strtotime($report['uploaded_at'])) ?>
                                <small><?= date('H:i', strtotime($report['uploaded_at'])) ?></small>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="download-count"><?= $report['download_count'] ?? 0 ?></span>
                        </td>
                        <td>
                            <a href="/reports/<?= $report['id'] ?>/download" class="btn btn-sm btn-primary">
                                ⬇️ Télécharger
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>Aucun rapport trouvé</h3>
            <p>
                <?php if (!empty($searchTerm)): ?>
                    Aucun résultat pour votre recherche. Essayez avec d'autres mots-clés.
                <?php else: ?>
                    Les rapports téléversés apparaîtront ici.
                <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header {
    background: white;
    padding: 20px;
    margin: -20px -20px 20px;
    border-bottom: 1px solid #e0e0e0;
}

.page-header-content h1 {
    margin: 0 0 10px 0;
    font-size: 24px;
    color: #2c3e50;
}

.breadcrumb {
    font-size: 14px;
    color: #666;
}

.breadcrumb a {
    color: #3498db;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.breadcrumb span.current {
    color: #95a5a6;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    font-size: 32px;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
}

.stat-label {
    font-size: 14px;
    color: #7f8c8d;
    margin-top: 5px;
}

.search-bar-container {
    margin-bottom: 20px;
}

.search-form {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-input-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.search-input {
    flex: 1;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
}

.card-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.card-header small {
    opacity: 0.9;
    font-size: 14px;
    font-weight: normal;
}

.card-body {
    padding: 25px;
}

.card-body.no-padding {
    padding: 0;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f8f9fa;
}

.data-table th {
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #e0e0e0;
    font-size: 14px;
}

.data-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
}

.data-table tbody tr:hover {
    background: #f8f9fa;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.file-icon {
    font-size: 20px;
}

.file-name {
    color: #2c3e50;
    font-weight: 500;
}

.file-size {
    color: #7f8c8d;
    font-size: 13px;
}

.date-display {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.date-display small {
    color: #95a5a6;
    font-size: 12px;
}

.download-count {
    display: inline-block;
    background: #ecf0f1;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: #7f8c8d;
}

.text-center {
    text-align: center;
}

.link-primary {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
}

.link-primary:hover {
    text-decoration: underline;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #2c3e50;
    margin-bottom: 10px;
}

.empty-state p {
    color: #7f8c8d;
    max-width: 400px;
    margin: 0 auto;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: 1fr;
    }

    .search-input-group {
        flex-direction: column;
    }

    .search-input {
        width: 100%;
    }

    .table-responsive {
        font-size: 12px;
    }

    .data-table th,
    .data-table td {
        padding: 8px 10px;
    }
}
</style>
