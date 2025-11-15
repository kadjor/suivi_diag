<?php
$user = \Core\Auth::user();
$is_client = ($user['role_name'] === 'client');
$can_upload = !$is_client || true; // Tous peuvent uploader
$can_message = true; // Tous peuvent envoyer des messages
?>

<div class="order-page">
    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h1>📦 Commande #<?= htmlspecialchars($order['order_number']) ?></h1>
            <div class="order-meta">
                <span class="meta-item">
                    <strong>Client:</strong> <?= htmlspecialchars($order['client_name'] ?? 'N/A') ?>
                </span>
                <span class="meta-item">
                    <strong>Créée le:</strong> <?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?>
                </span>
            </div>
        </div>
        <div class="header-right">
            <div class="status-badge-large" style="background-color: <?= htmlspecialchars($order['status_color'] ?? '#95a5a6') ?>;">
                <?= htmlspecialchars($order['status_label'] ?? 'N/A') ?>
            </div>
            <div class="header-actions">
                <a href="/orders" class="btn btn-secondary">← Retour</a>
                <?php if ($can_edit ?? false): ?>
                <a href="/orders/<?= $order['id'] ?>/edit" class="btn btn-primary">✏️ Modifier</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Navigation par onglets -->
    <div class="tabs-container">
        <div class="tabs">
            <button class="tab-btn active" data-tab="details">
                <span class="tab-icon">📋</span>
                <span class="tab-label">Détails</span>
            </button>
            <button class="tab-btn" data-tab="documents">
                <span class="tab-icon">📎</span>
                <span class="tab-label">Documents (<?= count($documents ?? []) ?>)</span>
            </button>
            <button class="tab-btn" data-tab="messages">
                <span class="tab-icon">💬</span>
                <span class="tab-label">Messages (<?= count($messages ?? []) ?>)</span>
                <?php if (($unread_count ?? 0) > 0): ?>
                <span class="badge-count"><?= $unread_count ?></span>
                <?php endif; ?>
            </button>
            <button class="tab-btn" data-tab="interventions">
                <span class="tab-icon">🔧</span>
                <span class="tab-label">Interventions (<?= count($interventions ?? []) ?>)</span>
            </button>
            <button class="tab-btn" data-tab="timeline">
                <span class="tab-icon">⏱️</span>
                <span class="tab-label">Historique</span>
            </button>
        </div>
    </div>

    <!-- Contenu des onglets -->
    <div class="tabs-content">
        <!-- Onglet: Détails -->
        <div class="tab-content active" id="tab-details">
            <div class="details-grid">
                <!-- Informations principales -->
                <div class="card">
                    <div class="card-header">
                        <h2>📋 Informations de la commande</h2>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Numéro de commande</span>
                                <span class="info-value"><?= htmlspecialchars($order['order_number']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Priorité</span>
                                <span class="priority-badge priority-<?= $order['priority'] ?? 'normal' ?>">
                                    <?= ucfirst($order['priority'] ?? 'normal') ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Date demandée</span>
                                <span class="info-value">
                                    <?= $order['requested_date'] ? date('d/m/Y', strtotime($order['requested_date'])) : 'N/A' ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Date limite</span>
                                <span class="info-value">
                                    <?= $order['deadline_date'] ? date('d/m/Y', strtotime($order['deadline_date'])) : 'N/A' ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($order['description']): ?>
                        <div class="description-section">
                            <strong>Description:</strong>
                            <p><?= nl2br(htmlspecialchars($order['description'])) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Site / Localisation -->
                <div class="card">
                    <div class="card-header">
                        <h2>📍 Site / Localisation</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($order['site_id']): ?>
                        <div class="info-grid">
                            <div class="info-item full-width">
                                <span class="info-label">Site</span>
                                <span class="info-value">
                                    <a href="/sites/<?= $order['site_id'] ?>">
                                        <?= htmlspecialchars($order['site_name'] ?? 'Voir le site') ?>
                                    </a>
                                </span>
                            </div>
                            <?php if ($order['site_address'] ?? false): ?>
                            <div class="info-item full-width">
                                <span class="info-label">Adresse</span>
                                <span class="info-value">
                                    <?= htmlspecialchars($order['site_address']) ?><br>
                                    <?= htmlspecialchars($order['site_city'] ?? '') ?> <?= htmlspecialchars($order['site_postal_code'] ?? '') ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="info-grid">
                            <div class="info-item full-width">
                                <span class="info-label">Adresse d'exécution</span>
                                <span class="info-value">
                                    <?= htmlspecialchars($order['execution_address'] ?? 'N/A') ?><br>
                                    <?= htmlspecialchars($order['execution_city'] ?? '') ?> <?= htmlspecialchars($order['execution_postal_code'] ?? '') ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Numéro de lot</span>
                                <span class="info-value"><?= htmlspecialchars($order['numero_lot'] ?? 'N/A') ?></span>
                            </div>
                            <?php if ($order['execution_numero_porte'] ?? false): ?>
                            <div class="info-item">
                                <span class="info-label">Porte / Apt</span>
                                <span class="info-value"><?= htmlspecialchars($order['execution_numero_porte']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($order['execution_niveau'] ?? false): ?>
                            <div class="info-item">
                                <span class="info-label">Niveau</span>
                                <span class="info-value"><?= htmlspecialchars($order['execution_niveau']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Technicien assigné -->
                <?php if ($order['assigned_to']): ?>
                <div class="card">
                    <div class="card-header">
                        <h2>👷 Technicien assigné</h2>
                    </div>
                    <div class="card-body">
                        <div class="technician-info">
                            <div class="technician-avatar">
                                <?= strtoupper(substr($order['technician_name'] ?? 'T', 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($order['technician_name'] ?? 'N/A') ?></strong>
                                <?php if ($order['technician_email'] ?? false): ?>
                                <br><small><?= htmlspecialchars($order['technician_email']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Onglet: Documents -->
        <div class="tab-content" id="tab-documents">
            <div class="card">
                <div class="card-header">
                    <h2>📎 Documents et plans</h2>
                    <?php if ($can_upload): ?>
                    <button onclick="showUploadModal()" class="btn btn-primary">
                        ➕ Ajouter un document
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($documents)): ?>
                    <div class="documents-grid">
                        <?php foreach ($documents as $doc): ?>
                        <div class="document-card">
                            <div class="document-icon">
                                <?php
                                $icon = '📄';
                                if ($doc['document_type'] === 'plan') $icon = '📐';
                                elseif ($doc['document_type'] === 'rapport') $icon = '📊';
                                elseif ($doc['document_type'] === 'photo') $icon = '📷';
                                elseif ($doc['document_type'] === 'bon_commande') $icon = '📋';
                                echo $icon;
                                ?>
                            </div>
                            <div class="document-info">
                                <strong><?= htmlspecialchars($doc['file_name']) ?></strong>
                                <small><?= ucfirst($doc['document_type']) ?> • <?= number_format($doc['file_size'] / 1024, 0) ?> Ko</small>
                                <?php if ($doc['description']): ?>
                                <p><?= htmlspecialchars($doc['description']) ?></p>
                                <?php endif; ?>
                                <small class="text-muted">
                                    Ajouté par <?= htmlspecialchars($doc['uploader_name'] ?? 'N/A') ?>
                                    le <?= date('d/m/Y à H:i', strtotime($doc['created_at'])) ?>
                                </small>
                            </div>
                            <div class="document-actions">
                                <a href="/orders/<?= $order['id'] ?>/documents/<?= $doc['id'] ?>/download" class="btn btn-sm" target="_blank">
                                    📥 Télécharger
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📎</div>
                        <p>Aucun document pour cette commande</p>
                        <?php if ($can_upload): ?>
                        <button onclick="showUploadModal()" class="btn btn-primary">
                            ➕ Ajouter le premier document
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Onglet: Messages -->
        <div class="tab-content" id="tab-messages">
            <div class="messages-container">
                <div class="card">
                    <div class="card-header">
                        <h2>💬 Discussion</h2>
                        <small class="text-muted">Les messages envoient une notification instantanée au secrétariat</small>
                    </div>
                    <div class="card-body">
                        <div class="messages-list" id="messagesList">
                            <?php if (!empty($messages)): ?>
                                <?php foreach ($messages as $msg): ?>
                                <div class="message-item <?= $msg['user_id'] == $user['id'] ? 'message-own' : 'message-other' ?>">
                                    <div class="message-avatar">
                                        <?= strtoupper(substr($msg['author_name'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div class="message-content">
                                        <div class="message-header">
                                            <strong><?= htmlspecialchars($msg['author_name'] ?? 'Utilisateur') ?></strong>
                                            <span class="message-time"><?= date('d/m/Y à H:i', strtotime($msg['created_at'])) ?></span>
                                        </div>
                                        <div class="message-body">
                                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">💬</div>
                                <p>Aucun message pour cette commande</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($can_message): ?>
                        <div class="message-form">
                            <form id="messageForm" onsubmit="sendMessage(event)">
                                <textarea
                                    id="messageInput"
                                    placeholder="Écrivez votre message... (notifie automatiquement le secrétariat)"
                                    rows="3"
                                    required
                                ></textarea>
                                <div class="message-form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        📤 Envoyer
                                    </button>
                                </div>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onglet: Interventions -->
        <div class="tab-content" id="tab-interventions">
            <div class="card">
                <div class="card-header">
                    <h2>🔧 Interventions</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($interventions)): ?>
                    <div class="interventions-list">
                        <?php foreach ($interventions as $intervention): ?>
                        <div class="intervention-card">
                            <div class="intervention-date">
                                <div class="date-day"><?= date('d', strtotime($intervention['scheduled_date'])) ?></div>
                                <div class="date-month"><?= strftime('%b', strtotime($intervention['scheduled_date'])) ?></div>
                            </div>
                            <div class="intervention-details">
                                <strong><?= htmlspecialchars($intervention['type'] ?? 'Intervention') ?></strong>
                                <?php if ($intervention['technician_name'] ?? false): ?>
                                <p>Technicien: <?= htmlspecialchars($intervention['technician_name']) ?></p>
                                <?php endif; ?>
                                <?php if ($intervention['notes'] ?? false): ?>
                                <p class="text-muted"><?= htmlspecialchars($intervention['notes']) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="intervention-status">
                                <span class="status-badge status-<?= $intervention['status'] ?? 'scheduled' ?>">
                                    <?= ucfirst($intervention['status'] ?? 'Planifiée') ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">🔧</div>
                        <p>Aucune intervention planifiée</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Onglet: Historique -->
        <div class="tab-content" id="tab-timeline">
            <div class="card">
                <div class="card-header">
                    <h2>⏱️ Historique de la commande</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($events)): ?>
                    <div class="timeline">
                        <?php foreach ($events as $event): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <strong><?= htmlspecialchars($event['event_type'] ?? 'Événement') ?></strong>
                                    <span class="timeline-date"><?= date('d/m/Y à H:i', strtotime($event['timestamp'])) ?></span>
                                </div>
                                <?php if (!empty($event['description'])): ?>
                                <p><?= htmlspecialchars($event['description']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($event['user_name'])): ?>
                                <small class="text-muted">Par <?= htmlspecialchars($event['user_name']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">⏱️</div>
                        <p>Aucun événement enregistré</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'upload de documents -->
<div id="uploadModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>📎 Ajouter un document</h2>
            <button class="modal-close" onclick="closeUploadModal()">&times;</button>
        </div>
        <form id="uploadForm" onsubmit="uploadDocument(event)" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-group">
                    <label for="documentType">Type de document *</label>
                    <select id="documentType" name="document_type" required class="form-control">
                        <option value="">-- Sélectionner --</option>
                        <option value="plan">📐 Plan</option>
                        <option value="rapport">📊 Rapport</option>
                        <option value="photo">📷 Photo</option>
                        <option value="bon_commande">📋 Bon de commande</option>
                        <option value="autre">📄 Autre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="documentFile">Fichier (PDF, images, max 10Mo) *</label>
                    <input type="file" id="documentFile" name="file" required class="form-control"
                           accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx">
                    <small class="form-text">Formats acceptés: PDF, JPG, PNG, GIF, DOC, DOCX (max 10Mo)</small>
                </div>

                <div class="form-group">
                    <label for="documentDescription">Description</label>
                    <textarea id="documentDescription" name="description" rows="3" class="form-control"
                              placeholder="Description optionnelle du document..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeUploadModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">📤 Uploader</button>
            </div>
        </form>
    </div>
</div>

<style>
.order-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* Header */
.page-header {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    flex-wrap: wrap;
}

.header-left h1 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.order-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.meta-item {
    color: #7f8c8d;
    font-size: 14px;
}

.header-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 15px;
}

.status-badge-large {
    padding: 10px 20px;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    font-size: 16px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

/* Tabs */
.tabs-container {
    background: white;
    border-radius: 12px 12px 0 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow-x: auto;
}

.tabs {
    display: flex;
    gap: 5px;
    padding: 10px 10px 0 10px;
}

.tab-btn {
    background: transparent;
    border: none;
    padding: 15px 25px;
    cursor: pointer;
    border-radius: 8px 8px 0 0;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
    color: #7f8c8d;
    position: relative;
}

.tab-btn:hover {
    background: #f8f9fa;
}

.tab-btn.active {
    background: #3498db;
    color: white;
}

.tab-icon {
    font-size: 18px;
}

.badge-count {
    background: #e74c3c;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}

/* Tab content */
.tabs-content {
    background: white;
    border-radius: 0 0 12px 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    min-height: 400px;
}

.tab-content {
    display: none;
    padding: 30px;
    animation: fadeIn 0.3s;
}

.tab-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Cards */
.card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 20px;
}

.card-header {
    padding: 20px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    margin: 0;
    font-size: 18px;
    color: #2c3e50;
}

.card-body {
    padding: 20px;
}

/* Details grid */
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-label {
    font-size: 13px;
    color: #7f8c8d;
    font-weight: 600;
}

.info-value {
    font-size: 15px;
    color: #2c3e50;
}

.priority-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
}

.priority-low { background: #95a5a6; color: white; }
.priority-normal { background: #3498db; color: white; }
.priority-high { background: #f39c12; color: white; }
.priority-urgent { background: #e74c3c; color: white; }

.description-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
}

.technician-info {
    display: flex;
    gap: 15px;
    align-items: center;
}

.technician-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 600;
}

/* Documents */
.documents-grid {
    display: grid;
    gap: 15px;
}

.document-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    gap: 15px;
    align-items: center;
    transition: box-shadow 0.3s;
}

.document-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.document-icon {
    font-size: 36px;
}

.document-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.document-info strong {
    color: #2c3e50;
}

.document-info small {
    color: #7f8c8d;
    font-size: 12px;
}

.document-info p {
    margin: 5px 0;
    color: #7f8c8d;
    font-size: 14px;
}

/* Messages */
.messages-list {
    max-height: 500px;
    overflow-y: auto;
    margin-bottom: 20px;
    padding: 10px;
}

.message-item {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.message-item.message-own {
    flex-direction: row-reverse;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    flex-shrink: 0;
}

.message-own .message-avatar {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.message-content {
    max-width: 70%;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 12px 15px;
}

.message-own .message-content {
    background: #e3f2fd;
}

.message-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    gap: 15px;
}

.message-time {
    font-size: 11px;
    color: #7f8c8d;
}

.message-body {
    color: #2c3e50;
    font-size: 14px;
}

.message-form {
    border-top: 2px solid #f0f0f0;
    padding-top: 20px;
}

.message-form textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    resize: vertical;
    margin-bottom: 10px;
}

.message-form textarea:focus {
    outline: none;
    border-color: #3498db;
}

.message-form-actions {
    display: flex;
    justify-content: flex-end;
}

/* Interventions */
.interventions-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.intervention-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    gap: 20px;
    align-items: center;
}

.intervention-date {
    text-align: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    min-width: 60px;
}

.date-day {
    font-size: 24px;
    font-weight: 600;
    color: #3498db;
}

.date-month {
    font-size: 12px;
    color: #7f8c8d;
    text-transform: uppercase;
}

.intervention-details {
    flex: 1;
}

.intervention-details strong {
    color: #2c3e50;
    display: block;
    margin-bottom: 5px;
}

.intervention-details p {
    margin: 5px 0;
    font-size: 14px;
    color: #7f8c8d;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 11px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -40px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #3498db;
    border: 4px solid white;
    box-shadow: 0 0 0 2px #3498db;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.timeline-date {
    font-size: 12px;
    color: #7f8c8d;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 20px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 32px;
    cursor: pointer;
    color: #7f8c8d;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 2px solid #f0f0f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-family: inherit;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
}

.form-text {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #7f8c8d;
}

/* Empty states */
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
    .page-header {
        flex-direction: column;
    }

    .header-right {
        width: 100%;
        align-items: stretch;
    }

    .tabs {
        overflow-x: auto;
    }

    .tab-label {
        display: none;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }

    .message-content {
        max-width: 85%;
    }
}
</style>

<script>
// Gestion des onglets
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabName = btn.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');
    });
});

// Modal d'upload
function showUploadModal() {
    document.getElementById('uploadModal').classList.add('active');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.remove('active');
    document.getElementById('uploadForm').reset();
}

// Upload de document
function uploadDocument(e) {
    e.preventDefault();
    const formData = new FormData(e.target);

    fetch('/orders/<?= $order['id'] ?>/documents/upload', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Document uploadé avec succès !');
            closeUploadModal();
            window.location.reload();
        } else {
            alert('❌ Erreur: ' + (data.error || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Erreur lors de l\'upload');
    });
}

// Envoi de message
function sendMessage(e) {
    e.preventDefault();
    const message = document.getElementById('messageInput').value.trim();

    if (!message) return;

    fetch('/orders/<?= $order['id'] ?>/messages/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('messageInput').value = '';
            // Ajouter le message à la liste
            const messagesList = document.getElementById('messagesList');
            const newMessage = createMessageElement(data.message);
            messagesList.appendChild(newMessage);
            messagesList.scrollTop = messagesList.scrollHeight;
        } else {
            alert('❌ Erreur: ' + (data.error || 'Erreur d\'envoi'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Erreur lors de l\'envoi du message');
    });
}

function createMessageElement(message) {
    const div = document.createElement('div');
    div.className = 'message-item message-own';
    div.innerHTML = `
        <div class="message-avatar">${message.author_initial}</div>
        <div class="message-content">
            <div class="message-header">
                <strong>${message.author_name}</strong>
                <span class="message-time">À l'instant</span>
            </div>
            <div class="message-body">${message.message.replace(/\n/g, '<br>')}</div>
        </div>
    `;
    return div;
}

// Fermer modal avec Echap
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeUploadModal();
    }
});
</script>
