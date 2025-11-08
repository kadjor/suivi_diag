<h1>Gestion des statuts et couleurs</h1>

<div class="page-actions">
    <a href="/admin" class="btn">Retour à l'administration</a>
</div>

<div class="alert alert-info">
    <strong>💡 Astuce :</strong> Modifiez les couleurs des statuts pour une meilleure lisibilité.
    Les couleurs sont utilisées dans tout le système pour afficher les badges de statut.
</div>

<div class="statuses-container">
    <?php
    $categoryLabels = [
        'order' => ['label' => 'Commandes', 'icon' => '📦'],
        'intervention' => ['label' => 'Interventions', 'icon' => '🔧'],
        'diagnostic' => ['label' => 'Diagnostics', 'icon' => '📋']
    ];

    foreach ($statuses as $category => $categoryStatuses):
        $categoryInfo = $categoryLabels[$category] ?? ['label' => ucfirst($category), 'icon' => '📌'];
    ?>
    <div class="card">
        <div class="card-header">
            <h2><?= $categoryInfo['icon'] ?> <?= $categoryInfo['label'] ?></h2>
        </div>
        <div class="card-body">
            <div class="statuses-grid">
                <?php foreach ($categoryStatuses as $status): ?>
                <div class="status-item" data-status-id="<?= $status['id'] ?>">
                    <div class="status-info">
                        <div class="status-label">
                            <strong><?= htmlspecialchars($status['label']) ?></strong>
                            <small class="status-code"><?= htmlspecialchars($status['code']) ?></small>
                        </div>
                        <div class="status-badge-preview">
                            <span class="badge" style="background-color: <?= htmlspecialchars($status['color']) ?>;">
                                <?= htmlspecialchars($status['label']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="status-color-control">
                        <label for="color-<?= $status['id'] ?>">Couleur :</label>
                        <div class="color-input-group">
                            <input type="color"
                                   id="color-<?= $status['id'] ?>"
                                   class="color-picker"
                                   value="<?= htmlspecialchars($status['color']) ?>"
                                   data-status-id="<?= $status['id'] ?>"
                                   data-original-color="<?= htmlspecialchars($status['color']) ?>">
                            <input type="text"
                                   class="color-text"
                                   value="<?= htmlspecialchars($status['color']) ?>"
                                   pattern="^#[0-9A-Fa-f]{6}$"
                                   maxlength="7"
                                   readonly>
                            <button type="button"
                                    class="btn btn-sm btn-primary save-color"
                                    data-status-id="<?= $status['id'] ?>"
                                    style="display: none;">
                                ✓ Appliquer
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="color-suggestions">
    <h3>🎨 Suggestions de couleurs</h3>
    <div class="color-palette">
        <button class="color-preset" style="background: #27ae60;" title="Vert - Pour les statuts positifs (Terminé, Validé)">Vert</button>
        <button class="color-preset" style="background: #3498db;" title="Bleu - Pour les statuts en cours">Bleu</button>
        <button class="color-preset" style="background: #f39c12;" title="Orange - Pour les statuts d'attention">Orange</button>
        <button class="color-preset" style="background: #e74c3c;" title="Rouge - Pour les statuts d'erreur">Rouge</button>
        <button class="color-preset" style="background: #9b59b6;" title="Violet - Pour les statuts spéciaux">Violet</button>
        <button class="color-preset" style="background: #1abc9c;" title="Turquoise - Pour les statuts d'attente">Turquoise</button>
        <button class="color-preset" style="background: #34495e;" title="Gris foncé - Pour les statuts inactifs">Gris foncé</button>
        <button class="color-preset" style="background: #95a5a6;" title="Gris - Pour les statuts neutres">Gris</button>
    </div>
</div>

<style>
.alert {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 6px;
    border-left: 4px solid;
}

.alert-info {
    background: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}

.statuses-container {
    margin-top: 20px;
}

.card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
}

.card-body {
    padding: 25px;
}

