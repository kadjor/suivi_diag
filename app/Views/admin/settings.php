<h1>Paramètres de l'application</h1>

<div class="page-actions">
    <a href="/admin" class="btn">Retour à l'administration</a>
</div>

<div class="settings-container">
    <form method="POST" action="/admin/settings" class="settings-form">
        <!-- Configuration Email -->
        <div class="card">
            <h2>Configuration Email (SMTP)</h2>
            <p class="help-text">Ces paramètres sont stockés dans config/email.php et ne seront pas écrasés lors des mises à jour.</p>

            <div class="form-row">
                <div class="form-group">
                    <label for="smtp_host">Serveur SMTP</label>
                    <input type="text" id="smtp_host" name="smtp_host"
                           value="<?= htmlspecialchars($emailConfig['smtp_host'] ?? '') ?>"
                           class="form-control" placeholder="smtp.gmail.com">
                </div>

                <div class="form-group">
                    <label for="smtp_port">Port SMTP</label>
                    <input type="number" id="smtp_port" name="smtp_port"
                           value="<?= htmlspecialchars($emailConfig['smtp_port'] ?? '587') ?>"
                           class="form-control" placeholder="587">
                </div>
            </div>

            <div class="form-group">
                <label for="smtp_encryption">Chiffrement</label>
                <select id="smtp_encryption" name="smtp_encryption" class="form-control">
                    <option value="tls" <?= ($emailConfig['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (recommandé)</option>
                    <option value="ssl" <?= ($emailConfig['smtp_encryption'] ?? 'tls') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                </select>
            </div>

            <div class="form-group">
                <label for="smtp_username">Nom d'utilisateur SMTP</label>
                <input type="text" id="smtp_username" name="smtp_username"
                       value="<?= htmlspecialchars($emailConfig['smtp_username'] ?? '') ?>"
                       class="form-control" placeholder="votre-email@example.com">
            </div>

            <div class="form-group">
                <label for="smtp_password">Mot de passe SMTP</label>
                <input type="password" id="smtp_password" name="smtp_password"
                       value="<?= htmlspecialchars($emailConfig['smtp_password'] ?? '') ?>"
                       class="form-control" placeholder="••••••••">
                <small class="help-text">Le mot de passe sera stocké en clair dans le fichier de configuration.</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="from_email">Email expéditeur</label>
                    <input type="email" id="from_email" name="from_email"
                           value="<?= htmlspecialchars($emailConfig['from_email'] ?? 'noreply@d-evidences.fr') ?>"
                           class="form-control" placeholder="noreply@d-evidences.fr">
                </div>

                <div class="form-group">
                    <label for="from_name">Nom de l'expéditeur</label>
                    <input type="text" id="from_name" name="from_name"
                           value="<?= htmlspecialchars($emailConfig['from_name'] ?? 'D-Evidences') ?>"
                           class="form-control" placeholder="D-Evidences">
                </div>
            </div>

            <div class="form-group">
                <label for="admin_email">Email administrateur</label>
                <input type="email" id="admin_email" name="admin_email"
                       value="<?= htmlspecialchars($emailConfig['admin_email'] ?? 'admin@d-evidences.fr') ?>"
                       class="form-control" placeholder="admin@d-evidences.fr">
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="enabled" <?= ($emailConfig['enabled'] ?? true) ? 'checked' : '' ?>>
                    Activer l'envoi d'emails
                </label>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="debug" <?= ($emailConfig['debug'] ?? false) ? 'checked' : '' ?>>
                    Mode debug (afficher les erreurs détaillées)
                </label>
            </div>

            <div class="form-group">
                <button type="button" id="testEmailBtn" class="btn btn-secondary">
                    🧪 Tester la configuration email
                </button>
                <span id="testEmailResult" class="test-result"></span>
            </div>
        </div>

        <!-- Informations système -->
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
                <div class="info-item">
                    <strong>Fichier config email:</strong>
                    <span><?= file_exists(__DIR__ . '/../../../config/email.php') ? '✅ Présent' : '❌ Manquant' ?></span>
                </div>
                <div class="info-item">
                    <strong>Droits d'écriture:</strong>
                    <span><?= is_writable(__DIR__ . '/../../../config/') ? '✅ OK' : '❌ Erreur' ?></span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 Enregistrer les paramètres</button>
        </div>
    </form>
</div>

<script>
document.getElementById('testEmailBtn').addEventListener('click', function() {
    const btn = this;
    const resultSpan = document.getElementById('testEmailResult');

    btn.disabled = true;
    btn.textContent = '⏳ Envoi en cours...';
    resultSpan.textContent = '';
    resultSpan.className = 'test-result';

    fetch('/admin/test-email', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'email=' + encodeURIComponent(document.getElementById('admin_email').value)
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = '🧪 Tester la configuration email';

        if (data.success) {
            resultSpan.textContent = '✅ ' + data.message;
            resultSpan.className = 'test-result success';
        } else {
            resultSpan.textContent = '❌ ' + (data.error || 'Erreur inconnue');
            resultSpan.className = 'test-result error';
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.textContent = '🧪 Tester la configuration email';
        resultSpan.textContent = '❌ Erreur: ' + error.message;
        resultSpan.className = 'test-result error';
    });
});
</script>

<style>
.page-actions {
    margin-bottom: 20px;
}

.settings-container {
    max-width: 900px;
}

.settings-form {
    display: grid;
    gap: 20px;
}

.card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 25px;
}

.card h2 {
    margin-top: 0;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
    color: #2c3e50;
}

.help-text {
    color: #7f8c8d;
    font-size: 0.9em;
    margin-bottom: 15px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #2c3e50;
    font-weight: 500;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #95a5a6;
    font-size: 0.85em;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: normal;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.info-item {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 4px;
    border-left: 3px solid #3498db;
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
    font-weight: 500;
}

.form-actions {
    margin-top: 10px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

.btn-secondary:disabled {
    background: #bdc3c7;
    cursor: not-allowed;
}

.test-result {
    margin-left: 15px;
    font-weight: 500;
}

.test-result.success {
    color: #27ae60;
}

.test-result.error {
    color: #e74c3c;
}
</style>
