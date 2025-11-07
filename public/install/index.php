<?php
/**
 * Installateur Web - Suivi Diagnostics
 *
 * Accès : https://gestion.d-evidences.fr/install/
 *
 * Ce script :
 * 1. Vérifie les prérequis PHP
 * 2. Teste la connexion MySQL
 * 3. Vide complètement la base de données
 * 4. Importe le schéma (tables)
 * 5. Importe les données de démonstration
 * 6. Configure les permissions
 * 7. Génère config/database.php
 */

// Démarrer la session
session_start();

// Configuration
define('APP_ROOT', dirname(dirname(__DIR__)));
define('VERSION', '1.0.0');

// Activer l'affichage des erreurs pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fonction pour afficher le header HTML
function render_header($title = 'Installation') {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?> - Suivi Diagnostics</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 20px;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                border-radius: 10px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                overflow: hidden;
            }
            .header {
                background: #2c3e50;
                color: white;
                padding: 30px;
                text-align: center;
            }
            .header h1 {
                font-size: 28px;
                margin-bottom: 10px;
            }
            .header p {
                opacity: 0.9;
                font-size: 14px;
            }
            .content {
                padding: 40px;
            }
            .step {
                background: #f8f9fa;
                border-left: 4px solid #667eea;
                padding: 20px;
                margin-bottom: 20px;
                border-radius: 4px;
            }
            .step h2 {
                color: #2c3e50;
                margin-bottom: 15px;
                font-size: 20px;
            }
            .check-item {
                padding: 10px 0;
                border-bottom: 1px solid #e0e0e0;
            }
            .check-item:last-child {
                border-bottom: none;
            }
            .status {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: bold;
                margin-left: 10px;
            }
            .status.ok {
                background: #d4edda;
                color: #155724;
            }
            .status.error {
                background: #f8d7da;
                color: #721c24;
            }
            .status.warning {
                background: #fff3cd;
                color: #856404;
            }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #2c3e50;
            }
            .form-group input, .form-group select {
                width: 100%;
                padding: 12px;
                border: 2px solid #e0e0e0;
                border-radius: 4px;
                font-size: 14px;
            }
            .form-group input:focus {
                outline: none;
                border-color: #667eea;
            }
            .form-group small {
                display: block;
                margin-top: 5px;
                color: #6c757d;
                font-size: 12px;
            }
            .btn {
                display: inline-block;
                padding: 12px 30px;
                background: #667eea;
                color: white;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                transition: background 0.3s;
            }
            .btn:hover {
                background: #5568d3;
            }
            .btn-danger {
                background: #dc3545;
            }
            .btn-danger:hover {
                background: #c82333;
            }
            .btn-success {
                background: #28a745;
            }
            .btn-success:hover {
                background: #218838;
            }
            .alert {
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
            }
            .alert-success {
                background: #d4edda;
                color: #155724;
                border: 1px solid #c3e6cb;
            }
            .alert-error {
                background: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
            }
            .alert-warning {
                background: #fff3cd;
                color: #856404;
                border: 1px solid #ffeaa7;
            }
            .progress {
                background: #e0e0e0;
                border-radius: 4px;
                height: 30px;
                overflow: hidden;
                margin-bottom: 20px;
            }
            .progress-bar {
                background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
                height: 100%;
                line-height: 30px;
                color: white;
                text-align: center;
                font-weight: bold;
                transition: width 0.3s;
            }
            .log {
                background: #1e1e1e;
                color: #00ff00;
                padding: 15px;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                font-size: 12px;
                max-height: 300px;
                overflow-y: auto;
                margin-top: 20px;
            }
            .log-line {
                margin-bottom: 5px;
            }
            .footer {
                background: #f8f9fa;
                padding: 20px;
                text-align: center;
                color: #6c757d;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🚀 Installation - Suivi Diagnostics</h1>
                <p>Version <?= VERSION ?> - Installation automatique</p>
            </div>
            <div class="content">
    <?php
}

