<h1>Rapports</h1>

<div class="page-actions">
    <button class="btn btn-primary" onclick="document.getElementById('uploadModal').style.display='block'">
        Téléverser un rapport
    </button>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Commande</th>
            <th>Client</th>
            <th>Fichier</th>
            <th>Taille</th>
            <th>Téléversé par</th>
            <th>Date</th>
            <th>Téléchargements</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reports ?? [] as $report): ?>
        <tr>
            <td>
                <a href="/orders/<?= $report['order_id'] ?>">
                    <?= htmlspecialchars($report['order_number'] ?? 'N/A') ?>
                </a>
            </td>
            <td><?= htmlspecialchars($report['client_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($report['original_filename'] ?? $report['filename'] ?? 'N/A') ?></td>
            <td><?= number_format(($report['file_size'] ?? 0) / 1024, 2) ?> KB</td>
            <td>
                <?= htmlspecialchars(($report['uploader_first_name'] ?? '') . ' ' . ($report['uploader_last_name'] ?? '')) ?>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($report['uploaded_at'])) ?></td>
            <td><?= $report['download_count'] ?? 0 ?></td>
            <td>
                <a href="/reports/<?= $report['id'] ?>/download" class="btn btn-sm">Télécharger</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if (empty($reports)): ?>
<p class="no-data">Aucun rapport disponible</p>
<?php endif; ?>

<!-- Modal Upload (simplifié) -->
<div id="uploadModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="background:white; margin:50px auto; padding:20px; max-width:500px; border-radius:8px;">
        <h2>Téléverser un rapport</h2>
        <form action="/reports/upload" method="POST" enctype="multipart/form-data">
            <div style="margin-bottom:15px;">
                <label>Commande ID:</label>
                <input type="number" name="order_id" required>
            </div>
            <div style="margin-bottom:15px;">
                <label>Fichier PDF:</label>
                <input type="file" name="file" accept=".pdf" required>
            </div>
            <button type="submit" class="btn btn-primary">Téléverser</button>
            <button type="button" class="btn" onclick="document.getElementById('uploadModal').style.display='none'">Annuler</button>
        </form>
    </div>
</div>
