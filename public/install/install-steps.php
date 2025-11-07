<?php
/**
 * API pour les étapes d'installation
 * Traite chaque étape individuellement via AJAX
 */

header('Content-Type: application/json');

// Configuration
define('APP_ROOT', dirname(dirname(__DIR__)));

// Récupérer l'étape demandée
$step = $_POST['step'] ?? '';

// Récupérer les identifiants de connexion
$db_host = $_POST['db_host'] ?? '';
$db_name = $_POST['db_name'] ?? '';
$db_user = $_POST['db_user'] ?? '';
$db_pass = $_POST['db_pass'] ?? '';

// Fonction utilitaire pour se connecter à MySQL
function connect_mysql($host, $user, $pass, $dbname = null) {
    try {
        $dsn = $dbname ? "mysql:host=$host;dbname=$dbname;charset=utf8mb4" : "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return ['success' => true, 'pdo' => $pdo];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Traiter l'étape demandée
switch ($step) {

    // Étape 1 : Tester la connexion MySQL
    case 'test_connection':
        $result = connect_mysql($db_host, $db_user, $db_pass);

        if (!$result['success']) {
            echo json_encode([
                'success' => false,
                'error' => 'Impossible de se connecter à MySQL : ' . $result['error']
            ]);
            exit;
        }

        $pdo = $result['pdo'];

        // Vérifier si la base de données existe
        try {
            $stmt = $pdo->query("SHOW DATABASES LIKE '$db_name'");
            $db_exists = $stmt->rowCount() > 0;

            // Si la base n'existe pas, essayer de la créer
            if (!$db_exists) {
                $pdo->exec("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }

            echo json_encode([
                'success' => true,
                'db_exists' => $db_exists,
                'message' => $db_exists ? 'Base de données existante' : 'Base de données créée'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de la vérification de la base : ' . $e->getMessage()
            ]);
        }
        break;

    // Étape 2 : Supprimer toutes les tables existantes
    case 'drop_tables':
        $result = connect_mysql($db_host, $db_user, $db_pass, $db_name);

        if (!$result['success']) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            exit;
        }

        $pdo = $result['pdo'];

        try {
            // Désactiver les contraintes de clés étrangères
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

            // Récupérer la liste des tables
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Supprimer chaque table
            foreach ($tables as $table) {
                $pdo->exec("DROP TABLE IF EXISTS `$table`");
            }

            // Réactiver les contraintes
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

            echo json_encode([
                'success' => true,
                'count' => count($tables),
                'message' => count($tables) . ' table(s) supprimée(s)'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de la suppression des tables : ' . $e->getMessage()
            ]);
        }
        break;

    // Étape 3 : Importer le schéma
    case 'import_schema':
        $result = connect_mysql($db_host, $db_user, $db_pass, $db_name);

        if (!$result['success']) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            exit;
        }

        $pdo = $result['pdo'];
        $schema_file = APP_ROOT . '/database/schema.sql';

        if (!file_exists($schema_file)) {
            echo json_encode([
                'success' => false,
                'error' => 'Fichier schema.sql introuvable : ' . $schema_file
            ]);
            exit;
        }

        try {
            // Lire le fichier SQL
            $sql = file_get_contents($schema_file);

            // Supprimer les commentaires et lignes vides
            $sql = preg_replace('/^--.*$/m', '', $sql);
            $sql = preg_replace('/^\s*$/m', '', $sql);

            // Exécuter le SQL
            $pdo->exec($sql);

            // Compter les tables créées
            $stmt = $pdo->query("SHOW TABLES");
            $table_count = $stmt->rowCount();

            echo json_encode([
                'success' => true,
                'tables' => $table_count,
                'message' => $table_count . ' table(s) créée(s)'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de l\'import du schéma : ' . $e->getMessage()
            ]);
        }
        break;

    // Étape 4 : Importer les données de démonstration
    case 'import_seeds':
        $result = connect_mysql($db_host, $db_user, $db_pass, $db_name);

        if (!$result['success']) {
            echo json_encode(['success' => false, 'error' => $result['error']]);
            exit;
        }

        $pdo = $result['pdo'];
        $seeds_file = APP_ROOT . '/database/seeds/001_initial_data.sql';

        if (!file_exists($seeds_file)) {
            echo json_encode([
                'success' => false,
                'error' => 'Fichier seeds introuvable : ' . $seeds_file
            ]);
            exit;
        }

        try {
            // Lire le fichier SQL
            $sql = file_get_contents($seeds_file);

            // Supprimer les commentaires et lignes vides
            $sql = preg_replace('/^--.*$/m', '', $sql);
            $sql = preg_replace('/^\s*$/m', '', $sql);

            // Exécuter le SQL
            $pdo->exec($sql);

            // Compter les utilisateurs créés
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $user_count = $stmt->fetch()['count'];

            echo json_encode([
                'success' => true,
                'users' => $user_count,
                'message' => $user_count . ' utilisateur(s) créé(s)'
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de l\'import des données : ' . $e->getMessage()
            ]);
        }
        break;

    // Étape 5 : Générer le fichier de configuration
    case 'generate_config':
        $config_file = APP_ROOT . '/config/database.php';
        $config_dir = dirname($config_file);

        // Créer le dossier config s'il n'existe pas
        if (!is_dir($config_dir)) {
            mkdir($config_dir, 0755, true);
        }

        // Générer le contenu du fichier
        $config_content = <<<PHP
<?php
/**
 * Configuration de la base de données
 * Généré automatiquement par l'installateur Web
 * Date : {DATE}
 */

return [
    'driver' => 'mysql',
    'host' => '{HOST}',
    'port' => 3306,
    'database' => '{DATABASE}',
    'username' => '{USERNAME}',
    'password' => '{PASSWORD}',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]
];

PHP;

        // Remplacer les placeholders
        $config_content = str_replace('{DATE}', date('Y-m-d H:i:s'), $config_content);
        $config_content = str_replace('{HOST}', $db_host, $config_content);
        $config_content = str_replace('{DATABASE}', $db_name, $config_content);
        $config_content = str_replace('{USERNAME}', $db_user, $config_content);
        $config_content = str_replace('{PASSWORD}', $db_pass, $config_content);

        // Sauvegarder le fichier existant si présent
        if (file_exists($config_file)) {
            $backup = $config_file . '.backup.' . date('YmdHis');
            copy($config_file, $backup);
        }

        // Écrire le nouveau fichier
        if (file_put_contents($config_file, $config_content) !== false) {
            chmod($config_file, 0640);

            echo json_encode([
                'success' => true,
                'file' => $config_file,
                'message' => 'Configuration générée avec succès'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Impossible d\'écrire le fichier de configuration. Vérifiez les permissions du dossier config/'
            ]);
        }
        break;

    default:
        echo json_encode([
            'success' => false,
            'error' => 'Étape inconnue : ' . $step
        ]);
        break;
}