// Fonction pour afficher le footer HTML
function render_footer() {
    ?>
            </div>
            <div class="footer">
                <p>Suivi Diagnostics &copy; <?= date('Y') ?> - Tous droits réservés</p>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// Fonction pour vérifier les prérequis
function check_requirements() {
    $checks = [];

    // Version PHP
    $php_version = PHP_VERSION;
    $checks[] = [
        'name' => 'PHP Version',
        'value' => $php_version,
        'status' => version_compare($php_version, '7.4.0', '>=') ? 'ok' : 'error',
        'message' => version_compare($php_version, '7.4.0', '>=') ? 'PHP 7.4+ requis' : 'PHP 7.4+ requis'
    ];

    // Extensions PHP requises
    $required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'curl'];

    foreach ($required_extensions as $ext) {
        $checks[] = [
            'name' => "Extension PHP : $ext",
            'value' => extension_loaded($ext) ? 'Installée' : 'Manquante',
            'status' => extension_loaded($ext) ? 'ok' : 'error',
            'message' => extension_loaded($ext) ? '' : "Extension $ext requise"
        ];
    }

    // Permissions en écriture
    $writable_dirs = [
        'logs' => APP_ROOT . '/logs',
        'cache' => APP_ROOT . '/cache',
        'sessions' => APP_ROOT . '/sessions',
        'uploads' => APP_ROOT . '/public/uploads'
    ];

    foreach ($writable_dirs as $name => $dir) {
        $writable = is_dir($dir) && is_writable($dir);
        $checks[] = [
            'name' => "Écriture : $name/",
            'value' => $writable ? 'OK' : 'Non accessible',
            'status' => $writable ? 'ok' : 'warning',
            'message' => $writable ? '' : "Créez le dossier ou ajustez les permissions (chmod 770)"
        ];
    }

    // Fichiers requis
    $required_files = [
        'schema' => APP_ROOT . '/database/schema.sql',
        'seeds' => APP_ROOT . '/database/seeds/001_initial_data.sql'
    ];

    foreach ($required_files as $name => $file) {
        $exists = file_exists($file);
        $checks[] = [
            'name' => "Fichier : $name",
            'value' => $exists ? 'Présent' : 'Manquant',
            'status' => $exists ? 'ok' : 'error',
            'message' => $exists ? '' : "Fichier requis : $file"
        ];
    }

    return $checks;
}

