<?php
/**
 * Processus d'installation - Suivi Diagnostics
 * Gère l'installation complète de la base de données
 */

render_header('Installation en cours');

// Vérifier que nous avons reçu les données du formulaire
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ?step=database');
    exit;
}

// Récupérer les paramètres de connexion
$db_host = $_POST['db_host'] ?? '';
$db_name = $_POST['db_name'] ?? '';
$db_user = $_POST['db_user'] ?? '';
$db_pass = $_POST['db_pass'] ?? '';

// Validation basique
if (empty($db_host) || empty($db_name) || empty($db_user) || empty($db_pass)) {
    ?>
    <div class="alert alert-error">
        <strong>❌ Erreur</strong><br>
        Tous les champs sont requis pour la connexion à la base de données.
    </div>
    <div style="text-align: center; margin-top: 20px;">
        <a href="?step=database" class="btn">Retour</a>
    </div>
    <?php
    render_footer();
    exit;
}

// Stocker en session pour générer le fichier de config plus tard
$_SESSION['db_config'] = [
    'host' => $db_host,
    'database' => $db_name,
    'username' => $db_user,
    'password' => $db_pass
];

?>

<div class="progress">
    <div class="progress-bar" id="progressBar" style="width: 0%;">0%</div>
</div>

<div class="log" id="installLog">
    <div class="log-line">🚀 Démarrage de l'installation...</div>
</div>

<div id="finalMessage" style="display: none;"></div>

<script>
let logContainer = document.getElementById('installLog');
let progressBar = document.getElementById('progressBar');
let currentStep = 0;
let totalSteps = 6;

function addLog(message, type = 'info') {
    let line = document.createElement('div');
    line.className = 'log-line';

    if (type === 'success') {
        line.style.color = '#00ff00';
        line.textContent = '✓ ' + message;
    } else if (type === 'error') {
        line.style.color = '#ff0000';
        line.textContent = '✗ ' + message;
    } else if (type === 'warning') {
        line.style.color = '#ffaa00';
        line.textContent = '⚠ ' + message;
    } else {
        line.style.color = '#00aaff';
        line.textContent = '→ ' + message;
    }

    logContainer.appendChild(line);
    logContainer.scrollTop = logContainer.scrollHeight;
}

function updateProgress(step, message) {
    currentStep = step;
    let percentage = Math.round((step / totalSteps) * 100);
    progressBar.style.width = percentage + '%';
    progressBar.textContent = percentage + '%';
    addLog(message);
}

function showFinalMessage(success, message) {
    let finalMsg = document.getElementById('finalMessage');
    finalMsg.style.display = 'block';
    finalMsg.className = success ? 'alert alert-success' : 'alert alert-error';
    finalMsg.innerHTML = message;
}

// Démarrer l'installation
setTimeout(() => {
    runInstallation();
}, 1000);

