<h1>Mon Profil</h1>

<div class="profile-container">
    <!-- Informations personnelles -->
    <div class="card">
        <h2>Informations personnelles</h2>
        <form action="/profile/update" method="POST">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" readonly class="readonly">
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="first_name">Prénom *</label>
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="last_name">Nom *</label>
                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Téléphone</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Rôle</label>
                <input type="text" value="<?= htmlspecialchars($user['role_label'] ?? 'N/A') ?>" readonly class="readonly">
            </div>

            <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
        </form>
    </div>

    <!-- Changement de mot de passe -->
    <div class="card">
        <h2>Changer le mot de passe</h2>
        <form action="/profile/change-password" method="POST">
            <div class="form-group">
                <label for="current_password">Mot de passe actuel *</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">Nouveau mot de passe *</label>
                <input type="password" id="new_password" name="new_password" required minlength="8">
                <small>Au moins 8 caractères</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe *</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>

            <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
        </form>
    </div>

    <!-- Informations de connexion -->
    <div class="card">
        <h2>Informations de connexion</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Dernière connexion:</strong>
                <span><?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Jamais' ?></span>
            </div>
            <div class="info-item">
                <strong>Compte créé le:</strong>
                <span><?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
            </div>
            <div class="info-item">
                <strong>Statut:</strong>
                <span class="badge" style="background-color: <?= $user['active'] ? '#27ae60' : '#e74c3c' ?>">
                    <?= $user['active'] ? 'Actif' : 'Inactif' ?>
                </span>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 800px;
    margin: 20px auto;
}

.card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.card h2 {
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input.readonly {
    background-color: #f5f5f5;
    cursor: not-allowed;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 0.85em;
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
}

.info-item span {
    display: block;
    font-size: 1.1em;
}
</style>
