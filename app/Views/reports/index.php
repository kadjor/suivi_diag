<?php
$user = \Core\Auth::user();
$isClient = ($user['role_name'] === 'client');
?>

<div class="reports-page">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <h1>📄 Rapports de diagnostic</h1>
            <p class="subtitle">
                <?php if ($isClient): ?>
                    Vos rapports de diagnostic
                <?php else: ?>
                    Tous les rapports de diagnostic
                <?php endif; ?>
            </p>
        </div>
        <div class="breadcrumb">
            <a href="/dashboard">Tableau de bord</a>
            <span>/</span>
            <span class="current">Rapports</span>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-container">
        <div class="stat-card blue">
            <div class="stat-icon">📊</div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
                <div class="stat-label"><?= $isClient ? 'Vos rapports' : 'Rapports totaux' ?></div>
            </div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon">💾</div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format(($stats['total_size'] ?? 0) / 1048576, 1) ?> Mo</div>
                <div class="stat-label">Espace utilisé</div>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon">📦</div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($stats['orders_with_reports'] ?? 0) ?></div>
                <div class="stat-label">Commandes avec rapports</div>
            </div>
        </div>
    </div>

    <!-- Barre de recherche -->
    <div class="search-section">
        <form method="GET" action="/reports" class="search-bar">
            <div class="search-icon">🔍</div>
            <input type="text"
                   name="search"
                   class="search-input"
                   placeholder="Rechercher par nom de fichier, numéro de commande, client..."
                   value="<?= htmlspecialchars($searchTerm ?? '') ?>"
                   autofocus>
            <?php if (!empty($searchTerm)): ?>
                <a href="/reports" class="clear-search" title="Effacer la recherche">✕</a>
            <?php endif; ?>
            <button type="submit" class="search-btn">Rechercher</button>
        </form>

        <?php if (!empty($searchTerm)): ?>
        <div class="search-info">
            <span class="search-term"><?= count($reports) ?> résultat(s) pour "<strong><?= htmlspecialchars($searchTerm) ?></strong>"</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Liste des rapports -->
    <div class="reports-container">
        <?php if (!empty($reports)): ?>
            <div class="reports-grid">
                <?php foreach ($reports as $report): ?>
                <div class="report-card">
                    <div class="report-header">
                        <div class="file-icon-large">📄</div>
                        <div class="report-title">
                            <h3 class="file-name"><?= htmlspecialchars($report['file_name'] ?? $report['filename'] ?? 'Rapport') ?></h3>
                            <div class="report-meta">
                                <span class="meta-item">
                                    <span class="meta-icon">🏢</span>
                                    <?= htmlspecialchars($report['client_name'] ?? 'N/A') ?>
                                </span>
                                <span class="meta-item">
                                    <span class="meta-icon">📋</span>
                                    <a href="/orders/<?= $report['order_id'] ?>" class="order-link">
                                        <?= htmlspecialchars($report['order_number'] ?? 'N/A') ?>
                                    </a>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="report-body">
                        <div class="report-info-grid">
                            <div class="info-item">
                                <div class="info-label">Taille</div>
                                <div class="info-value"><?= number_format(($report['file_size'] ?? 0) / 1024, 1) ?> Ko</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Téléchargements</div>
                                <div class="info-value">
                                    <span class="download-badge"><?= $report['download_count'] ?? 0 ?></span>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Téléversé par</div>
                                <div class="info-value">
                                    <?= htmlspecialchars(trim(($report['uploader_first_name'] ?? '') . ' ' . ($report['uploader_last_name'] ?? ''))) ?: 'N/A' ?>
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Date</div>
                                <div class="info-value">
                                    <?= date('d/m/Y à H:i', strtotime($report['uploaded_at'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="report-footer">
                        <a href="/reports/<?= $report['id'] ?>/download" class="download-btn">
                            <span class="btn-icon">⬇️</span>
                            <span>Télécharger</span>
                        </a>
                        <a href="/orders/<?= $report['order_id'] ?>" class="view-order-btn">
                            Voir la commande →
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- État vide -->
            <div class="empty-state">
                <div class="empty-icon">
                    <?php if (!empty($searchTerm)): ?>
                        🔍
                    <?php else: ?>
                        📭
                    <?php endif; ?>
                </div>
                <h2 class="empty-title">
                    <?php if (!empty($searchTerm)): ?>
                        Aucun résultat trouvé
                    <?php else: ?>
                        Aucun rapport disponible
                    <?php endif; ?>
                </h2>
                <p class="empty-description">
                    <?php if (!empty($searchTerm)): ?>
                        Aucun rapport ne correspond à votre recherche "<strong><?= htmlspecialchars($searchTerm) ?></strong>".
                        <br>Essayez avec d'autres mots-clés.
                    <?php elseif ($isClient): ?>
                        Vos rapports de diagnostic apparaîtront ici une fois qu'ils seront téléversés.
                    <?php else: ?>
                        Les rapports téléversés par les techniciens apparaîtront ici.
                    <?php endif; ?>
                </p>
                <?php if (!empty($searchTerm)): ?>
                <a href="/reports" class="btn-back">
                    ← Voir tous les rapports
                </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
* {
    box-sizing: border-box;
}

.reports-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* Header */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.header-content h1 {
    margin: 0 0 8px 0;
    font-size: 32px;
    font-weight: 700;
}

.subtitle {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
}

.breadcrumb {
    margin-top: 15px;
    font-size: 14px;
    opacity: 0.8;
}

.breadcrumb a {
    color: white;
    text-decoration: none;
    transition: opacity 0.3s;
}

.breadcrumb a:hover {
    opacity: 0.8;
    text-decoration: underline;
}

.breadcrumb span {
    margin: 0 8px;
}

.breadcrumb .current {
    opacity: 1;
    font-weight: 600;
}

/* Statistiques */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.stat-card.blue .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card.purple .stat-icon {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
}

.stat-card.green .stat-icon {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
}

.stat-icon {
    font-size: 36px;
    width: 70px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    flex-shrink: 0;
}

.stat-info {
    flex: 1;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 14px;
    color: #7f8c8d;
    font-weight: 500;
}

/* Recherche */
.search-section {
    margin-bottom: 30px;
}

.search-bar {
    background: white;
    border-radius: 12px;
    padding: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: box-shadow 0.3s;
}

.search-bar:focus-within {
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.2);
}

.search-icon {
    font-size: 22px;
    padding-left: 12px;
    color: #95a5a6;
}

.search-input {
    flex: 1;
    border: none;
    padding: 12px 8px;
    font-size: 15px;
    outline: none;
    color: #2c3e50;
}

.search-input::placeholder {
    color: #95a5a6;
}

.clear-search {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ecf0f1;
    border-radius: 50%;
    color: #7f8c8d;
    text-decoration: none;
    font-size: 18px;
    transition: all 0.3s;
}

.clear-search:hover {
    background: #e74c3c;
    color: white;
}

.search-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
}

.search-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.search-info {
    margin-top: 15px;
    padding: 12px 20px;
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    border-radius: 6px;
    color: #1976d2;
    font-size: 14px;
}

.search-term strong {
    font-weight: 700;
}

/* Grid de rapports */
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 24px;
}

