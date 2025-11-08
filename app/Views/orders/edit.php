<div class="page-header">
    <div class="page-header-content">
        <h1>📝 Modifier la commande #<?= htmlspecialchars($order['order_number']) ?></h1>
        <div class="breadcrumb">
            <a href="/dashboard">Tableau de bord</a>
            <span>/</span>
            <a href="/orders">Commandes</a>
            <span>/</span>
            <a href="/orders/<?= $order['id'] ?>">#<?= htmlspecialchars($order['order_number']) ?></a>
            <span>/</span>
            <span class="current">Modifier</span>
        </div>
    </div>
    <div class="page-actions">
        <a href="/orders/<?= $order['id'] ?>" class="btn btn-secondary">
            <span>←</span> Annuler
        </a>
    </div>
</div>

<form method="POST" action="/orders/<?= $order['id'] ?>/update" class="edit-form">

    <!-- Informations client et site -->
    <div class="card">
        <div class="card-header">
            <h2>🏢 Client et site</h2>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label for="client_id">
                        Client *
                        <span class="field-hint">Organisation cliente</span>
                    </label>
                    <select id="client_id" name="client_id" required class="form-control">
                        <option value="">Sélectionner un client</option>
                        <?php foreach ($clients as $client): ?>
                        <option value="<?= $client['id'] ?>" <?= $order['client_id'] == $client['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($client['organization_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="site_id">
                        Site *
                        <span class="field-hint">Lieu d'intervention</span>
                    </label>
                    <select id="site_id" name="site_id" required class="form-control">
                        <option value="">Sélectionner un site</option>
                        <?php foreach ($sites as $site): ?>
                        <option value="<?= $site['id'] ?>" <?= $order['site_id'] == $site['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($site['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Dates et priorité -->
    <div class="card">
        <div class="card-header">
            <h2>📅 Planification</h2>
        </div>
        <div class="card-body">
            <div class="form-grid form-grid-3">
                <div class="form-group">
                    <label for="requested_date">
                        Date souhaitée
                        <span class="field-hint">Date demandée par le client</span>
                    </label>
                    <input type="date"
                           id="requested_date"
                           name="requested_date"
                           value="<?= $order['requested_date'] ? date('Y-m-d', strtotime($order['requested_date'])) : '' ?>"
                           class="form-control">
                </div>

                <div class="form-group">
                    <label for="deadline_date">
                        Date limite
                        <span class="field-hint">Échéance maximale</span>
                    </label>
                    <input type="date"
                           id="deadline_date"
                           name="deadline_date"
                           value="<?= $order['deadline_date'] ? date('Y-m-d', strtotime($order['deadline_date'])) : '' ?>"
                           class="form-control">
                </div>

                <div class="form-group">
                    <label for="priority">
                        Priorité
                        <span class="field-hint">Niveau d'urgence</span>
                    </label>
                    <select id="priority" name="priority" class="form-control">
                        <option value="low" <?= ($order['priority'] ?? 'normal') == 'low' ? 'selected' : '' ?>>🟢 Basse</option>
                        <option value="normal" <?= ($order['priority'] ?? 'normal') == 'normal' ? 'selected' : '' ?>>🔵 Normale</option>
                        <option value="high" <?= ($order['priority'] ?? 'normal') == 'high' ? 'selected' : '' ?>>🟠 Haute</option>
                        <option value="urgent" <?= ($order['priority'] ?? 'normal') == 'urgent' ? 'selected' : '' ?>>🔴 Urgente</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Affectation -->
    <div class="card">
        <div class="card-header">
            <h2>👤 Affectation</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="assigned_to">
                    Technicien assigné
                    <span class="field-hint">Responsable de l'exécution</span>
                </label>
                <select id="assigned_to" name="assigned_to" class="form-control">
                    <option value="">Non assigné</option>
                    <?php foreach ($technicians as $tech): ?>
                    <option value="<?= $tech['id'] ?>" <?= $order['assigned_to'] == $tech['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Description et notes -->
    <div class="card">
        <div class="card-header">
            <h2>📄 Description et notes</h2>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label for="description">
                    Description de la commande
                    <span class="field-hint">Détails de la mission</span>
                </label>
                <textarea id="description"
                          name="description"
                          rows="4"
                          class="form-control"
                          placeholder="Décrivez les travaux à réaliser, les objectifs, les spécificités..."><?= htmlspecialchars($order['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="notes">
                    Notes internes
                    <span class="field-hint">Information non visible par le client</span>
                </label>
                <textarea id="notes"
                          name="notes"
                          rows="3"
                          class="form-control"
                          placeholder="Notes pour l'équipe, remarques techniques..."><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Barre d'actions sticky -->
    <div class="form-actions-sticky">
        <button type="submit" class="btn btn-primary btn-lg">
            💾 Enregistrer les modifications
        </button>
        <a href="/orders/<?= $order['id'] ?>" class="btn btn-secondary btn-lg">
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

textarea.form-control {
    resize: vertical;
    min-height: 100px;
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

<script>
// Filtrer les sites en fonction du client sélectionné
document.getElementById('client_id').addEventListener('change', function() {
    const clientId = this.value;
    const siteSelect = document.getElementById('site_id');

    if (!clientId) {
        siteSelect.value = '';
        return;
    }

    // Dans une version future, on pourrait charger les sites via AJAX
    // Pour l'instant, on garde tous les sites visibles
});
</script>
