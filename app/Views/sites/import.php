<h1>📥 Import Patrimoine Excel</h1>

<div class="import-container">
    <!-- Étape 1: Sélection client et upload -->
    <div class="card" id="step1">
        <h2>Étape 1 : Sélection du client et fichier</h2>

        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="client_id">Client <span class="required">*</span></label>
                <select name="client_id" id="client_id" class="form-control" required>
                    <option value="">-- Sélectionner un client --</option>
                    <?php foreach ($clients ?? [] as $client): ?>
                        <option value="<?= $client['id'] ?>">
                            <?= htmlspecialchars($client['organization_name']) ?>
                            (<?= $client['sites_count'] ?? 0 ?> sites)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="excel_file">Fichier Excel <span class="required">*</span></label>
                <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xls,.xlsx" required>
                <small class="form-text">Formats acceptés : .xls, .xlsx (max 10 Mo)</small>
            </div>

            <button type="submit" class="btn btn-primary">
                📤 Uploader et prévisualiser
            </button>
        </form>
    </div>

    <!-- Étape 2: Mappage des colonnes (caché au départ) -->
    <div class="card" id="step2" style="display: none;">
        <h2>Étape 2 : Mappage des colonnes</h2>

        <div class="alert alert-info">
            <strong>ℹ️ Instructions pour le mappage manuel :</strong>
            <ol style="margin: 10px 0; padding-left: 20px;">
                <li><strong>Identifiez vos colonnes Excel :</strong> Consultez le tableau d'aperçu ci-dessous pour voir les colonnes de votre fichier (A, B, C, etc.)</li>
                <li><strong>Faites correspondre les colonnes :</strong> Pour chaque champ de la base de données, sélectionnez la colonne Excel correspondante</li>
                <li><strong>Vérifiez l'aperçu :</strong> La colonne "Aperçu" affiche un exemple de donnée pour vérifier que le mappage est correct</li>
                <li><strong>Champs obligatoires :</strong> Les lignes surlignées en <span style="background: #fff9e6; padding: 2px 6px;">jaune</span> avec une <span class="required">*</span> sont obligatoires</li>
            </ol>
            <p><strong>💡 Astuce :</strong> Commencez par les champs obligatoires (*), puis mappez les champs optionnels selon vos besoins.</p>
        </div>

        <div id="previewData"></div>

        <form id="mappingForm">
            <input type="hidden" name="client_id" id="mapping_client_id">
            <input type="hidden" name="filepath" id="filepath">

            <h3>Correspondance des colonnes</h3>
            <div class="mapping-grid" id="mappingGrid"></div>

            <div class="form-actions" style="margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="resetImport()">
                    ← Annuler
                </button>
                <button type="submit" class="btn btn-success">
                    ✓ Lancer l'import
                </button>
            </div>
        </form>
    </div>

    <!-- Étape 3: Résultats (caché au départ) -->
    <div class="card" id="step3" style="display: none;">
        <h2>Étape 3 : Résultats de l'import</h2>
        <div id="importResults"></div>
        <button type="button" class="btn btn-primary" onclick="window.location.href='/sites'">
            → Voir les sites
        </button>
        <button type="button" class="btn btn-secondary" onclick="resetImport()">
            📥 Nouvel import
        </button>
    </div>
</div>

<style>
.import-container {
    max-width: 1200px;
    margin: 0 auto;
}

.card {
    background: white;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-text {
    display: block;
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}

.required {
    color: red;
}

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-info {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
}

.alert-success {
    background: #e8f5e9;
    border-left: 4px solid #4caf50;
}

.alert-warning {
    background: #fff3e0;
    border-left: 4px solid #ff9800;
}

.alert-danger {
    background: #ffebee;
    border-left: 4px solid #f44336;
}

.mapping-grid {
    margin-bottom: 20px;
}

.mapping-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    font-size: 14px;
}

.mapping-table thead {
    background: #2196f3;
    color: white;
}

.mapping-table th {
    padding: 12px;
    text-align: left;
    font-weight: bold;
}

.mapping-table td {
    padding: 10px;
    border: 1px solid #ddd;
}

.mapping-table tbody tr {
    background: white;
}

.mapping-table tbody tr:hover {
    background: #f5f5f5;
}

.mapping-table tbody tr.mapping-required {
    background: #fff9e6;
}

.mapping-table tbody tr.mapping-required:hover {
    background: #fff3cd;
}

