<div class="page-header">
    <h1>Nouvelle Cession</h1>
    <p class="subtitle">Créer une cession de sites entre clients</p>
</div>

<div class="form-container">
    <form id="cessionForm" style="max-width: 800px;">
        <div class="form-section">
            <h3>Informations de la cession</h3>

            <div class="form-group">
                <label for="title" class="required">Titre de la cession</label>
                <input type="text" id="title" name="title" class="form-control" required
                       placeholder="Ex: Cession des sites région Ouest">
                <small class="form-text">Un titre descriptif pour identifier cette cession</small>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"
                          placeholder="Description détaillée de la cession (optionnel)"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="from_client_id" class="required">Client cédant</label>
                    <select id="from_client_id" name="from_client_id" class="form-control" required>
                        <option value="">-- Sélectionner le client cédant --</option>
                        <?php foreach ($clients ?? [] as $client): ?>
                        <option value="<?= $client['id'] ?>">
                            <?= htmlspecialchars($client['organization_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text">Le client qui transfère les sites</small>
                </div>

                <div class="form-group">
                    <label for="to_client_id" class="required">Client cessionnaire</label>
                    <select id="to_client_id" name="to_client_id" class="form-control" required>
                        <option value="">-- Sélectionner le client cessionnaire --</option>
                        <?php foreach ($clients ?? [] as $client): ?>
                        <option value="<?= $client['id'] ?>">
                            <?= htmlspecialchars($client['organization_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text">Le client qui reçoit les sites</small>
                </div>
            </div>

            <div class="form-group">
                <label for="effective_date">Date effective du transfert</label>
                <input type="date" id="effective_date" name="effective_date" class="form-control">
                <small class="form-text">Date prévue pour le transfert effectif des sites</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="icon-save"></i> Créer la cession
            </button>
            <a href="/cessions" class="btn btn-secondary">Annuler</a>
        </div>

        <div id="errorMessage" class="alert alert-danger" style="display: none; margin-top: 20px;"></div>
        <div id="successMessage" class="alert alert-success" style="display: none; margin-top: 20px;"></div>
    </form>
</div>

<style>
.form-container {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-section {
    margin-bottom: 30px;
}

.form-section h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.form-group label.required::after {
    content: ' *';
    color: #e74c3c;
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

.form-text {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: #666;
}

.form-actions {
    display: flex;
    gap: 10px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.alert {
    padding: 15px;
    border-radius: 4px;
}

.alert-danger {
    background-color: #fee;
    border: 1px solid #fcc;
    color: #c33;
}

.alert-success {
    background-color: #efe;
    border: 1px solid #cfc;
    color: #3c3;
}

.btn {
    padding: 10px 20px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background-color: #3498db;
    color: white;
}

.btn-primary:hover {
    background-color: #2980b9;
}

.btn-secondary {
    background-color: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background-color: #7f8c8d;
}

.page-header {
    margin-bottom: 30px;
}

.page-header .subtitle {
    color: #666;
    font-size: 14px;
    margin-top: 5px;
}
</style>

<script>
document.getElementById('cessionForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const errorMessage = document.getElementById('errorMessage');
    const successMessage = document.getElementById('successMessage');

    // Reset messages
    errorMessage.style.display = 'none';
    successMessage.style.display = 'none';

    // Validation
    const fromClientId = document.getElementById('from_client_id').value;
    const toClientId = document.getElementById('to_client_id').value;

    if (fromClientId === toClientId) {
        errorMessage.textContent = 'Le client cédant et le client cessionnaire doivent être différents';
        errorMessage.style.display = 'block';
        return;
    }

    // Prepare data
    const formData = {
        title: document.getElementById('title').value,
        description: document.getElementById('description').value,
        from_client_id: fromClientId,
        to_client_id: toClientId,
        effective_date: document.getElementById('effective_date').value || null
    };

    // Disable button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="icon-spinner"></i> Création en cours...';

    try {
        const response = await fetch('/cessions/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            successMessage.textContent = result.message;
            successMessage.style.display = 'block';

            // Rediriger vers la page de détails après 1 seconde
            setTimeout(() => {
                window.location.href = '/cessions/' + result.cession_id;
            }, 1000);
        } else {
            errorMessage.textContent = result.message || 'Une erreur est survenue';
            errorMessage.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="icon-save"></i> Créer la cession';
        }
    } catch (error) {
        console.error('Error:', error);
        errorMessage.textContent = 'Erreur de communication avec le serveur';
        errorMessage.style.display = 'block';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="icon-save"></i> Créer la cession';
    }
});

// Validation en temps réel
document.getElementById('from_client_id').addEventListener('change', validateClients);
document.getElementById('to_client_id').addEventListener('change', validateClients);

function validateClients() {
    const fromClientId = document.getElementById('from_client_id').value;
    const toClientId = document.getElementById('to_client_id').value;
    const errorMessage = document.getElementById('errorMessage');

    if (fromClientId && toClientId && fromClientId === toClientId) {
        errorMessage.textContent = 'Le client cédant et le client cessionnaire doivent être différents';
        errorMessage.style.display = 'block';
    } else {
        errorMessage.style.display = 'none';
    }
}
</script>
