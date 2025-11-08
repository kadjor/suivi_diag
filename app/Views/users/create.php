<h1>Créer un utilisateur</h1>

<div class="page-actions">
    <a href="/users" class="btn">Retour à la liste</a>
</div>

<form method="POST" action="/users/store" class="form-container">
    <div class="form-row">
        <div class="form-group">
            <label for="username">Nom d'utilisateur *</label>
            <input type="text" id="username" name="username" required class="form-control">
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="first_name">Prénom *</label>
            <input type="text" id="first_name" name="first_name" required class="form-control">
        </div>

        <div class="form-group">
            <label for="last_name">Nom *</label>
            <input type="text" id="last_name" name="last_name" required class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="password">Mot de passe *</label>
            <input type="password" id="password" name="password" required class="form-control">
        </div>

        <div class="form-group">
            <label for="phone">Téléphone</label>
            <input type="tel" id="phone" name="phone" class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="role_id">Rôle *</label>
            <select id="role_id" name="role_id" required class="form-control">
                <option value="">Sélectionner un rôle</option>
                <?php foreach ($roles as $role): ?>
                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="client_id">Client (si rôle client)</label>
            <select id="client_id" name="client_id" class="form-control">
                <option value="">Aucun</option>
                <?php foreach ($clients as $client): ?>
                <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['organization_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="active" value="1" checked>
            Compte actif
        </label>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>
    </div>
</form>