.mapping-select {
    width: 100%;
    min-width: 200px;
}

.preview-cell {
    background: #fafafa;
    font-family: monospace;
    font-size: 12px;
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.preview-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    font-size: 12px;
}

.preview-table th,
.preview-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.preview-table th {
    background: #f5f5f5;
    font-weight: bold;
}

.preview-table tbody tr:nth-child(even) {
    background: #fafafa;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
}

.btn-primary {
    background: #2196f3;
    color: white;
}

.btn-success {
    background: #4caf50;
    color: white;
}

.btn-secondary {
    background: #757575;
    color: white;
}

.btn:hover {
    opacity: 0.9;
}

.loading {
    text-align: center;
    padding: 20px;
}
</style>

<script>
let uploadedData = null;

// Upload et prévisualisation
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const clientId = document.getElementById('client_id').value;
    const fileInput = document.getElementById('excel_file');

    if (!clientId || !fileInput.files[0]) {
        alert('Veuillez sélectionner un client et un fichier');
        return;
    }

    const formData = new FormData();
    formData.append('client_id', clientId);
    formData.append('excel_file', fileInput.files[0]);

    try {
        document.querySelector('#step1 .btn-primary').textContent = '⏳ Chargement...';

        const response = await fetch('/sites/upload-excel', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            uploadedData = data;
            showMappingStep(data, clientId);
        } else {
            alert('Erreur : ' + (data.error || 'Erreur inconnue'));
        }
    } catch (error) {
        alert('Erreur lors de l\'upload : ' + error.message);
    } finally {
        document.querySelector('#step1 .btn-primary').textContent = '📤 Uploader et prévisualiser';
    }
});

// Afficher l'étape de mappage
function showMappingStep(data, clientId) {
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';

    // Sauvegarder les infos
    document.getElementById('mapping_client_id').value = clientId;
    document.getElementById('filepath').value = data.filepath;

    // Afficher la prévisualisation
    let previewHtml = `
        <p><strong>Fichier :</strong> ${data.filename}</p>
        <p><strong>Nombre total de lignes :</strong> ${data.totalRows}</p>
        <h4>Aperçu des données (5 premières lignes) :</h4>
        <div style="overflow-x: auto;">
            <table class="preview-table">
                <thead>
                    <tr>${data.headers.map(h => `<th>${h || '(vide)'}</th>`).join('')}</tr>
                </thead>
                <tbody>
                    ${data.sampleRows.map(row =>
                        `<tr>${row.map(cell => `<td>${cell || '-'}</td>`).join('')}</tr>`
                    ).join('')}
                </tbody>
            </table>
        </div>
    `;
    document.getElementById('previewData').innerHTML = previewHtml;

    // Générer le mappage
    const fields = [
        { name: 'numero_groupe', label: 'Numéro de groupe', required: true, description: 'Identifiant unique du groupe de lots' },
        { name: 'numero_lot', label: 'Numéro de lot', required: true, description: 'Numéro du lot dans le groupe' },
        { name: 'nom_groupe', label: 'Nom du groupe', required: false, description: 'Nom descriptif du groupe (optionnel)' },
        { name: 'address', label: 'Adresse', required: true, description: 'Adresse complète du site' },
        { name: 'city', label: 'Ville', required: true, description: 'Ville où se trouve le site' },
        { name: 'postal_code', label: 'Code postal', required: true, description: 'Code postal (5 chiffres)' },
        { name: 'numero_porte', label: 'Numéro de porte', required: false, description: 'Numéro de porte ou d\'appartement' },
        { name: 'niveau', label: 'Niveau', required: false, description: 'Étage ou niveau du lot' },
        { name: 'identifiant_fiscal', label: 'Identifiant fiscal', required: false, description: 'Référence cadastrale ou fiscale' },
        { name: 'nommage_rapport', label: 'Nommage rapport', required: false, description: 'Format de nom pour les rapports' },
        { name: 'numero_batiment', label: 'Numéro de bâtiment', required: false, description: 'Numéro ou nom du bâtiment' },
        { name: 'numero_entree', label: 'Numéro d\'entrée', required: false, description: 'Numéro d\'entrée ou de cage d\'escalier' }
    ];

    let mappingHtml = '<table class="mapping-table"><thead><tr><th>Champ de la base</th><th>Description</th><th>Colonne Excel</th><th>Aperçu</th></tr></thead><tbody>';

    fields.forEach(field => {
        const requiredMark = field.required ? ' <span class="required">*</span>' : '';
        const requiredClass = field.required ? 'mapping-required' : '';

        mappingHtml += `
            <tr class="${requiredClass}">
                <td><strong>${field.label}${requiredMark}</strong></td>
                <td><small>${field.description}</small></td>
                <td>
                    <select name="mapping[${field.name}]" class="form-control mapping-select" ${field.required ? 'required' : ''} data-field="${field.name}">
                        <option value="">-- Sélectionner une colonne --</option>
                        ${data.headers.map((header, index) => {
                            const columnLetter = String.fromCharCode(65 + index);
                            return `<option value="${columnLetter}">${columnLetter} - ${header || '(colonne vide)'}</option>`;
                        }).join('')}
                    </select>
                </td>
                <td class="preview-cell" id="preview_${field.name}">-</td>
            </tr>
        `;
    });

    mappingHtml += '</tbody></table>';

    document.getElementById('mappingGrid').innerHTML = mappingHtml;

    // Ajouter un listener pour afficher l'aperçu lors du changement de mappage
    document.querySelectorAll('.mapping-select').forEach(select => {
        select.addEventListener('change', function() {
            const fieldName = this.dataset.field;
            const columnLetter = this.value;
            const previewCell = document.getElementById('preview_' + fieldName);

            if (columnLetter) {
                const columnIndex = columnLetter.charCodeAt(0) - 65;
                const sampleValue = data.sampleRows[0] && data.sampleRows[0][columnIndex]
                    ? data.sampleRows[0][columnIndex]
                    : '-';
                previewCell.textContent = sampleValue;
                previewCell.style.background = '#e8f5e9';
            } else {
                previewCell.textContent = '-';
                previewCell.style.background = '';
            }
        });
    });
}

