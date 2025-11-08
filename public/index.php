<?php
/**
 * Point d'entrée de l'application
 *
 * Ce fichier initialise l'application et route les requêtes
 */

// Démarrage session
session_start();

// Définir les chemins
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('LOG_PATH', ROOT_PATH . '/logs');
define('LIB_PATH', ROOT_PATH . '/libs');

// Chargement de la configuration
$config = require CONFIG_PATH . '/app.php';
$dbConfig = require CONFIG_PATH . '/database.php';

// Configuration PHP
error_reporting($config['environment'] === 'production' ? 0 : E_ALL);
ini_set('display_errors', $config['environment'] === 'production' ? '0' : '1');
date_default_timezone_set($config['timezone']);

// Autoloader simple
spl_autoload_register(function ($class) {
    // Conversion du namespace en chemin de fichier
    $file = APP_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Gestion des erreurs
set_error_handler(function ($severity, $message, $file, $line) use ($config) {
    if (!(error_reporting() & $severity)) {
        return;
    }

    $log = sprintf(
        "[%s] Error: %s in %s on line %d\n",
        date('Y-m-d H:i:s'),
        $message,
        $file,
        $line
    );

    if ($config['logging']['enabled']) {
        file_put_contents($config['logging']['error_log'], $log, FILE_APPEND);
    }

    if ($config['environment'] === 'production') {
        // Afficher page d'erreur générique
        http_response_code(500);
        require APP_PATH . '/Views/errors/500.php';
        exit;
    } else {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

// Gestion des exceptions
set_exception_handler(function ($exception) use ($config) {
    $log = sprintf(
        "[%s] Exception: %s in %s on line %d\nStack trace:\n%s\n",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );

    if ($config['logging']['enabled']) {
        file_put_contents($config['logging']['error_log'], $log, FILE_APPEND);
    }

    if ($config['environment'] === 'production') {
        http_response_code(500);
        require APP_PATH . '/Views/errors/500.php';
    } else {
        echo '<pre>' . htmlspecialchars($log) . '</pre>';
    }
    exit;
});

// Helper functions
require_once APP_PATH . '/Helpers/functions.php';

// Initialisation de la base de données
try {
    Core\Database::init($dbConfig);
} catch (Exception $e) {
    if ($config['environment'] === 'production') {
        http_response_code(503);
        echo "Service temporairement indisponible.";
    } else {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
    }
    exit;
}

// Initialisation du routeur
$router = new Core\Router();

// Routes publiques
$router->get('/', 'Controllers\AuthController@showLogin');
$router->get('/login', 'Controllers\AuthController@showLogin');
$router->post('/login', 'Controllers\AuthController@login');
$router->get('/logout', 'Controllers\AuthController@logout');

// Routes protégées - Dashboard
$router->get('/dashboard', 'Controllers\DashboardController@index');

// Routes protégées - Commandes
$router->get('/orders', 'Controllers\OrderController@index');
$router->get('/orders/create', 'Controllers\OrderController@create');
$router->post('/orders/store', 'Controllers\OrderController@store');
$router->get('/orders/{id}', 'Controllers\OrderController@show');
$router->get('/orders/{id}/edit', 'Controllers\OrderController@edit');
$router->post('/orders/{id}/update', 'Controllers\OrderController@update');
$router->post('/orders/{id}/assign', 'Controllers\OrderController@assign');
$router->post('/orders/{id}/close', 'Controllers\OrderController@close');
$router->get('/orders/{id}/timeline', 'Controllers\OrderController@timeline');
$router->post('/orders/{id}/generate-ar', 'Controllers\OrderController@generateAcknowledgment');

// Routes protégées - Interventions
$router->get('/interventions', 'Controllers\InterventionController@index');
$router->post('/interventions/create', 'Controllers\InterventionController@create');
$router->post('/interventions/update-status', 'Controllers\InterventionController@updateStatus');

// Routes protégées - Sites
$router->get('/sites', 'Controllers\SiteController@index');
$router->get('/sites/create', 'Controllers\SiteController@create');
$router->post('/sites/store', 'Controllers\SiteController@store');
$router->get('/sites/{id}', 'Controllers\SiteController@show');
$router->get('/sites/{id}/edit', 'Controllers\SiteController@edit');
$router->post('/sites/{id}/update', 'Controllers\SiteController@update');

// Routes protégées - Clients
$router->get('/clients', 'Controllers\ClientController@index');
$router->get('/clients/create', 'Controllers\ClientController@create');
$router->post('/clients/store', 'Controllers\ClientController@store');
$router->get('/clients/{id}', 'Controllers\ClientController@show');
$router->get('/clients/{id}/edit', 'Controllers\ClientController@edit');
$router->post('/clients/{id}/update', 'Controllers\ClientController@update');

// Routes protégées - Cartographie
$router->get('/map', 'Controllers\MapController@index');
$router->get('/map/data', 'Controllers\MapController@getData');
$router->get('/map/site/{id}', 'Controllers\MapController@siteDetails');

// Routes protégées - Rapports
$router->get('/reports', 'Controllers\ReportController@index');
$router->post('/reports/upload', 'Controllers\ReportController@upload');
$router->get('/reports/{id}/download', 'Controllers\ReportController@download');
$router->post('/reports/import-excel', 'Controllers\ReportController@importExcel');
$router->post('/reports/confirm-import', 'Controllers\ReportController@confirmImport');

// Routes protégées - Messages
$router->get('/messages', 'Controllers\MessageController@index');
$router->get('/orders/{id}/messages', 'Controllers\MessageController@index');
$router->post('/orders/{id}/messages', 'Controllers\MessageController@store');

// Routes protégées - Calendrier
$router->get('/calendar', 'Controllers\CalendarController@index');
$router->get('/calendar/events', 'Controllers\CalendarController@events');
$router->post('/appointments/store', 'Controllers\AppointmentController@store');
$router->get('/appointments/{id}/ics', 'Controllers\AppointmentController@exportIcs');

// Routes protégées - Utilisateurs
$router->get('/users', 'Controllers\UserController@index');
$router->get('/users/create', 'Controllers\UserController@create');
$router->post('/users/store', 'Controllers\UserController@store');
$router->get('/users/{id}/edit', 'Controllers\UserController@edit');
$router->post('/users/{id}/update', 'Controllers\UserController@update');
$router->post('/users/{id}/delete', 'Controllers\UserController@delete');

// Routes protégées - Profil utilisateur
$router->get('/profile', 'Controllers\UserController@profile');
$router->post('/profile/update', 'Controllers\UserController@updateProfile');
$router->post('/profile/change-password', 'Controllers\UserController@changePassword');

// Routes protégées - Cartographie
$router->get('/map', 'Controllers\MapController@index');
$router->get('/map/nearby-orders', 'Controllers\MapController@getNearbyOrders');
$router->get('/map/search-address', 'Controllers\MapController@searchAddress');

// Routes protégées - Administration
$router->get('/admin', 'Controllers\AdminController@index');
$router->get('/admin/users', 'Controllers\AdminController@users');
$router->get('/admin/settings', 'Controllers\AdminController@settings');
$router->post('/admin/settings', 'Controllers\AdminController@updateSettings');
$router->post('/admin/test-email', 'Controllers\AdminController@testEmail');
$router->get('/admin/audit-logs', 'Controllers\AdminController@auditLogs');
$router->get('/admin/referentials', 'Controllers\AdminController@referentials');
$router->get('/admin/statuses', 'Controllers\AdminController@statuses');
$router->post('/admin/statuses/update-color', 'Controllers\AdminController@updateStatusColor');

// Routes protégées - Déploiement (admin seulement)
$router->get('/deploy', 'Controllers\DeployController@index');
$router->post('/deploy/pull', 'Controllers\DeployController@pull');
$router->post('/deploy/download-github', 'Controllers\DeployController@downloadFromGithub');
$router->post('/deploy/apply-permissions', 'Controllers\DeployController@applyPermissions');
$router->get('/deploy/diff', 'Controllers\DeployController@diff');
$router->post('/deploy/reset', 'Controllers\DeployController@reset');
$router->get('/deploy/backups', 'Controllers\DeployController@backups');
$router->get('/deploy/migrations', 'Controllers\DeployController@migrations');
$router->post('/deploy/run-migrations', 'Controllers\DeployController@runMigrations');
$router->post('/deploy/reset-migrations', 'Controllers\DeployController@resetMigrations');

// Routes protégées - Exports
$router->get('/export/orders', 'Controllers\ExportController@orders');
$router->get('/export/interventions', 'Controllers\ExportController@interventions');
$router->get('/export/diagnostics', 'Controllers\ExportController@diagnostics');

// Récupération de l'URI et de la méthode
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Suppression du query string
if (($pos = strpos($requestUri, '?')) !== false) {
    $requestUri = substr($requestUri, 0, $pos);
}

// Suppression du chemin de base si l'app n'est pas à la racine
$basePath = $config['base_path'];
if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Dispatch de la requête
try {
    $router->dispatch($requestUri, $requestMethod);
} catch (Exception $e) {
    if ($config['environment'] === 'production') {
        http_response_code(404);
        require APP_PATH . '/Views/errors/404.php';
    } else {
        throw $e;
    }
}
