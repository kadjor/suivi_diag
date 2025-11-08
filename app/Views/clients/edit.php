<h1>Modifier le client</h1>

<div class="page-actions">
    <a href="/clients/<?= $client['id'] ?>" class="btn">Retour aux détails</a>
</div>

<form method="POST" action="/clients/<?= $client['id'] ?>/update" class="form-container">
    <h2>Informations de l'organisation</h2>

    <div class="form-row">
        <div class="form-group">
            <label for="organization_name">Nom de l'organisation *</label>
            <input type="text" id="organization_name" name="organization_name" value="<?= htmlspecialchars($client['organization_name']) ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="siret">SIRET</label>
            <input type="text" id="siret" name="siret" value="<?= htmlspecialchars($client['siret'] ?? '') ?>" class="form-control">
        </div>
    </div>

    <h2>Contact principal</h2>

    <div class="form-row">
        <div class="form-group">
            <label for="contact_first_name">Prénom *</label>
            <input type="text" id="contact_first_name" name="contact_first_name" value="<?= htmlspecialchars($client['contact_first_name']) ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="contact_last_name">Nom *</label>
            <input type="text" id="contact_last_name" name="contact_last_name" value="<?= htmlspecialchars($client['contact_last_name']) ?>" required class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="contact_email">Email *</label>
            <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($client['contact_email']) ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="contact_phone">Téléphone *</label>
            <input type="tel" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($client['contact_phone']) ?>" required class="form-control">
        </div>
    </div>

    <h2>Adresse</h2>

    <div class="form-group">
        <label for="address">Adresse *</label>
        <input type="text" id="address" name="address" value="<?= htmlspecialchars($client['address']) ?>" required class="form-control">
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="postal_code">Code postal *</label>
            <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($client['postal_code']) ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="city">Ville *</label>
            <input type="text" id="city" name="city" value="<?= htmlspecialchars($client['city']) ?>" required class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="4" class="form-control"><?= htmlspecialchars($client['notes'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="active" value="1" <?= $client['active'] ? 'checked' : '' ?>>
            Client actif
        </label>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </div>
</form>
