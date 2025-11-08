<h1>Créer un site</h1>

<div class="page-actions">
    <a href="/sites" class="btn">Retour à la liste</a>
</div>

<form method="POST" action="/sites/store" class="form-container">
    <div class="form-group">
        <label for="client_id">Client *</label>
        <select id="client_id" name="client_id" required class="form-control">
            <option value="">Sélectionner un client</option>
            <?php foreach ($clients as $client): ?>
            <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['organization_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="name">Nom du site *</label>
            <input type="text" id="name" name="name" required class="form-control">
        </div>

        <div class="form-group">
            <label for="reference_pch">Référence PCH</label>
            <input type="text" id="reference_pch" name="reference_pch" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="address">Adresse *</label>
        <input type="text" id="address" name="address" required class="form-control">
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="postal_code">Code postal *</label>
            <input type="text" id="postal_code" name="postal_code" required class="form-control">
        </div>

        <div class="form-group">
            <label for="city">Ville *</label>
            <input type="text" id="city" name="city" required class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="building_type">Type de bâtiment</label>
            <input type="text" id="building_type" name="building_type" class="form-control">
        </div>

        <div class="form-group">
            <label for="construction_year">Année de construction</label>
            <input type="number" id="construction_year" name="construction_year" class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="surface">Surface (m²)</label>
            <input type="number" id="surface" name="surface" class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="latitude">Latitude</label>
            <input type="number" step="0.000001" id="latitude" name="latitude" class="form-control">
        </div>

        <div class="form-group">
            <label for="longitude">Longitude</label>
            <input type="number" step="0.000001" id="longitude" name="longitude" class="form-control">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Créer le site</button>
    </div>
</form>