// Traiter l'import
document.getElementById('mappingForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    // Convertir le mapping en objet
    const mapping = {};
    formData.forEach((value, key) => {
        if (key.startsWith('mapping[') && value) {
            const fieldName = key.match(/mapping\[(.+)\]/)[1];
            mapping[fieldName] = value;
        }
    });

    try {
        document.querySelector('#step2 .btn-success').textContent = '⏳ Import en cours...';

        const response = await fetch('/sites/process-import', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                client_id: formData.get('client_id'),
                filepath: formData.get('filepath'),
                mapping: JSON.stringify(mapping)
            })
        });

        const data = await response.json();

        if (data.success) {
            showResults(data.results);
        } else {
            alert('Erreur : ' + (data.error || 'Erreur inconnue'));
        }
    } catch (error) {
        alert('Erreur lors de l\'import : ' + error.message);
    } finally {
        document.querySelector('#step2 .btn-success').textContent = '✓ Lancer l\'import';
    }
});

// Afficher les résultats
function showResults(results) {
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'block';

    let html = '<div class="alert alert-success">';
    html += `<h3>✅ Import terminé avec succès</h3>`;
    html += `<p><strong>${results.success}</strong> sites importés/mis à jour</p>`;
    html += '</div>';

    if (results.warnings && results.warnings.length > 0) {
        html += '<div class="alert alert-warning">';
        html += `<h4>⚠️ Avertissements (${results.warnings.length})</h4>`;
        html += '<ul>';
        results.warnings.slice(0, 10).forEach(warning => {
            html += `<li>${warning}</li>`;
        });
        if (results.warnings.length > 10) {
            html += `<li><em>... et ${results.warnings.length - 10} autres avertissements</em></li>`;
        }
        html += '</ul></div>';
    }

    if (results.errors && results.errors.length > 0) {
        html += '<div class="alert alert-danger">';
        html += `<h4>❌ Erreurs (${results.errors.length})</h4>`;
        html += '<ul>';
        results.errors.slice(0, 10).forEach(error => {
            html += `<li>${error}</li>`;
        });
        if (results.errors.length > 10) {
            html += `<li><em>... et ${results.errors.length - 10} autres erreurs</em></li>`;
        }
        html += '</ul></div>';
    }

    document.getElementById('importResults').innerHTML = html;
}

// Réinitialiser l'import
function resetImport() {
    document.getElementById('step1').style.display = 'block';
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'none';
    document.getElementById('uploadForm').reset();
    uploadedData = null;
}
</script>
