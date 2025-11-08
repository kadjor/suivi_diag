<div class="page-header">
    <div class="page-header-content">
        <h1>🏗️ Modifier le site</h1>
        <div class="breadcrumb">
            <a href="/dashboard">Tableau de bord</a>
            <span>/</span>
            <a href="/sites">Sites</a>
            <span>/</span>
            <a href="/sites/<?= $site['id'] ?>"><?= htmlspecialchars($site['name']) ?></a>
            <span>/</span>
            <span class="current">Modifier</span>
        </div>
    </div>
    <div class="page-actions">
        <a href="/sites/<?= $site['id'] ?>" class="btn btn-secondary">
            <span>←</span> Annuler
        </a>
    </div>
</div>

<form method="POST" action="/sites/<?= $site['id'] ?>/update" class="edit-form">

    <!-- Client -->
    <div class="card">
        <div class="card-header">
            <h2>🏢 Organisation</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="client_id">
                    Client propriétaire *
                    <span class="field-hint">Organisation à laquelle appartient ce site</span>
                </label>
                <select id="client_id" name="client_id" required class="form-control">
                    <option value="">Sélectionner un client</option>
                    <?php foreach ($clients as $client): ?>
                    <option value="<?= $client['id'] ?>" <?= $site['client_id'] == $client['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($client['organization_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Informations générales -->
    <div class="card">
        <div class="card-header">
            <h2>📍 Informations générales</h2>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">
                        Nom du site *
                        <span class="field-hint">Désignation du site</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="<?= htmlspecialchars($site['name']) ?>"
                           required
                           class="form-control"
                           placeholder="Ex: Siège social, Usine Nord...">
                </div>

                <div class="form-group">
                    <label for="reference_pch">
                        Référence PCH
                        <span class="field-hint">Référence technique</span>
                    </label>
                    <input type="text"
                           id="reference_pch"
                           name="reference_pch"
                           value="<?= htmlspecialchars($site['reference_pch'] ?? '') ?>"
                           class="form-control"
                           placeholder="PCH-XXXX">
                </div>
            </div>
        </div>
    </div>

    <!-- Adresse -->
    <div class="card">
        <div class="card-header">
            <h2>🗺️ Localisation</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="address">
                    Adresse *
                    <span class="field-hint">Numéro et nom de rue</span>
                </label>
                <input type="text"
                       id="address"
                       name="address"
                       value="<?= htmlspecialchars($site['address']) ?>"
                       required
                       class="form-control"
                       placeholder="12 Rue de la République">
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="postal_code">
                        Code postal *
                        <span class="field-hint">5 chiffres</span>
                    </label>
                    <input type="text"
                           id="postal_code"
                           name="postal_code"
                           value="<?= htmlspecialchars($site['postal_code']) ?>"
                           required
                           pattern="[0-9]{5}"
                           maxlength="5"
                           class="form-control"
                           placeholder="75001">
                </div>

                <div class="form-group">
                    <label for="city">
                        Ville *
                        <span class="field-hint">Commune</span>
                    </label>
                    <input type="text"
                           id="city"
                           name="city"
                           value="<?= htmlspecialchars($site['city']) ?>"
                           required
                           class="form-control"
                           placeholder="Paris">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="latitude">
                        Latitude
                        <span class="field-hint">Coordonnée GPS (optionnel)</span>
                    </label>
                    <input type="number"
                           step="0.000001"
                           id="latitude"
                           name="latitude"
                           value="<?= $site['latitude'] ?? '' ?>"
                           class="form-control"
                           placeholder="48.856614">
                </div>

                <div class="form-group">
                    <label for="longitude">
                        Longitude
                        <span class="field-hint">Coordonnée GPS (optionnel)</span>
                    </label>
                    <input type="number"
                           step="0.000001"
                           id="longitude"
                           name="longitude"
                           value="<?= $site['longitude'] ?? '' ?>"
                           class="form-control"
                           placeholder="2.352222">
                </div>
            </div>
        </div>
    </div>

    <!-- Caractéristiques du bâtiment -->
    <div class="card">
        <div class="card-header">
            <h2>🏛️ Caractéristiques du bâtiment</h2>
        </div>
        <div class="card-body">
            <div class="form-grid form-grid-3">
                <div class="form-group">
                    <label for="building_type">
                        Type de bâtiment
                        <span class="field-hint">Nature du bâtiment</span>
                    </label>
                    <input type="text"
                           id="building_type"
                           name="building_type"
                           value="<?= htmlspecialchars($site['building_type'] ?? '') ?>"
                           class="form-control"
                           placeholder="Bureaux, Entrepôt, Habitation...">
                </div>

                <div class="form-group">
                    <label for="construction_year">
                        Année de construction
                        <span class="field-hint">Année de construction</span>
                    </label>
                    <input type="number"
                           id="construction_year"
                           name="construction_year"
                           value="<?= $site['construction_year'] ?? '' ?>"
                           min="1800"
                           max="<?= date('Y') + 5 ?>"
                           class="form-control"
                           placeholder="<?= date('Y') ?>">
                </div>

                <div class="form-group">
                    <label for="surface">
                        Surface (m²)
                        <span class="field-hint">Surface totale</span>
                    </label>
                    <input type="number"
                           id="surface"
                           name="surface"
                           value="<?= $site['surface'] ?? '' ?>"
                           step="0.01"
                           min="0"
                           class="form-control"
                           placeholder="1500">
                </div>
            </div>
        </div>
    </div>

    <!-- Barre d'actions sticky -->
    <div class="form-actions-sticky">
        <button type="submit" class="btn btn-primary btn-lg">
            💾 Enregistrer les modifications
        </button>
        <a href="/sites/<?= $site['id'] ?>" class="btn btn-secondary btn-lg">
            Annuler
        </a>
    </div>
</form>

<style>
.page-header {
    background: white;
    padding: 20px;
    margin: -20px -20px 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-header-content h1 {
    margin: 0 0 10px 0;
    font-size: 24px;
    color: #2c3e50;
}

.breadcrumb {
    font-size: 14px;
    color: #666;
}

.breadcrumb a {
    color: #3498db;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.breadcrumb span.current {
    color: #95a5a6;
}

.edit-form {
    max-width: 1200px;
    margin: 0 auto;
    padding-bottom: 100px;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
}

.card-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.card-body {
    padding: 25px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.form-grid-3 {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

.form-group {
    margin-bottom: 20px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #2c3e50;
    font-size: 14px;
}

.field-hint {
    display: block;
    font-weight: normal;
    color: #7f8c8d;
    font-size: 12px;
    margin-top: 2px;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-actions-sticky {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    padding: 15px 20px;
    border-top: 2px solid #e0e0e0;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    display: flex;
    gap: 10px;
    justify-content: center;
    z-index: 1000;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.btn-lg {
    padding: 12px 30px;
    font-size: 16px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
}

/* Responsive */
@media (max-width: 768px) {
    .form-grid,
    .form-grid-3 {
        grid-template-columns: 1fr;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .form-actions-sticky {
        flex-direction: column;
    }

    .btn-lg {
        width: 100%;
        justify-content: center;
    }
}
</style>