.statuses-grid {
    display: grid;
    gap: 20px;
}

.status-item {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 20px;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.status-item:hover {
    background: #fff;
    border-color: #3498db;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.1);
}

.status-item.changed {
    border-color: #f39c12;
    background: #fff8e6;
}

.status-info {
    display: flex;
    align-items: center;
    gap: 20px;
}

.status-label {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.status-label strong {
    font-size: 1.05em;
    color: #2c3e50;
}

.status-code {
    color: #7f8c8d;
    font-family: monospace;
    font-size: 0.9em;
}

.status-badge-preview {
    min-width: 120px;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 4px;
    color: white;
    font-size: 0.9em;
    font-weight: 500;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.status-color-control {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-color-control label {
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.color-input-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.color-picker {
    width: 50px;
    height: 40px;
    border: 2px solid #ddd;
    border-radius: 6px;
    cursor: pointer;
    transition: border-color 0.3s ease;
}

.color-picker:hover {
    border-color: #3498db;
}

.color-text {
    width: 90px;
    padding: 8px 10px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.9em;
    text-align: center;
    background: white;
}

.save-color {
    white-space: nowrap;
}

.color-suggestions {
    margin-top: 30px;
    padding: 20px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
}

.color-suggestions h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #2c3e50;
}

.color-palette {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.color-preset {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.color-preset:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

@media (max-width: 768px) {
    .status-item {
        grid-template-columns: 1fr;
    }

    .status-info {
        flex-direction: column;
        align-items: flex-start;
    }

    .color-input-group {
        flex-wrap: wrap;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du changement de couleur
    document.querySelectorAll('.color-picker').forEach(picker => {
        picker.addEventListener('input', function() {
            const statusId = this.dataset.statusId;
            const statusItem = this.closest('.status-item');
            const colorText = statusItem.querySelector('.color-text');
            const badge = statusItem.querySelector('.badge');
            const saveBtn = statusItem.querySelector('.save-color');
            const originalColor = this.dataset.originalColor;

            // Mettre à jour l'affichage
            const newColor = this.value;
            colorText.value = newColor;
            badge.style.backgroundColor = newColor;

            // Afficher le bouton sauvegarder si la couleur a changé
            if (newColor !== originalColor) {
                saveBtn.style.display = 'inline-block';
                statusItem.classList.add('changed');
            } else {
                saveBtn.style.display = 'none';
                statusItem.classList.remove('changed');
            }
        });
    });

    // Gestion de la sauvegarde
    document.querySelectorAll('.save-color').forEach(btn => {
        btn.addEventListener('click', function() {
            const statusId = this.dataset.statusId;
            const statusItem = this.closest('.status-item');
            const picker = statusItem.querySelector('.color-picker');
            const newColor = picker.value;

            // Désactiver le bouton pendant l'envoi
            this.disabled = true;
            this.textContent = '⏳ Enregistrement...';

            // Envoyer la mise à jour
            fetch('/admin/statuses/update-color', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `status_id=${statusId}&color=${encodeURIComponent(newColor)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour la couleur d'origine
                    picker.dataset.originalColor = newColor;
                    statusItem.classList.remove('changed');
                    this.style.display = 'none';
                    this.textContent = '✓ Appliquer';
                    this.disabled = false;

                    // Afficher un message de succès
                    showNotification('Couleur mise à jour avec succès', 'success');
                } else {
                    throw new Error(data.error || 'Erreur lors de la mise à jour');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                this.textContent = '✓ Appliquer';
                this.disabled = false;
                showNotification('Erreur : ' + error.message, 'error');
            });
        });
    });

    // Notification simple
    function showNotification(message, type) {
        const notif = document.createElement('div');
        notif.className = 'notification notification-' + type;
        notif.textContent = message;
        notif.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
            color: white;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;
        document.body.appendChild(notif);

        setTimeout(() => {
            notif.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notif.remove(), 300);
        }, 3000);
    }
});
</script>
