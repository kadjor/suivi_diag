<h1>🚀 Déploiement et Mise à jour</h1>

<div class="deploy-container">
    <!-- Statut -->
    <div class="card">
        <h2>📊 Statut <?= $isGitRepo ? 'Git' : 'du déploiement' ?></h2>

        <?php if (!$isGitRepo): ?>
        <div class="alert alert-info">
            <strong>ℹ️ Mode sans Git détecté</strong>
            <p>Ce répertoire n'est pas un dépôt Git. Utilisez le téléchargement direct depuis GitHub.</p>
        </div>
        <?php endif; ?>

        <div class="info-grid">
            <div class="info-item">
                <strong>Type:</strong>
                <span class="badge <?= $isGitRepo ? 'badge-success' : 'badge-warning' ?>">
                    <?= $isGitRepo ? '✅ Git Repository' : '⚠️ Non-Git' ?>
                </span>
            </div>

            <?php if ($isGitRepo): ?>
            <div class="info-item">
                <strong>Branche actuelle:</strong>
                <span class="badge badge-info"><?= htmlspecialchars($currentBranch) ?></span>
            </div>

            <div class="info-item">
                <strong>État:</strong>
                <?php if ($hasChanges): ?>
                    <span class="badge badge-warning">⚠️ Changements locaux</span>
                <?php else: ?>
                    <span class="badge badge-success">✅ Propre</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="info-item">
                <strong>Répertoire:</strong>
                <span><?= htmlspecialchars($appDir) ?></span>
            </div>
        </div>

        <?php if ($isGitRepo && !empty($gitStatus['files'])): ?>
        <div class="git-changes">
            <h3>Fichiers modifiés:</h3>
            <pre class="code-block"><?php
                foreach ($gitStatus['files'] as $file) {
                    echo htmlspecialchars($file) . "\n";
                }
            ?></pre>
        </div>
        <?php endif; ?>
    </div>

    <!-- Actions de déploiement -->
    <div class="card">
        <h2>⚙️ Actions</h2>

        <?php if ($isGitRepo): ?>
        <!-- Actions Git -->
        <div class="action-buttons">
            <button id="btnDiff" class="btn btn-info">
                📋 Voir les différences
            </button>

            <button id="btnPull" class="btn btn-primary">
                ⬇️ Mettre à jour (Git Pull)
            </button>

            <?php if ($hasChanges): ?>
            <button id="btnReset" class="btn btn-warning">
                🔄 Réinitialiser les changements
            </button>
            <?php endif; ?>

            <button id="btnPermissions" class="btn btn-success">
                🔐 Restaurer les permissions
            </button>

            <button id="btnBackups" class="btn btn-secondary">
                💾 Voir les backups
            </button>
        </div>
        <?php else: ?>
        <!-- Actions non-Git (téléchargement direct) -->
        <div class="github-form">
            <h3>📦 Téléchargement depuis GitHub</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="github_user">Utilisateur GitHub:</label>
                    <input type="text" id="github_user" value="kadjor" class="form-control">
                </div>
                <div class="form-group">
                    <label for="github_repo">Dépôt:</label>
                    <input type="text" id="github_repo" value="suivi_diag" class="form-control">
                </div>
                <div class="form-group">
                    <label for="github_branch">Branche:</label>
                    <input type="text" id="github_branch" value="main" class="form-control">
                </div>
            </div>
            <div class="action-buttons">
                <button id="btnDownloadGithub" class="btn btn-primary">
                    ⬇️ Télécharger et déployer
                </button>

                <button id="btnPermissions" class="btn btn-success">
                    🔐 Restaurer les permissions
                </button>

                <button id="btnBackups" class="btn btn-secondary">
                    💾 Voir les backups
                </button>
            </div>
        </div>
        <?php endif; ?>

        <div id="actionResult" class="action-result"></div>
    </div>

    <!-- Migrations de base de données -->
    <div class="card">
        <h2>🗄️ Migrations de base de données</h2>
        <p class="info-text">Gérez les migrations SQL pour créer et mettre à jour les tables de la base de données.</p>

        <div class="action-buttons">
            <button id="btnLoadMigrations" class="btn btn-info">
                📋 Charger les migrations
            </button>
            <button id="btnRunMigrations" class="btn btn-primary" style="display:none;">
                ▶️ Exécuter les migrations en attente
            </button>
        </div>

        <div id="migrationsResult" class="migrations-result"></div>
        <div id="migrationsList" class="migrations-list"></div>
    </div>

    <!-- Derniers commits -->
    <div class="card">
        <h2>📝 Derniers commits</h2>

        <?php if (!empty($lastCommits)): ?>
        <div class="commits-list">
            <?php foreach ($lastCommits as $commit): ?>
            <div class="commit-item">
                <span class="commit-hash"><?= htmlspecialchars($commit['hash']) ?></span>
                <span class="commit-message"><?= htmlspecialchars($commit['message']) ?></span>
                <span class="commit-meta">
                    par <strong><?= htmlspecialchars($commit['author']) ?></strong>
                    • <?= htmlspecialchars($commit['date']) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="no-data">Aucun commit trouvé</p>
        <?php endif; ?>
    </div>

    <!-- Logs de déploiement -->
    <div class="card">
        <h2>📜 Logs de déploiement</h2>

        <?php if (!empty($deployLogs)): ?>
        <pre class="log-viewer"><?php
            foreach ($deployLogs as $log) {
                echo htmlspecialchars($log) . "\n";
            }
        ?></pre>
        <button id="btnRefreshLogs" class="btn btn-small">🔄 Actualiser les logs</button>
        <?php else: ?>
        <p class="no-data">Aucun log de déploiement</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour afficher les résultats -->
