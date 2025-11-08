<h1>Modifier la commande #<?= htmlspecialchars($order['order_number']) ?></h1>

<div class="page-actions">
    <a href="/orders/<?= $order['id'] ?>" class="btn">Retour aux détails</a>
</div>

<form method="POST" action="/orders/<?= $order['id'] ?>/update" class="form-container">
    <div class="form-row">
        <div class="form-group">
            <label for="client_id">Client *</label>
            <select id="client_id" name="client_id" required class="form-control">
                <?php foreach ($clients as $client): ?>
                <option value="<?= $client['id'] ?>" <?= $order['client_id'] == $client['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($client['organization_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="site_id">Site *</label>
            <select id="site_id" name="site_id" required class="form-control">
                <?php foreach ($sites as $site): ?>
                <option value="<?= $site['id'] ?>" <?= $order['site_id'] == $site['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($site['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="requested_date">Date souhaitée</label>
            <input type="date" id="requested_date" name="requested_date" value="<?= $order['requested_date'] ? date('Y-m-d', strtotime($order['requested_date'])) : '' ?>" class="form-control">
        </div>

        <div class="form-group">
            <label for="deadline_date">Date limite</label>
            <input type="date" id="deadline_date" name="deadline_date" value="<?= $order['deadline_date'] ? date('Y-m-d', strtotime($order['deadline_date'])) : '' ?>" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label for="priority">Priorité</label>
        <select id="priority" name="priority" class="form-control">
            <option value="low" <?= ($order['priority'] ?? 'normal') == 'low' ? 'selected' : '' ?>>Basse</option>
            <option value="normal" <?= ($order['priority'] ?? 'normal') == 'normal' ? 'selected' : '' ?>>Normale</option>
            <option value="high" <?= ($order['priority'] ?? 'normal') == 'high' ? 'selected' : '' ?>>Haute</option>
            <option value="urgent" <?= ($order['priority'] ?? 'normal') == 'urgent' ? 'selected' : '' ?>>Urgente</option>
        </select>
    </div>

    <div class="form-group">
        <label for="assigned_to">Technicien assigné</label>
        <select id="assigned_to" name="assigned_to" class="form-control">
            <option value="">Non assigné</option>
            <?php foreach ($technicians as $tech): ?>
            <option value="<?= $tech['id'] ?>" <?= $order['assigned_to'] == $tech['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($tech['first_name'] . ' ' . $tech['last_name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" class="form-control"><?= htmlspecialchars($order['description'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label for="notes">Notes internes</label>
        <textarea id="notes" name="notes" rows="3" class="form-control"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </div>
</form>
