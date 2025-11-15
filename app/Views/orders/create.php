<div class="order-create-page">
    <div class="page-header">
        <h1>📋 Créer une nouvelle commande</h1>
        <a href="/orders" class="btn btn-secondary">← Retour aux commandes</a>
    </div>

    <form id="orderForm" method="POST" action="/orders/store" enctype="multipart/form-data">
        <!-- Section 1: Informations client -->
        <div class="form-section">
            <h2>👤 Informations Client</h2>

            <?php if ($is_client ?? false): ?>
                <!-- Affichage simplifié pour les clients -->
                <div class="client-info-display">
                    <div class="info-card">
                        <div class="info-row">
                            <strong>🏢 Organisation:</strong>
                            <span><?= htmlspecialchars($current_client['organization_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="info-row">
                            <strong>📧 Email:</strong>
                            <span><?= htmlspecialchars($current_client['email'] ?? 'N/A') ?></span>
                        </div>
                        <div class="info-row">
                            <strong>📞 Téléphone:</strong>
                            <span><?= htmlspecialchars($current_client['phone'] ?? 'N/A') ?></span>
                        </div>
                        <div class="info-row">
                            <strong>📍 Adresse:</strong>
                            <span><?= htmlspecialchars($current_client['address'] ?? 'N/A') ?>, <?= htmlspecialchars($current_client['postal_code'] ?? '') ?> <?= htmlspecialchars($current_client['city'] ?? '') ?></span>
                        </div>
                    </div>
                    <input type="hidden" name="client_id" value="<?= $current_client['id'] ?>">
                    <input type="hidden" id="client_id" value="<?= $current_client['id'] ?>">
                </div>
            <?php else: ?>
                <!-- Sélection de client pour les administrateurs/secrétariat -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="client_id">Client <span class="required">*</span></label>
                        <select name="client_id" id="client_id" class="form-control" required>
                            <option value="">-- Sélectionner un client --</option>
                            <?php foreach ($clients ?? [] as $client): ?>
                                <option value="<?= $client['id'] ?>"
                                        data-email="<?= htmlspecialchars($client['email'] ?? '') ?>"
                                        data-phone="<?= htmlspecialchars($client['phone'] ?? '') ?>">
                                    <?= htmlspecialchars($client['organization_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Email client</label>
                        <input type="text" id="client_email_display" class="form-control" readonly disabled>
                    </div>
                </div>

                <div id="clientInfo" class="client-info" style="display: none;">
                    <div class="info-box">
                        <strong>📧 Email:</strong> <span id="client_email_text"></span> &nbsp;&nbsp;
                        <strong>📞 Téléphone:</strong> <span id="client_phone_text"></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section 2: Recherche patrimoine (numéro de lot) -->
        <div class="form-section">
            <h2>🏢 Adresse d'exécution</h2>

            <div class="form-row">
                <div class="form-group">
                    <label for="search_lot">Recherche par N° de lot (patrimoine) 🔍</label>
                    <input type="text" id="search_lot" class="form-control" placeholder="Tapez le numéro de lot...">
                    <small class="form-text">Recherche dans le patrimoine du client sélectionné</small>
                </div>
            </div>

            <div id="lotResults" class="autocomplete-results"></div>

            <div class="form-row">
                <div class="form-group">
                    <label for="numero_lot">Numéro de lot</label>
                    <input type="text" name="numero_lot" id="numero_lot" class="form-control">
                    <input type="hidden" name="site_id" id="site_id">
                </div>

                <div class="form-group">
                    <label for="execution_address">Adresse <span class="required">*</span></label>
                    <input type="text" name="execution_address" id="execution_address" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="execution_postal_code">Code postal <span class="required">*</span></label>
                    <input type="text" name="execution_postal_code" id="execution_postal_code" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="execution_city">Ville <span class="required">*</span></label>
                    <input type="text" name="execution_city" id="execution_city" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="execution_numero_porte">Numéro de porte</label>
                    <input type="text" name="execution_numero_porte" id="execution_numero_porte" class="form-control">
                </div>

                <div class="form-group">
                    <label for="execution_niveau">Étage / Niveau</label>
                    <input type="text" name="execution_niveau" id="execution_niveau" class="form-control">
                </div>
            </div>
        </div>

        <!-- Section 3: Diagnostics demandés -->
        <div class="form-section">
            <h2>🔬 Diagnostics demandés</h2>

            <div class="diagnostics-grid">
                <?php foreach ($diagnostic_types ?? [] as $type): ?>
                    <div class="diagnostic-item">
                        <label class="checkbox-label">
                            <input type="checkbox"
                                   name="diagnostic_types[]"
                                   value="<?= $type['id'] ?>"
                                   data-name="<?= htmlspecialchars($type['name']) ?>">
                            <span class="diagnostic-badge" style="background-color: <?= $type['color'] ?? '#3498db' ?>">
                                <?= htmlspecialchars($type['code']) ?>
                            </span>
                            <?= htmlspecialchars($type['name']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="diagnosticsError" class="error-message" style="display: none;">
                ⚠️ Veuillez sélectionner au moins un diagnostic
            </div>
        </div>

        <!-- Section 4: Destinataires des rapports -->
        <div class="form-section">
            <h2>📨 Destinataires des rapports</h2>

            <p class="form-help">
                Le client principal recevra automatiquement les rapports. Vous pouvez ajouter d'autres destinataires ci-dessous.
            </p>

            <div class="recipients-container">
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="recipient_email">Email destinataire</label>
                        <input type="email" id="recipient_email" class="form-control" placeholder="exemple@email.com">
                    </div>
                    <div class="form-group" style="width: 150px;">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary" onclick="addRecipient()">
                            ➕ Ajouter
                        </button>
                    </div>
                </div>

                <div id="recipientsList"></div>
                <input type="hidden" name="report_recipients" id="report_recipients">
            </div>
        </div>

        <!-- Section 5: Upload PDF bon de commande -->
        <div class="form-section">
            <h2>📄 Bon de commande (PDF)</h2>

            <div class="form-group">
                <label for="bon_de_commande_pdf">Upload PDF du bon de commande (optionnel)</label>
                <input type="file" name="bon_de_commande_pdf" id="bon_de_commande_pdf" class="form-control" accept=".pdf">
                <small class="form-text">Format accepté : PDF uniquement (max 10 Mo)</small>
            </div>
        </div>

        <!-- Section 6: Notes -->
        <div class="form-section">
            <h2>📝 Notes & Remarques</h2>

            <div class="form-group">
                <label for="notes">Notes internes</label>
                <textarea name="notes" id="notes" class="form-control" rows="4"
                          placeholder="Remarques, instructions particulières..."></textarea>
            </div>

            <div class="form-group">
                <label for="priority">Priorité</label>
                <select name="priority" id="priority" class="form-control">
                    <option value="low">Basse</option>
                    <option value="normal" selected>Normale</option>
                    <option value="high">Haute</option>
                    <option value="urgent">Urgente</option>
                </select>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='/orders'">
                ✕ Annuler
            </button>
            <button type="submit" class="btn btn-success" id="submitBtn">
                ✓ Créer la commande
            </button>
        </div>
    </form>
</div>

<style>
.order-create-page {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 3px solid #2c3e50;
}

.page-header h1 {
    color: #2c3e50;
    margin: 0;
}

.form-section {
    background: white;
    padding: 25px;
    margin-bottom: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #3498db;
}

.form-section h2 {
    color: #2c3e50;
    font-size: 1.3rem;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #ecf0f1;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.form-group {
    flex: 1;
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
    color: #2c3e50;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 2px solid #dfe6e9;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
}

.form-control:disabled {
    background: #ecf0f1;
    cursor: not-allowed;
}

.form-text {
    display: block;
    margin-top: 5px;
    color: #7f8c8d;
    font-size: 12px;
}

.required {
    color: #e74c3c;
    font-weight: bold;
}

.client-info {
    margin-top: 15px;
}

.info-box {
    background: #e8f5e9;
    padding: 15px;
    border-radius: 4px;
    border-left: 4px solid #27ae60;
}

.client-info-display {
    margin-bottom: 20px;
}

.info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.info-row {
    display: flex;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.info-row:last-child {
    border-bottom: none;
}

.info-row strong {
    min-width: 140px;
    font-weight: 600;
    opacity: 0.9;
}

.info-row span {
    flex: 1;
    font-size: 15px;
}

.autocomplete-results {
    position: relative;
    z-index: 100;
}

.autocomplete-item {
    background: white;
    padding: 12px;
    border: 1px solid #dfe6e9;
    border-top: none;
    cursor: pointer;
    transition: background 0.2s;
}

.autocomplete-item:first-child {
    border-top: 1px solid #dfe6e9;
    border-radius: 4px 4px 0 0;
}

.autocomplete-item:last-child {
    border-radius: 0 0 4px 4px;
}

.autocomplete-item:hover {
    background: #f8f9fa;
}

.autocomplete-item strong {
    color: #2c3e50;
    display: block;
    margin-bottom: 5px;
}

.autocomplete-item small {
    color: #7f8c8d;
}

.diagnostics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.diagnostic-item {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 4px;
    border: 2px solid transparent;
    transition: all 0.3s;
}

.diagnostic-item:has(input:checked) {
    background: #e3f2fd;
    border-color: #3498db;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.diagnostic-badge {
    padding: 4px 8px;
    border-radius: 4px;
    color: white;
    font-weight: bold;
    font-size: 11px;
}

.recipients-container {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}

#recipientsList {
    margin-top: 15px;
}

.recipient-tag {
    display: inline-flex;
    align-items: center;
    background: #3498db;
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    margin: 5px;
    font-size: 14px;
}

.recipient-tag .remove-recipient {
    margin-left: 10px;
    cursor: pointer;
    font-weight: bold;
    padding: 0 5px;
}

.recipient-tag .remove-recipient:hover {
    color: #e74c3c;
}

.form-help {
    background: #fff3cd;
    padding: 10px 15px;
    border-radius: 4px;
    border-left: 4px solid #ffc107;
    margin-bottom: 15px;
    font-size: 14px;
}

.error-message {
    background: #ffebee;
    color: #c62828;
    padding: 10px 15px;
    border-radius: 4px;
    border-left: 4px solid #e74c3c;
    margin-top: 10px;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    padding-top: 20px;
}

.btn {
    padding: 12px 30px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-success {
    background: #27ae60;
    color: white;
}

.btn-success:hover {
    background: #229954;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }

    .diagnostics-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Variables globales
let recipients = [];
let searchTimeout = null;

// Au changement de client
document.getElementById('client_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];

    if (this.value) {
        // Afficher les infos client
        const email = selectedOption.getAttribute('data-email');
        const phone = selectedOption.getAttribute('data-phone');

        document.getElementById('client_email_display').value = email;
        document.getElementById('client_email_text').textContent = email || 'Non renseigné';
        document.getElementById('client_phone_text').textContent = phone || 'Non renseigné';
        document.getElementById('clientInfo').style.display = 'block';

        // Activer la recherche de lot
        document.getElementById('search_lot').disabled = false;
    } else {
        document.getElementById('clientInfo').style.display = 'none';
        document.getElementById('search_lot').disabled = true;
        document.getElementById('search_lot').value = '';
    }

    // Réinitialiser la recherche
    document.getElementById('lotResults').innerHTML = '';
    clearForm();
});

// Recherche autocomplete sur numéro de lot
document.getElementById('search_lot').addEventListener('input', function() {
    clearTimeout(searchTimeout);

    const query = this.value.trim();
    const clientId = document.getElementById('client_id').value;

    if (!clientId) {
        alert('Veuillez d\'abord sélectionner un client');
        this.value = '';
        return;
    }

    if (query.length < 2) {
        document.getElementById('lotResults').innerHTML = '';
        return;
    }

    searchTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`/api/sites/search-lot?client_id=${clientId}&q=${encodeURIComponent(query)}`);
            const data = await response.json();

            displayLotResults(data.sites || []);
        } catch (error) {
            console.error('Erreur recherche:', error);
        }
    }, 300);
});

// Afficher les résultats de recherche
function displayLotResults(sites) {
    const container = document.getElementById('lotResults');

    if (sites.length === 0) {
        container.innerHTML = `
            <div class="autocomplete-item" style="background: #fff3cd;">
                <strong>❌ Aucun lot trouvé</strong>
                <small>Le numéro de lot sera ajouté au patrimoine après validation de la commande</small>
            </div>
        `;
        return;
    }

    let html = '';
    sites.forEach(site => {
        html += `
            <div class="autocomplete-item" onclick="selectSite(${site.id}, '${escapeHtml(site.numero_lot)}', '${escapeHtml(site.address)}', '${escapeHtml(site.city)}', '${escapeHtml(site.postal_code)}', '${escapeHtml(site.numero_porte || '')}', '${escapeHtml(site.niveau || '')}')">
                <strong>🏢 Lot ${escapeHtml(site.numero_lot)}</strong>
                <small>${escapeHtml(site.address)}, ${escapeHtml(site.postal_code)} ${escapeHtml(site.city)}</small>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Sélectionner un site
function selectSite(id, lot, address, city, postalCode, porte, niveau) {
    document.getElementById('site_id').value = id;
    document.getElementById('numero_lot').value = lot;
    document.getElementById('execution_address').value = address;
    document.getElementById('execution_city').value = city;
    document.getElementById('execution_postal_code').value = postalCode;
    document.getElementById('execution_numero_porte').value = porte;
    document.getElementById('execution_niveau').value = niveau;

    document.getElementById('search_lot').value = lot;
    document.getElementById('lotResults').innerHTML = '';
}

// Nettoyer le formulaire
function clearForm() {
    document.getElementById('site_id').value = '';
    document.getElementById('numero_lot').value = '';
    document.getElementById('execution_address').value = '';
    document.getElementById('execution_city').value = '';
    document.getElementById('execution_postal_code').value = '';
    document.getElementById('execution_numero_porte').value = '';
    document.getElementById('execution_niveau').value = '';
}

// Ajouter un destinataire
function addRecipient() {
    const emailInput = document.getElementById('recipient_email');
    const email = emailInput.value.trim();

    if (!email) {
        return;
    }

    // Validation email
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Email invalide');
        return;
    }

    if (recipients.includes(email)) {
        alert('Cet email est déjà dans la liste');
        return;
    }

    recipients.push(email);
    emailInput.value = '';

    updateRecipientsList();
}

// Supprimer un destinataire
function removeRecipient(email) {
    recipients = recipients.filter(e => e !== email);
    updateRecipientsList();
}

// Mettre à jour l'affichage des destinataires
function updateRecipientsList() {
    const container = document.getElementById('recipientsList');

    if (recipients.length === 0) {
        container.innerHTML = '';
        document.getElementById('report_recipients').value = '';
        return;
    }

    let html = '';
    recipients.forEach(email => {
        html += `
            <span class="recipient-tag">
                ${escapeHtml(email)}
                <span class="remove-recipient" onclick="removeRecipient('${escapeHtml(email)}')">✕</span>
            </span>
        `;
    });

    container.innerHTML = html;
    document.getElementById('report_recipients').value = JSON.stringify(recipients);
}

// Validation avant soumission
document.getElementById('orderForm').addEventListener('submit', function(e) {
    // Vérifier qu'au moins un diagnostic est sélectionné
    const checkedDiagnostics = document.querySelectorAll('input[name="diagnostic_types[]"]:checked');

    if (checkedDiagnostics.length === 0) {
        e.preventDefault();
        document.getElementById('diagnosticsError').style.display = 'block';
        document.getElementById('diagnosticsError').scrollIntoView({ behavior: 'smooth' });
        return false;
    }

    document.getElementById('diagnosticsError').style.display = 'none';

    // Confirmation
    const clientName = document.getElementById('client_id').options[document.getElementById('client_id').selectedIndex].text;
    const address = document.getElementById('execution_address').value;

    if (!confirm(`Créer la commande pour :\n\nClient : ${clientName}\nAdresse : ${address}\n\nContinuer ?`)) {
        e.preventDefault();
        return false;
    }

    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').textContent = '⏳ Création en cours...';
});

// Fonction helper
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