async function runInstallation() {
    try {
        // Étape 1 : Test de connexion
        updateProgress(1, 'Test de connexion MySQL...');
        let result = await fetch('install-steps.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'step=test_connection&db_host=<?= urlencode($db_host) ?>&db_name=<?= urlencode($db_name) ?>&db_user=<?= urlencode($db_user) ?>&db_pass=<?= urlencode($db_pass) ?>'
        }).then(r => r.json());

        if (!result.success) {
            addLog('Échec de la connexion : ' + result.error, 'error');
            showFinalMessage(false, '<strong>❌ Installation échouée</strong><br>' + result.error + '<br><br><a href="?step=database" class="btn">Réessayer</a>');
            return;
        }
        addLog('Connexion MySQL établie', 'success');

        // Étape 2 : Suppression des tables existantes
        updateProgress(2, 'Suppression des tables existantes...');
        result = await fetch('install-steps.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'step=drop_tables&db_host=<?= urlencode($db_host) ?>&db_name=<?= urlencode($db_name) ?>&db_user=<?= urlencode($db_user) ?>&db_pass=<?= urlencode($db_pass) ?>'
        }).then(r => r.json());

        if (!result.success) {
            addLog('Erreur lors de la suppression : ' + result.error, 'error');
            showFinalMessage(false, '<strong>❌ Installation échouée</strong><br>' + result.error);
            return;
        }
        addLog('Tables supprimées : ' + result.count + ' table(s)', 'success');

        // Étape 3 : Import du schéma
        updateProgress(3, 'Import du schéma (tables)...');
        result = await fetch('install-steps.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'step=import_schema&db_host=<?= urlencode($db_host) ?>&db_name=<?= urlencode($db_name) ?>&db_user=<?= urlencode($db_user) ?>&db_pass=<?= urlencode($db_pass) ?>'
        }).then(r => r.json());

        if (!result.success) {
            addLog('Erreur lors de l\'import du schéma : ' + result.error, 'error');
            showFinalMessage(false, '<strong>❌ Installation échouée</strong><br>' + result.error);
            return;
        }
        addLog('Schéma importé : ' + result.tables + ' table(s) créée(s)', 'success');

        // Étape 4 : Import des données
        updateProgress(4, 'Import des données de démonstration...');
        result = await fetch('install-steps.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'step=import_seeds&db_host=<?= urlencode($db_host) ?>&db_name=<?= urlencode($db_name) ?>&db_user=<?= urlencode($db_user) ?>&db_pass=<?= urlencode($db_pass) ?>'
        }).then(r => r.json());

        if (!result.success) {
            addLog('Erreur lors de l\'import des données : ' + result.error, 'error');
            showFinalMessage(false, '<strong>❌ Installation échouée</strong><br>' + result.error);
            return;
        }
        addLog('Données importées : ' + result.users + ' utilisateur(s)', 'success');

        // Étape 5 : Génération de la configuration
        updateProgress(5, 'Génération du fichier de configuration...');
        result = await fetch('install-steps.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'step=generate_config&db_host=<?= urlencode($db_host) ?>&db_name=<?= urlencode($db_name) ?>&db_user=<?= urlencode($db_user) ?>&db_pass=<?= urlencode($db_pass) ?>'
        }).then(r => r.json());

        if (!result.success) {
            addLog('Avertissement : ' + result.error, 'warning');
        } else {
            addLog('Configuration générée : config/database.php', 'success');
        }

        // Étape 6 : Finalisation
        updateProgress(6, 'Finalisation de l\'installation...');
        await new Promise(resolve => setTimeout(resolve, 500));
        addLog('Installation terminée !', 'success');

        // Afficher le message de succès
        showFinalMessage(true, `
            <strong>✅ Installation réussie !</strong><br><br>

            <div style="background: white; padding: 20px; border-radius: 4px; color: #2c3e50; margin-top: 15px;">
                <h3 style="margin-top: 0; color: #2c3e50;">🔑 Comptes de démonstration</h3>
                <p style="margin-bottom: 15px;"><strong>Mot de passe pour TOUS :</strong> <code style="background: #f8f9fa; padding: 4px 8px; border-radius: 3px; color: #dc3545; font-weight: bold;">password</code></p>

                <div style="margin-bottom: 10px;">
                    <strong>👤 Administrateur :</strong><br>
                    Identifiant : <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px;">admin</code>
                </div>

                <div style="margin-bottom: 10px;">
                    <strong>👤 Secrétariat :</strong><br>
                    Identifiant : <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px;">secretariat</code>
                </div>

                <div style="margin-bottom: 10px;">
                    <strong>👤 Technicien :</strong><br>
                    Identifiant : <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px;">tech1</code>
                </div>

                <div style="margin-bottom: 15px;">
                    <strong>👤 Client :</strong><br>
                    Identifiant : <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px;">client.pch</code>
                </div>

                <div style="background: #fff3cd; padding: 10px; border-radius: 3px; border-left: 4px solid #856404; margin-top: 15px;">
                    <strong>⚠️ IMPORTANT :</strong> Changez ces mots de passe après la première connexion !
                </div>
            </div>

            <div style="text-align: center; margin-top: 25px;">
                <a href="../" class="btn btn-success" style="display: inline-block;">Accéder à l'application</a>
            </div>
        `);

    } catch (error) {
        addLog('Erreur technique : ' + error.message, 'error');
        showFinalMessage(false, '<strong>❌ Erreur technique</strong><br>' + error.message);
    }
}
</script>

<?php
render_footer();