<div id="resultModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Résultat</h2>
        <div id="modalBody"></div>
    </div>
</div>

<script>
// Modal
const modal = document.getElementById('resultModal');
const modalTitle = document.getElementById('modalTitle');
const modalBody = document.getElementById('modalBody');
const closeBtn = document.querySelector('.close');

closeBtn.onclick = function() {
    modal.style.display = 'none';
}

window.onclick = function(event) {
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

function showModal(title, content, isHtml = false) {
    modalTitle.textContent = title;
    if (isHtml) {
        modalBody.innerHTML = content;
    } else {
        modalBody.textContent = content;
    }
    modal.style.display = 'block';
}

// Voir les différences (mode Git uniquement)
const btnDiff = document.getElementById('btnDiff');
if (btnDiff) {
    btnDiff.addEventListener('click', async function() {
        this.disabled = true;
        this.textContent = '⏳ Chargement...';

        try {
            const response = await fetch('/deploy/diff');
            const data = await response.json();

            if (data.diff) {
                showModal('📋 Différences avec la version distante', data.diff);
            } else {
                showModal('📋 Différences', 'Aucune différence trouvée');
            }
        } catch (error) {
            showModal('❌ Erreur', error.message);
        } finally {
            this.disabled = false;
            this.textContent = '📋 Voir les différences';
        }
    });
}

// Git Pull (mode Git uniquement)
const btnPull = document.getElementById('btnPull');
if (btnPull) {
    btnPull.addEventListener('click', async function() {
        const resultDiv = document.getElementById('actionResult');

        // Confirmation inline
        resultDiv.innerHTML = `
            <div class="confirm-box">
                <p><strong>⚠️ Confirmer la mise à jour</strong></p>
                <p>Un backup sera automatiquement créé avant la mise à jour.</p>
                <div style="margin-top: 15px;">
                    <button class="btn btn-primary" id="confirmPull">✓ Confirmer</button>
                    <button class="btn btn-secondary" id="cancelPull">✗ Annuler</button>
                </div>
            </div>
        `;

        document.getElementById('cancelPull').onclick = () => {
            resultDiv.innerHTML = '';
        };

        document.getElementById('confirmPull').onclick = async () => {
            this.disabled = true;
            this.textContent = '⏳ Mise à jour en cours...';
            resultDiv.innerHTML = '<div class="loading">⏳ Mise à jour en cours, veuillez patienter...</div>';

        try {
            const response = await fetch('/deploy/pull', { method: 'POST' });
            const data = await response.json();

            let resultHtml = '';

            if (data.success) {
                resultHtml = '<div class="result-success"><h3>✅ Mise à jour réussie !</h3>';

                if (data.messages && data.messages.length > 0) {
                    resultHtml += '<ul>';
                    data.messages.forEach(msg => {
                        resultHtml += '<li>' + escapeHtml(msg) + '</li>';
                    });
                    resultHtml += '</ul>';
                }

                resultHtml += '<p><strong>Veuillez recharger la page pour voir les changements.</strong></p>';
                resultHtml += '</div>';

                // Recharger après 3 secondes
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            } else {
                resultHtml = '<div class="result-error"><h3>❌ Erreur lors de la mise à jour</h3>';

                if (data.errors && data.errors.length > 0) {
                    resultHtml += '<ul>';
                    data.errors.forEach(err => {
                        resultHtml += '<li>' + escapeHtml(err) + '</li>';
                    });
                    resultHtml += '</ul>';
                }

                resultHtml += '</div>';
            }

            resultDiv.innerHTML = resultHtml;

        } catch (error) {
            resultDiv.innerHTML = '<div class="result-error">❌ Erreur: ' + escapeHtml(error.message) + '</div>';
        } finally {
            this.disabled = false;
            this.textContent = '⬇️ Mettre à jour (Git Pull)';
        }
        };
    });
}

// Reset
const btnReset = document.getElementById('btnReset');
if (btnReset) {
    btnReset.addEventListener('click', async function() {
        const resultDiv = document.getElementById('actionResult');

        // Confirmation inline
        resultDiv.innerHTML = `
            <div class="confirm-box warning">
                <p><strong>⚠️ ATTENTION: Action destructive</strong></p>
                <p>Cette action va supprimer TOUS vos changements locaux.</p>
                <div style="margin-top: 15px;">
                    <button class="btn btn-warning" id="confirmReset">✓ Confirmer la réinitialisation</button>
                    <button class="btn btn-secondary" id="cancelReset">✗ Annuler</button>
                </div>
            </div>
        `;

        document.getElementById('cancelReset').onclick = () => {
            resultDiv.innerHTML = '';
        };

        document.getElementById('confirmReset').onclick = async () => {
            this.disabled = true;
            this.textContent = '⏳ Réinitialisation...';
            resultDiv.innerHTML = '<div class="loading">⏳ Réinitialisation en cours...</div>';

            try {
                const response = await fetch('/deploy/reset', { method: 'POST' });
                const data = await response.json();

                if (data.success) {
                    resultDiv.innerHTML = '<div class="result-success">✅ ' + escapeHtml(data.message) + '<br>Rechargement dans 2 secondes...</div>';
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    resultDiv.innerHTML = '<div class="result-error">❌ ' + escapeHtml(data.error) + '</div>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<div class="result-error">❌ Erreur: ' + escapeHtml(error.message) + '</div>';
            } finally {
                this.disabled = false;
                this.textContent = '🔄 Réinitialiser les changements';
            }
        };
    });
}

// Voir les backups
document.getElementById('btnBackups').addEventListener('click', async function() {
    this.disabled = true;
    this.textContent = '⏳ Chargement...';

    try {
        const response = await fetch('/deploy/backups');
        const data = await response.json();

        let content = '<div class="backups-list">';

        if (data.backups && data.backups.length > 0) {
            content += '<table class="data-table">';
            content += '<thead><tr><th>Fichier</th><th>Taille</th><th>Date</th></tr></thead>';
            content += '<tbody>';
            data.backups.forEach(backup => {
                content += '<tr>';
                content += '<td>' + escapeHtml(backup.name) + '</td>';
                content += '<td>' + escapeHtml(backup.size) + '</td>';
                content += '<td>' + escapeHtml(backup.date) + '</td>';
                content += '</tr>';
            });
            content += '</tbody></table>';
        } else {
            content += '<p>Aucun backup trouvé</p>';
        }

        content += '</div>';

        showModal('💾 Backups disponibles', content, true);
    } catch (error) {
        showModal('❌ Erreur', error.message);
    } finally {
        this.disabled = false;
        this.textContent = '💾 Voir les backups';
    }
});

// Actualiser les logs
document.getElementById('btnRefreshLogs')?.addEventListener('click', function() {
    window.location.reload();
});

// Télécharger depuis GitHub (mode non-Git)
document.getElementById('btnDownloadGithub')?.addEventListener('click', async function() {
    const githubUser = document.getElementById('github_user').value;
    const githubRepo = document.getElementById('github_repo').value;
    const githubBranch = document.getElementById('github_branch').value;
    const resultDiv = document.getElementById('actionResult');

    if (!githubUser || !githubRepo || !githubBranch) {
        resultDiv.innerHTML = '<div class="result-error">⚠️ Veuillez remplir tous les champs</div>';
        return;
    }

    // Confirmation inline
    resultDiv.innerHTML = `
        <div class="confirm-box">
            <p><strong>⚠️ Confirmer le téléchargement depuis GitHub</strong></p>
            <p>Utilisateur: <strong>${escapeHtml(githubUser)}</strong><br>
            Dépôt: <strong>${escapeHtml(githubRepo)}</strong><br>
            Branche: <strong>${escapeHtml(githubBranch)}</strong></p>
            <p>Un backup sera automatiquement créé.</p>
            <div style="margin-top: 15px;">
                <button class="btn btn-primary" id="confirmDownload">✓ Confirmer</button>
                <button class="btn btn-secondary" id="cancelDownload">✗ Annuler</button>
            </div>
        </div>
    `;

    document.getElementById('cancelDownload').onclick = () => {
        resultDiv.innerHTML = '';
    };

    document.getElementById('confirmDownload').onclick = async () => {
        this.disabled = true;
        this.textContent = '⏳ Téléchargement en cours...';
        resultDiv.innerHTML = '<div class="loading">⏳ Téléchargement et déploiement en cours, veuillez patienter...</div>';

    try {
        const formData = new FormData();
        formData.append('github_user', githubUser);
        formData.append('github_repo', githubRepo);
        formData.append('github_branch', githubBranch);

        const response = await fetch('/deploy/download-github', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        let resultHtml = '';

        if (data.success) {
            resultHtml = '<div class="result-success"><h3>✅ Téléchargement et déploiement réussis !</h3>';

            if (data.messages && data.messages.length > 0) {
                resultHtml += '<ul>';
                data.messages.forEach(msg => {
                    resultHtml += '<li>' + escapeHtml(msg) + '</li>';
                });
                resultHtml += '</ul>';
            }

            resultHtml += '<p><strong>Veuillez recharger la page pour voir les changements.</strong></p>';
            resultHtml += '</div>';

            // Recharger après 3 secondes
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        } else {
            resultHtml = '<div class="result-error"><h3>❌ Erreur lors du téléchargement</h3>';

            if (data.errors && data.errors.length > 0) {
                resultHtml += '<ul>';
                data.errors.forEach(err => {
                    resultHtml += '<li>' + escapeHtml(err) + '</li>';
                });
                resultHtml += '</ul>';
            }

            resultHtml += '</div>';
        }

        resultDiv.innerHTML = resultHtml;

    } catch (error) {
        resultDiv.innerHTML = '<div class="result-error">❌ Erreur: ' + escapeHtml(error.message) + '</div>';
    } finally {
        this.disabled = false;
        this.textContent = '⬇️ Télécharger et déployer';
    }
    };
});

// Restaurer les permissions
document.getElementById('btnPermissions')?.addEventListener('click', async function() {
    const resultDiv = document.getElementById('actionResult');

    // Confirmation inline
    resultDiv.innerHTML = `
        <div class="confirm-box">
            <p><strong>🔐 Confirmer la restauration des permissions</strong></p>
            <p>Cette action appliquera les permissions correctes pour le fonctionnement de l'application.</p>
            <div style="margin-top: 15px;">
                <button class="btn btn-success" id="confirmPerms">✓ Confirmer</button>
                <button class="btn btn-secondary" id="cancelPerms">✗ Annuler</button>
            </div>
        </div>
    `;

    document.getElementById('cancelPerms').onclick = () => {
        resultDiv.innerHTML = '';
    };

    document.getElementById('confirmPerms').onclick = async () => {
        this.disabled = true;
        this.textContent = '⏳ Application en cours...';
        resultDiv.innerHTML = '<div class="loading">⏳ Application des permissions...</div>';

        try {
            const response = await fetch('/deploy/apply-permissions', { method: 'POST' });
            const data = await response.json();

            if (data.success) {
                resultDiv.innerHTML = '<div class="result-success">✅ ' + escapeHtml(data.message) + '</div>';
            } else {
                resultDiv.innerHTML = '<div class="result-error">❌ ' + escapeHtml(data.error) + '</div>';
            }
        } catch (error) {
            resultDiv.innerHTML = '<div class="result-error">❌ Erreur: ' + escapeHtml(error.message) + '</div>';
        } finally {
            this.disabled = false;
            this.textContent = '🔐 Restaurer les permissions';
        }
    };
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Charger les migrations
document.getElementById('btnLoadMigrations')?.addEventListener('click', async function() {
    this.disabled = true;
    this.textContent = '⏳ Chargement...';

    const migrationsListDiv = document.getElementById('migrationsList');
    const btnRunMigrations = document.getElementById('btnRunMigrations');

    try {
        const response = await fetch('/deploy/migrations');
        const data = await response.json();

        let html = '';

        if (data.migrations && data.migrations.length > 0) {
            html += '<table class="data-table">';
            html += '<thead><tr><th>Fichier</th><th>Taille</th><th>Statut</th></tr></thead>';
            html += '<tbody>';

            let hasPending = false;
            data.migrations.forEach(migration => {
                const status = migration.executed ?
                    '<span class="badge badge-success">✅ Exécutée</span>' :
                    '<span class="badge badge-warning">⏳ En attente</span>';

                if (!migration.executed) {
                    hasPending = true;
                }

                html += '<tr>';
                html += '<td><code>' + escapeHtml(migration.name) + '</code></td>';
                html += '<td>' + escapeHtml(migration.size) + '</td>';
                html += '<td>' + status + '</td>';
                html += '</tr>';
            });
            html += '</tbody></table>';

            // Afficher le bouton d'exécution si des migrations sont en attente
            if (hasPending) {
                btnRunMigrations.style.display = 'inline-block';
            } else {
                btnRunMigrations.style.display = 'none';
            }
        } else {
            html = '<p class="no-data">Aucune migration trouvée dans database/migrations/</p>';
            btnRunMigrations.style.display = 'none';
        }

        migrationsListDiv.innerHTML = html;
    } catch (error) {
        migrationsListDiv.innerHTML = '<div class="result-error">❌ Erreur: ' + escapeHtml(error.message) + '</div>';
    } finally {
        this.disabled = false;
        this.textContent = '📋 Charger les migrations';
    }
});

// Exécuter les migrations
document.getElementById('btnRunMigrations')?.addEventListener('click', async function() {
    const resultDiv = document.getElementById('migrationsResult');

    // Confirmation inline
    resultDiv.innerHTML = `
        <div class="confirm-box">
            <p><strong>⚠️ Confirmer l'exécution des migrations</strong></p>
            <p>Cela va créer/modifier les tables de la base de données.</p>
            <div style="margin-top: 15px;">
                <button class="btn btn-primary" id="confirmMigrations">✓ Confirmer</button>
                <button class="btn btn-secondary" id="cancelMigrations">✗ Annuler</button>
            </div>
        </div>
    `;

    document.getElementById('cancelMigrations').onclick = () => {
        resultDiv.innerHTML = '';
    };

    document.getElementById('confirmMigrations').onclick = async () => {
        this.disabled = true;
        this.textContent = '⏳ Exécution en cours...';
        resultDiv.innerHTML = '<div class="loading">⏳ Exécution des migrations en cours...</div>';

    try {
        const response = await fetch('/deploy/run-migrations', { method: 'POST' });
        const data = await response.json();

        let resultHtml = '';

        if (data.success) {
            resultHtml = '<div class="result-success"><h3>✅ Migrations exécutées avec succès !</h3>';

            if (data.executed && data.executed.length > 0) {
                resultHtml += '<p><strong>Migrations exécutées:</strong></p>';
                resultHtml += '<ul>';
                data.executed.forEach(migration => {
                    resultHtml += '<li>✅ ' + escapeHtml(migration) + '</li>';
                });
                resultHtml += '</ul>';
            }

            if (data.skipped && data.skipped.length > 0) {
                resultHtml += '<p><strong>Migrations ignorées (déjà exécutées):</strong></p>';
                resultHtml += '<ul>';
                data.skipped.forEach(migration => {
                    resultHtml += '<li>⊘ ' + escapeHtml(migration) + '</li>';
                });
                resultHtml += '</ul>';
            }

            resultHtml += '</div>';

            // Recharger la liste des migrations
            setTimeout(() => {
                document.getElementById('btnLoadMigrations').click();
            }, 1000);
        } else {
            resultHtml = '<div class="result-error"><h3>❌ Erreur lors de l\'exécution</h3>';

            if (data.errors && data.errors.length > 0) {
                resultHtml += '<ul>';
                data.errors.forEach(err => {
                    resultHtml += '<li>' + escapeHtml(err) + '</li>';
                });
                resultHtml += '</ul>';
            }

            if (data.executed && data.executed.length > 0) {
                resultHtml += '<p><strong>Migrations exécutées avant l\'erreur:</strong></p>';
                resultHtml += '<ul>';
                data.executed.forEach(migration => {
                    resultHtml += '<li>✅ ' + escapeHtml(migration) + '</li>';
                });
                resultHtml += '</ul>';
            }

            resultHtml += '</div>';
        }

        resultDiv.innerHTML = resultHtml;

    } catch (error) {
        resultDiv.innerHTML = '<div class="result-error">❌ Erreur: ' + escapeHtml(error.message) + '</div>';
    } finally {
        this.disabled = false;
        this.textContent = '▶️ Exécuter les migrations en attente';
    }
    };
});
</script>

<style>
.deploy-container {
    max-width: 1200px;
    margin: 0 auto;
}

.card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.card h2 {
    margin-top: 0;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
    color: #2c3e50;
}

.card h3 {
    margin-top: 15px;
    margin-bottom: 10px;
    color: #34495e;
    font-size: 1.1em;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.info-item {
    padding: 12px;
    background: #f8f9fa;
    border-radius: 4px;
    border-left: 3px solid #3498db;
}

.info-item strong {
    display: block;
    margin-bottom: 5px;
    color: #666;
    font-size: 0.9em;
}

.info-item span {
    display: block;
    font-size: 1.05em;
    color: #2c3e50;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.9em;
    font-weight: 500;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
}

.git-changes {
    margin-top: 15px;
}

.code-block {
    background: #2c3e50;
    color: #ecf0f1;
    padding: 15px;
    border-radius: 4px;
    overflow-x: auto;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    max-height: 300px;
    overflow-y: auto;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2980b9;
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-info:hover:not(:disabled) {
    background: #138496;
}

.btn-warning {
    background: #f39c12;
    color: white;
}

.btn-warning:hover:not(:disabled) {
    background: #e67e22;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover:not(:disabled) {
    background: #7f8c8d;
}

.btn-small {
    padding: 8px 16px;
    font-size: 13px;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.action-result {
    margin-top: 20px;
}

.loading {
    padding: 20px;
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    border-radius: 4px;
    color: #0c5460;
    text-align: center;
}

.result-success {
    padding: 20px;
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 4px;
    color: #155724;
}

.result-success h3 {
    margin-top: 0;
    color: #155724;
}

.result-error {
    padding: 20px;
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    color: #721c24;
}

.result-error h3 {
    margin-top: 0;
    color: #721c24;
}

.result-success ul,
.result-error ul {
    margin: 10px 0;
    padding-left: 20px;
}

.commits-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.commit-item {
    padding: 12px;
    background: #f8f9fa;
    border-left: 3px solid #3498db;
    border-radius: 4px;
    display: grid;
    gap: 5px;
}

.commit-hash {
    font-family: 'Courier New', monospace;
    font-weight: bold;
    color: #e74c3c;
    font-size: 0.9em;
}

.commit-message {
    color: #2c3e50;
    font-weight: 500;
}

.commit-meta {
    color: #7f8c8d;
    font-size: 0.85em;
}

.log-viewer {
    background: #2c3e50;
    color: #ecf0f1;
    padding: 15px;
    border-radius: 4px;
    overflow-x: auto;
    font-family: 'Courier New', monospace;
    font-size: 0.85em;
    max-height: 400px;
    overflow-y: auto;
    line-height: 1.5;
}

.no-data {
    text-align: center;
    padding: 30px;
    color: #999;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 30px;
    border: 1px solid #888;
    border-radius: 8px;
    width: 80%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 20px;
}

.close:hover,
.close:focus {
    color: #000;
}

.backups-list table {
    width: 100%;
    margin-top: 15px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.data-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.data-table tbody tr:hover {
    background: #f8f9fa;
}

/* Formulaire GitHub */
.github-form {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 20px;
}

.github-form h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #2c3e50;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 5px;
    font-weight: 500;
    color: #2c3e50;
    font-size: 0.9em;
}

.form-control {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

/* Alerte info */
.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 15px;
}

.alert-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
}

.alert strong {
    display: block;
    margin-bottom: 5px;
}

.alert p {
    margin: 0;
}

.btn-success {
    background: #27ae60;
    color: white;
}

.btn-success:hover:not(:disabled) {
    background: #229954;
}

/* Migrations */
.info-text {
    color: #666;
    margin-bottom: 15px;
    font-size: 0.95em;
}

.migrations-result {
    margin-top: 15px;
}

.migrations-list {
    margin-top: 15px;
}

.migrations-list code {
    background: #f4f4f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

/* Boîtes de confirmation */
.confirm-box {
    padding: 20px;
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 8px;
    margin: 20px 0;
}

.confirm-box.warning {
    background: #f8d7da;
    border-color: #f5c6cb;
}

.confirm-box p {
    margin: 10px 0;
}

.confirm-box strong {
    color: #856404;
}

.confirm-box.warning strong {
    color: #721c24;
}

.confirm-box .btn {
    margin-right: 10px;
}
</style>