.report-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
}

.report-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.report-header {
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.file-icon-large {
    font-size: 48px;
    flex-shrink: 0;
}

.report-title {
    flex: 1;
    min-width: 0;
}

.file-name {
    margin: 0 0 10px 0;
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.report-meta {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.meta-item {
    font-size: 13px;
    color: #7f8c8d;
    display: flex;
    align-items: center;
    gap: 6px;
}

.meta-icon {
    font-size: 14px;
}

.order-link {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
}

.order-link:hover {
    text-decoration: underline;
}

.report-body {
    padding: 20px;
}

.report-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-label {
    font-size: 12px;
    color: #95a5a6;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.info-value {
    font-size: 14px;
    color: #2c3e50;
    font-weight: 500;
}

.download-badge {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
}

.report-footer {
    padding: 15px 20px;
    background: #f8f9fa;
    display: flex;
    gap: 12px;
    border-top: 1px solid #e9ecef;
}

.download-btn {
    flex: 1;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: transform 0.3s, box-shadow 0.3s;
}

.download-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-icon {
    font-size: 16px;
}

.view-order-btn {
    padding: 12px 20px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    color: #7f8c8d;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
}

.view-order-btn:hover {
    border-color: #667eea;
    color: #667eea;
}

/* État vide */
.empty-state {
    background: white;
    border-radius: 12px;
    padding: 80px 40px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.empty-icon {
    font-size: 80px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-title {
    font-size: 24px;
    color: #2c3e50;
    margin: 0 0 12px 0;
    font-weight: 600;
}

.empty-description {
    font-size: 16px;
    color: #7f8c8d;
    max-width: 500px;
    margin: 0 auto 30px;
    line-height: 1.6;
}

.btn-back {
    display: inline-block;
    padding: 12px 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: transform 0.3s, box-shadow 0.3s;
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .reports-page {
        padding: 15px;
    }

    .page-header {
        padding: 20px;
    }

    .header-content h1 {
        font-size: 24px;
    }

    .stats-container {
        grid-template-columns: 1fr;
    }

    .reports-grid {
        grid-template-columns: 1fr;
    }

    .search-bar {
        flex-wrap: wrap;
    }

    .search-btn {
        width: 100%;
    }

    .report-info-grid {
        grid-template-columns: 1fr;
    }

    .report-footer {
        flex-direction: column;
    }
}
</style>