// Fonction pour tester la connexion MySQL
function test_mysql_connection($host, $user, $pass, $dbname) {
    try {
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Vérifier si la base existe
        $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
        $db_exists = $stmt->rowCount() > 0;

        // Si la base n'existe pas, essayer de la créer
        if (!$db_exists) {
            $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }

        return ['success' => true, 'db_exists' => $db_exists, 'pdo' => $pdo];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Récupérer l'étape actuelle
$step = $_GET['step'] ?? 'welcome';

// Traiter l'étape
switch ($step) {
    case 'welcome':
        render_header('Bienvenue');
        ?>
        <div class="step">
            <h2>👋 Bienvenue dans l'installateur</h2>
            <p>Cet assistant va vous guider pour installer la plateforme <strong>Suivi Diagnostics</strong> en quelques minutes.</p>
            <br>
            <p><strong>Ce qui sera fait :</strong></p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>✅ Vérification des prérequis PHP</li>
                <li>✅ Configuration de la base de données</li>
                <li>✅ Suppression complète des données existantes</li>
                <li>✅ Import du schéma (20+ tables)</li>
                <li>✅ Import des données de démonstration</li>
                <li>✅ Génération de la configuration</li>
            </ul>
            <br>
            <p>⏱️ Durée estimée : <strong>2-3 minutes</strong></p>
        </div>

        <div style="text-align: center;">
            <a href="?step=requirements" class="btn">Démarrer l'installation</a>
        </div>
        <?php
        render_footer();
        break;

    case 'requirements':
        render_header('Vérification des prérequis');

        $checks = check_requirements();
        $has_errors = false;
        $has_warnings = false;

        foreach ($checks as $check) {
            if ($check['status'] === 'error') $has_errors = true;
            if ($check['status'] === 'warning') $has_warnings = true;
        }
        ?>

        <div class="step">
            <h2>🔍 Vérification des prérequis</h2>

            <?php foreach ($checks as $check): ?>
                <div class="check-item">
                    <strong><?= htmlspecialchars($check['name']) ?>:</strong>
                    <?= htmlspecialchars($check['value']) ?>
                    <span class="status <?= $check['status'] ?>"><?= strtoupper($check['status']) ?></span>
                    <?php if (!empty($check['message'])): ?>
                        <br><small style="color: #dc3545;"><?= htmlspecialchars($check['message']) ?></small>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($has_errors): ?>
            <div class="alert alert-error">
                <strong>❌ Erreurs détectées</strong><br>
                Corrigez les erreurs ci-dessus avant de continuer.
            </div>
        <?php elseif ($has_warnings): ?>
            <div class="alert alert-warning">
                <strong>⚠️ Avertissements détectés</strong><br>
                Vous pouvez continuer mais certaines fonctionnalités peuvent ne pas marcher.
            </div>
            <div style="text-align: center;">
                <a href="?step=database" class="btn">Continuer quand même</a>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <strong>✅ Tous les prérequis sont satisfaits !</strong><br>
                Vous pouvez continuer l'installation.
            </div>
            <div style="text-align: center;">
                <a href="?step=database" class="btn">Continuer</a>
            </div>
        <?php endif; ?>

        <?php
        render_footer();
        break;

    case 'database':
        render_header('Configuration de la base de données');

        // Valeurs par défaut
        $default_host = 'localhost';
        $default_user = 'gestion';
        $default_dbname = 'gestion';

        // Lire la config existante si elle existe
        $config_file = APP_ROOT . '/config/database.php';
        if (file_exists($config_file)) {
            $config = include $config_file;
            if (is_array($config)) {
                $default_host = $config['host'] ?? $default_host;
                $default_user = $config['username'] ?? $default_user;
                $default_dbname = $config['database'] ?? $default_dbname;
            }
        }
        ?>

        <div class="step">
            <h2>🗄️ Configuration de la base de données</h2>

            <form method="POST" action="?step=install">
                <div class="form-group">
                    <label>Hôte MySQL</label>
                    <input type="text" name="db_host" value="<?= htmlspecialchars($default_host) ?>" required>
                    <small>Généralement "localhost"</small>
                </div>

                <div class="form-group">
                    <label>Nom de la base de données</label>
                    <input type="text" name="db_name" value="<?= htmlspecialchars($default_dbname) ?>" required>
                    <small>Votre base MySQL (ex: gestion)</small>
                </div>

                <div class="form-group">
                    <label>Utilisateur MySQL</label>
                    <input type="text" name="db_user" value="<?= htmlspecialchars($default_user) ?>" required>
                    <small>Votre utilisateur MySQL (ex: gestion)</small>
                </div>

                <div class="form-group">
                    <label>Mot de passe MySQL</label>
                    <input type="password" name="db_pass" required>
                    <small>Le mot de passe de votre utilisateur MySQL</small>
                </div>

                <div class="alert alert-warning">
                    <strong>⚠️ ATTENTION :</strong><br>
                    Cette installation va <strong>SUPPRIMER TOUTES LES DONNÉES</strong> de la base de données sélectionnée et réinstaller proprement l'application.
                </div>

                <div style="text-align: center;">
                    <button type="submit" class="btn btn-danger">
                        Vider la base et réinstaller
                    </button>
                </div>
            </form>
        </div>

        <?php
        render_footer();
        break;

    case 'install':
        // Suite dans le prochain fichier (partie 2)
        include __DIR__ . '/install-process.php';
        break;

    default:
        header('Location: ?step=welcome');
        exit;
}
