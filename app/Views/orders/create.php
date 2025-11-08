<h1>Créer une commande</h1>

<div class="page-actions">
    <a href="/orders" class="btn">Retour à la liste</a>
</div>

<form method="POST" action="/orders/store" class="form-container">
    <div class="form-row">
        <div class="form-group">
            <label for="client_id">Client *</label>
            <select id="client_id" name="client_id" required class="form-control">
                <option value="">Sélectionner un client</option>
                <?php foreach ($clients as $client): ?>
                <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['organization_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="site_id">Site *</label>
            <select id="site_id" name="site_id" required class="form-control">
                <option value="">Sélectionner un site</option>
                <?php foreach ($sites as $site): ?>
                <option value="<?= $site['id'] ?>"><?= htmlspecialchars($site['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="order_date">Date de commande *</label>
            <input type="date" id="order_date" name="order_date" value="<?= date('Y-m-d') ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="desired_date">Date souhaitée</label>
            <input type="date" id="desired_date" name="desired_date" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" class="form-control"></textarea>
    </div>

    <div class="form-group">
        <label for="notes">Notes internes</label>
        <textarea id="notes" name="notes" rows="3" class="form-control"></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Créer la commande</button>
    </div>
</form>
