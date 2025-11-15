<h1>Modifier le client : <?= htmlspecialchars($client['organization_name']) ?></h1>

<div class="page-actions">
    <a href="/clients/<?= $client['id'] ?>" class="btn">
        <span>←</span> Retour aux détails
    </a>
</div>

<form method="POST" action="/clients/<?= $client['id'] ?>/update" class="client-edit-form">
    <!-- Informations de l'organisation -->
    <div class="card">
        <div class="card-header">
            <h2>🏢 Informations de l'organisation</h2>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label for="organization_name">Nom de l'organisation *</label>
                    <input type="text" id="organization_name" name="organization_name"
                           value="<?= htmlspecialchars($client['organization_name']) ?>"
                           required class="form-control">
                </div>

                <div class="form-group">
                    <label for="siret">SIRET</label>
                    <input type="text" id="siret" name="siret"
                           value="<?= htmlspecialchars($client['siret'] ?? '') ?>"
                           class="form-control"
                           placeholder="14 chiffres"
                           pattern="[0-9]{14}">
                    <small class="form-text">Format: 14 chiffres</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact principal -->
    <div class="card">
        <div class="card-header">
            <h2>👤 Contact principal</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="contact_name">Nom du contact *</label>
                <input type="text" id="contact_name" name="contact_name"
                       value="<?= htmlspecialchars($client['contact_name']) ?>"
                       required class="form-control"
                       placeholder="Prénom NOM">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($client['email']) ?>"
                           required class="form-control">
                </div>

                <div class="form-group">
                    <label for="phone">Téléphone</label>
                    <input type="tel" id="phone" name="phone"
                           value="<?= htmlspecialchars($client['phone'] ?? '') ?>"
                           class="form-control"
                           placeholder="01 23 45 67 89">
                </div>
            </div>
        </div>
    </div>

    <!-- Adresse -->
    <div class="card">
        <div class="card-header">
            <h2>📍 Adresse</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="address">Adresse *</label>
                <input type="text" id="address" name="address"
                       value="<?= htmlspecialchars($client['address']) ?>"
                       required class="form-control"
                       placeholder="Numéro et nom de rue">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="postal_code">Code postal *</label>
                    <input type="text" id="postal_code" name="postal_code"
                           value="<?= htmlspecialchars($client['postal_code']) ?>"
                           required class="form-control"
                           pattern="[0-9]{5}"
                           placeholder="75001">
                    <small class="form-text">5 chiffres</small>
                </div>

                <div class="form-group">
                    <label for="city">Ville *</label>
                    <input type="text" id="city" name="city"
                           value="<?= htmlspecialchars($client['city']) ?>"
                           required class="form-control">
                </div>
            </div>
        </div>
    </div>

    <!-- Notes et statut -->
    <div class="card">
        <div class="card-header">
            <h2>📝 Notes et statut</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="notes">Notes internes</label>
                <textarea id="notes" name="notes" rows="4" class="form-control"
                          placeholder="Notes visibles uniquement par l'équipe..."><?= htmlspecialchars($client['notes'] ?? '') ?></textarea>
                <small class="form-text">Ces notes ne sont pas visibles par le client</small>
            </div>

            <div class="form-group-checkbox">
                <label class="checkbox-label">
                    <input type="checkbox" name="active" value="1" <?= $client['active'] ? 'checked' : '' ?>>
                    <span class="checkbox-text">
                        <strong>Client actif</strong>
                        <small>Décochez pour désactiver le client sans le supprimer</small>
                    </span>
                </label>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="form-actions-sticky">
        <button type="submit" class="btn btn-primary btn-lg">
            💾 Enregistrer les modifications
        </button>
        <a href="/clients/<?= $client['id'] ?>" class="btn btn-secondary btn-lg">
            Annuler
        </a>
    </div>
</form>

<style>
.client-edit-form {
    max-width: 900px;
    margin: 0 auto;
    padding-bottom: 100px;
}

.card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.card-header {
    padding: 20px 25px;
    border-bottom: 2px solid #f0f0f0;
    background: linear-gradient(to right, #f8f9fa, #ffffff);
}

.card-header h2 {
    margin: 0;
    font-size: 1.3em;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.card-body {
    padding: 25px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 600;
    font-size: 0.95em;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 15px;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-control:invalid {
    border-color: #e74c3c;
}

.form-text {
    display: block;
    margin-top: 5px;
    font-size: 0.85em;
    color: #7f8c8d;
}

.form-group-checkbox {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 2px solid #e9ecef;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    cursor: pointer;
    margin: 0;
}

.checkbox-label input[type="checkbox"] {
    width: 20px;
    height: 20px;
    margin-right: 12px;
    margin-top: 2px;
    cursor: pointer;
}

.checkbox-text {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.checkbox-text strong {
    color: #2c3e50;
    font-size: 1em;
}

.checkbox-text small {
    color: #7f8c8d;
    font-size: 0.9em;
}

.form-actions-sticky {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 2px solid #e0e0e0;
    padding: 15px 20px;
    display: flex;
    gap: 15px;
    justify-content: center;
    box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
    z-index: 1000;
}

.btn-lg {
    padding: 14px 32px;
    font-size: 16px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .client-edit-form {
        padding-bottom: 120px;
    }

    .form-actions-sticky {
        flex-direction: column;
    }
}
</style>
