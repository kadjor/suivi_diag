<?php
/**
 * Script d'installation automatique
 *
 * Commande : php scripts/install.php
 * ou via navigateur : http://votre-domaine.com/scripts/install.php
 */

// Détection environnement CLI vs Web
$isCli = (php_sapi_name() === 'cli');

// Fonction d'affichage
function output($message, $type = 'info') {
    global $isCli;
    $colors = ['success' => "\033[0;32m", 'error' => "\033[0;31m", 'info' => "\033[0;36m", 'warning' => "\033[0;33m"];
    $reset = "\033[0m";

    if ($isCli) {
        echo ($colors[$type] ?? '') . $message . ($reset ?? '') . PHP_EOL;
    } else {
        $colorMap = ['success' => 'green', 'error' => 'red', 'info' => 'blue', 'warning' => 'orange'];
        echo "<p style='color:{$colorMap[$type]};margin:5px 0;'>" . htmlspecialchars($message) . "</p>";
        flush();
    }
}

// HTML header si web
if (!$isCli) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Installation - Suivi Diagnostics</title>
          <style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;background:#f5f5f5}
          h1{color:#333;border-bottom:2px solid #667eea;padding-bottom:10px}
          .box{background:white;padding:20px;border-radius:5px;box-shadow:0 2px 5px rgba(0,0,0,0.1)}</style>
          </head><body><h1>🚀 Installation de la Plateforme de Suivi de Diagnostics</h1><div class='box'>";
}

output("=== DÉBUT DE L'INSTALLATION ===", 'info');
output("");

// Chemin racine
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('DATABASE_PATH', ROOT_PATH . '/database');

// Étape 1 : Vérification des prérequis
output("📋 Étape 1/5 : Vérification des prérequis PHP", 'info');

$requirements = [
    'version' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'pdo' => extension_loaded('pdo'),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'mbstring' => extension_loaded('mbstring'),
    'json' => extension_loaded('json'),
    'fileinfo' => extension_loaded('fileinfo')
];

$allOk = true;
foreach ($requirements as $req => $status) {
    if ($status) {
        output("  ✓ $req : OK", 'success');
    } else {
        output("  ✗ $req : MANQUANT", 'error');
        $allOk = false;
    }
}

if (!$allOk) {
    output("", 'error');
    output("❌ Certains prérequis ne sont pas satisfaits. Installation impossible.", 'error');
    exit(1);
}

output("✅ Tous les prérequis sont satisfaits.", 'success');
output("");

// Étape 2 : Configuration de la base de données
output("🗄️  Étape 2/5 : Configuration de la base de données", 'info');

$dbConfigFile = CONFIG_PATH . '/database.php';
if (!file_exists($dbConfigFile)) {
    output("❌ Fichier config/database.php introuvable.", 'error');
    exit(1);
}

$dbConfig = require $dbConfigFile;

// Test de connexion
try {
    $dsn = sprintf(
        "%s:host=%s;port=%d;charset=%s",
        $dbConfig['driver'],
        $dbConfig['host'],
        $dbConfig['port'],
        $dbConfig['charset']
    );

    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    output("  ✓ Connexion au serveur MySQL réussie", 'success');

    // Créer la base si elle n'existe pas
    $dbName = $dbConfig['database'];
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    output("  ✓ Base de données '{$dbName}' créée ou déjà existante", 'success');

    // Se connecter à la base
    $pdo->exec("USE `{$dbName}`");

} catch (PDOException $e) {
    output("❌ Erreur de connexion : " . $e->getMessage(), 'error');
    output("   Vérifiez vos identifiants dans config/database.php", 'warning');
    exit(1);
}

output("");

// Étape 3 : Création du schéma
output("📊 Étape 3/5 : Création du schéma de base de données", 'info');

$schemaFile = DATABASE_PATH . '/schema.sql';
if (!file_exists($schemaFile)) {
    output("❌ Fichier database/schema.sql introuvable.", 'error');
    exit(1);
}

try {
    $schema = file_get_contents($schemaFile);
    $pdo->exec($schema);
    output("✅ Schéma créé avec succès.", 'success');
} catch (PDOException $e) {
    // Si les tables existent déjà, continuer
    if (strpos($e->getMessage(), 'already exists') !== false) {
        output("⚠️  Les tables existent déjà, on passe cette étape.", 'warning');
    } else {
        output("❌ Erreur lors de la création du schéma : " . $e->getMessage(), 'error');
        exit(1);
    }
}

output("");

// Étape 4 : Insertion des données initiales
output("🌱 Étape 4/5 : Insertion des données initiales et de démonstration", 'info');

$seedFile = DATABASE_PATH . '/seeds/001_initial_data.sql';
if (file_exists($seedFile)) {
    try {
        // Vérifier si des données existent déjà
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            output("⚠️  Des données existent déjà. Seeds ignorés.", 'warning');
        } else {
            $seeds = file_get_contents($seedFile);
            $pdo->exec($seeds);
            output("✅ Données de démonstration insérées.", 'success');
        }
    } catch (PDOException $e) {
        output("⚠️  Erreur lors de l'insertion des seeds : " . $e->getMessage(), 'warning');
    }
} else {
    output("⚠️  Fichier de seeds non trouvé, on continue...", 'warning');
}

output("");

// Étape 5 : Vérification des permissions fichiers
output("🔐 Étape 5/5 : Vérification des permissions", 'info');

$dirsToCheck = [
    ROOT_PATH . '/logs',
    ROOT_PATH . '/public/uploads',
    ROOT_PATH . '/public/uploads/reports',
    ROOT_PATH . '/public/uploads/attachments',
    ROOT_PATH . '/public/uploads/acknowledgments',
    ROOT_PATH . '/public/uploads/imports',
    ROOT_PATH . '/public/uploads/temp'
];

foreach ($dirsToCheck as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0770, true)) {
            output("  ✓ Répertoire créé : " . basename(dirname($dir)) . '/' . basename($dir), 'success');
        } else {
            output("  ✗ Impossible de créer : " . $dir, 'error');
        }
    }

    if (is_writable($dir)) {
        output("  ✓ Répertoire accessible en écriture : " . basename($dir), 'success');
    } else {
        output("  ⚠️  Répertoire non accessible en écriture : {$dir}", 'warning');
        output("     Exécutez : chmod 770 {$dir}", 'warning');
    }
}

output("");

// Résumé final
output("=== INSTALLATION TERMINÉE ===", 'success');
output("");
output("🎉 La plateforme est prête à être utilisée !", 'success');
output("");
output("📝 Comptes de démonstration (mot de passe pour tous : Demo2024!)", 'info');
output("   • Administrateur  : admin / Demo2024!", 'info');
output("   • Secrétariat     : secretariat / Demo2024!", 'info');
output("   • Technicien      : tech1 / Demo2024!", 'info');
output("   • Client PCH      : client.pch / Demo2024!", 'info');
output("");
output("🔗 Accédez à la plateforme : " . (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : 'http://localhost'), 'info');
output("");
output("📖 Consultez docs/INSTALL.md pour plus d'informations", 'info');
output("");

if (!$isCli) {
    echo "</div><div class='box' style='margin-top:20px;text-align:center'>
          <a href='../public/index.php' style='display:inline-block;padding:15px 30px;background:#667eea;color:white;text-decoration:none;border-radius:5px;font-weight:bold'>
          🚀 Accéder à la plateforme</a></div></body></html>";
}
