<h1>Modifier le site</h1>

<div class="page-actions">
    <a href="/sites/<?= $site['id'] ?>" class="btn">Retour aux détails</a>
</div>

<form method="POST" action="/sites/<?= $site['id'] ?>/update" class="form-container">
    <div class="form-group">
        <label for="client_id">Client *</label>
        <select id="client_id" name="client_id" required class="form-control">
            <?php foreach ($clients as $client): ?>
            <option value="<?= $client['id'] ?>" <?= $site['client_id'] == $client['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($client['organization_name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="name">Nom du site *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($site['name']) ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="reference_pch">Référence PCH</label>
            <input type="text" id="reference_pch" name="reference_pch" value="<?= htmlspecialchars($site['reference_pch'] ?? '') ?>" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="address">Adresse *</label>
        <input type="text" id="address" name="address" value="<?= htmlspecialchars($site['address']) ?>" required class="form-control">
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="postal_code">Code postal *</label>
            <input type="text" id="postal_code" name="postal_code" value="<?= htmlspecialchars($site['postal_code']) ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="city">Ville *</label>
            <input type="text" id="city" name="city" value="<?= htmlspecialchars($site['city']) ?>" required class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="building_type">Type de bâtiment</label>
            <input type="text" id="building_type" name="building_type" value="<?= htmlspecialchars($site['building_type'] ?? '') ?>" class="form-control">
        </div>

        <div class="form-group">
            <label for="construction_year">Année de construction</label>
            <input type="number" id="construction_year" name="construction_year" value="<?= $site['construction_year'] ?? '' ?>" class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="surface">Surface (m²)</label>
            <input type="number" id="surface" name="surface" value="<?= $site['surface'] ?? '' ?>" class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="latitude">Latitude</label>
            <input type="number" step="0.000001" id="latitude" name="latitude" value="<?= $site['latitude'] ?? '' ?>" class="form-control">
        </div>

        <div class="form-group">
            <label for="longitude">Longitude</label>
            <input type="number" step="0.000001" id="longitude" name="longitude" value="<?= $site['longitude'] ?? '' ?>" class="form-control">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </div>
</form>
