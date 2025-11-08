<h1>Paramètres de l'application</h1>

<div class="page-actions">
    <a href="/admin" class="btn">Retour à l'administration</a>
</div>

<div class="settings-container">
    <form method="POST" action="/admin/settings" class="settings-form">
        <!-- Application Settings -->
        <div class="card">
            <h2>Paramètres généraux</h2>

            <div class="form-group">
                <label for="app_name">Nom de l'application</label>
                <input type="text" id="app_name" name="app_name"
                       value="<?= htmlspecialchars($config['app_name'] ?? 'Suivi Diag') ?>"
                       class="form-control">
            </div>

            <div class="form-group">
                <label for="app_env">Environnement</label>
                <select id="app_env" name="app_env" class="form-control">
                    <option value="development" <?= ($config['environment'] ?? 'production') === 'development' ? 'selected' : '' ?>>Développement</option>
                    <option value="production" <?= ($config['environment'] ?? 'production') === 'production' ? 'selected' : '' ?>>Production</option>
                </select>
            </div>

            <div class="form-group">
                <label for="timezone">Fuseau horaire</label>
                <input type="text" id="timezone" name="timezone"
                       value="<?= htmlspecialchars($config['timezone'] ?? 'Europe/Paris') ?>"
                       class="form-control">
            </div>
        </div>

        <!-- Email Settings -->
        <div class="card">
            <h2>Paramètres email</h2>

            <div class="form-group">
                <label for="email_from">Email expéditeur</label>
                <input type="email" id="email_from" name="email_from"
                       value="<?= htmlspecialchars($config['email_from'] ?? 'noreply@example.com') ?>"
                       class="form-control">
            </div>

            <div class="form-group">
                <label for="email_from_name">Nom de l'expéditeur</label>
                <input type="text" id="email_from_name" name="email_from_name"
                       value="<?= htmlspecialchars($config['email_from_name'] ?? 'Suivi Diag') ?>"
                       class="form-control">
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
                    <strong>Timezone système:</strong>
                    <span><?= date_default_timezone_get() ?></span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer les paramètres</button>
        </div>
    </form>
</div>

<style>
.page-actions {
    margin-bottom: 20px;
}

.settings-container {
    max-width: 800px;
}

.settings-form {
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

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #2c3e50;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

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

.form-actions {
    margin-top: 10px;
}
</style>
