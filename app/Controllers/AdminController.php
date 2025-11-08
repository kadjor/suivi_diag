<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Core\Session;
use Models\User;
use Models\Client;
use Models\AuditLog;
use Models\Status;

class AdminController extends Controller
{
    public function __construct()
    {
        if (!Auth::check() || !Auth::can('manage_settings')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/dashboard');
        }
    }

    public function index()
    {
        View::render('admin.index');
    }

    public function users()
    {
        $userModel = new User();
        $users = $userModel->getAll();
        View::render('admin.users', ['users' => $users]);
    }

    public function clients()
    {
        $clientModel = new Client();
        $clients = $clientModel->getAll();
        View::render('admin.clients', ['clients' => $clients]);
    }

    public function auditLogs()
    {
        $auditModel = new AuditLog();
        $logs = $auditModel->getRecent(100);
        View::render('admin.audit_logs', ['logs' => $logs]);
    }

    public function settings()
    {
        // Charger les paramètres depuis la base de données
        try {
            $settingsModel = new \Models\Setting();
            $settings = $settingsModel->getAllAsArray();
        } catch (\Exception $e) {
            // Si la table n'existe pas encore, utiliser un tableau vide
            $settings = [];
        }

        // Charger la configuration email depuis le fichier
        $emailConfigFile = __DIR__ . '/../../config/email.php';
        $emailConfig = file_exists($emailConfigFile) ? require $emailConfigFile : [];

        View::render('admin.settings', [
            'settings' => $settings,
            'emailConfig' => $emailConfig
        ]);
    }

    public function updateSettings()
    {
        if (!Auth::can('manage_settings')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/dashboard');
        }

        try {
            $data = $_POST;

            // Séparer les paramètres email des autres paramètres
            $emailParams = [
                'smtp_host', 'smtp_port', 'smtp_encryption',
                'smtp_username', 'smtp_password', 'from_email',
                'from_name', 'admin_email', 'enabled', 'debug'
            ];

            $emailData = [];
            $otherData = [];

            foreach ($data as $key => $value) {
                if (in_array($key, $emailParams)) {
                    $emailData[$key] = $value;
                } else {
                    $otherData[$key] = $value;
                }
            }

            // Sauvegarder les paramètres email dans le fichier
            if (!empty($emailData)) {
                $this->saveEmailConfig($emailData);
            }

            // Sauvegarder les autres paramètres dans la base de données
            if (!empty($otherData)) {
                $settingsModel = new \Models\Setting();
                foreach ($otherData as $key => $value) {
                    $settingsModel->set($key, $value);
                }
            }

            Session::flash('success', 'Paramètres mis à jour avec succès');
        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }

        View::redirect('/admin/settings');
    }

    /**
     * Sauvegarde la configuration email dans le fichier config/email.php
     */
    private function saveEmailConfig($data)
    {
        $emailConfigFile = __DIR__ . '/../../config/email.php';

        // Charger la configuration actuelle
        $currentConfig = file_exists($emailConfigFile) ? require $emailConfigFile : [];

        // Fusionner avec les nouvelles données
        $newConfig = array_merge($currentConfig, $data);

        // Convertir les booléens
        $newConfig['enabled'] = isset($newConfig['enabled']) && $newConfig['enabled'] === 'on';
        $newConfig['debug'] = isset($newConfig['debug']) && $newConfig['debug'] === 'on';

        // Générer le contenu du fichier
        $content = "<?php\n";
        $content .= "/**\n";
        $content .= " * Configuration Email\n";
        $content .= " * Ce fichier n'est pas versionné et ne sera pas écrasé lors des mises à jour\n";
        $content .= " * Dernière modification : " . date('d/m/Y H:i:s') . "\n";
        $content .= " */\n\n";
        $content .= "return [\n";
        $content .= "    // Configuration SMTP\n";
        $content .= "    'smtp_host' => " . var_export($newConfig['smtp_host'] ?? '', true) . ",\n";
        $content .= "    'smtp_port' => " . var_export((int)($newConfig['smtp_port'] ?? 587), true) . ",\n";
        $content .= "    'smtp_encryption' => " . var_export($newConfig['smtp_encryption'] ?? 'tls', true) . ",\n";
        $content .= "    'smtp_username' => " . var_export($newConfig['smtp_username'] ?? '', true) . ",\n";
        $content .= "    'smtp_password' => " . var_export($newConfig['smtp_password'] ?? '', true) . ",\n\n";
        $content .= "    // Expéditeur par défaut\n";
        $content .= "    'from_email' => " . var_export($newConfig['from_email'] ?? 'noreply@d-evidences.fr', true) . ",\n";
        $content .= "    'from_name' => " . var_export($newConfig['from_name'] ?? 'D-Evidences', true) . ",\n\n";
        $content .= "    // Email de l'administrateur\n";
        $content .= "    'admin_email' => " . var_export($newConfig['admin_email'] ?? 'admin@d-evidences.fr', true) . ",\n\n";
        $content .= "    // Activer/désactiver les emails\n";
        $content .= "    'enabled' => " . var_export($newConfig['enabled'] ?? true, true) . ",\n\n";
        $content .= "    // Mode debug\n";
        $content .= "    'debug' => " . var_export($newConfig['debug'] ?? false, true) . "\n";
        $content .= "];\n";

        // Écrire le fichier
        if (file_put_contents($emailConfigFile, $content) === false) {
            throw new \Exception('Impossible d\'écrire le fichier de configuration email');
        }
    }

    public function testEmail()
    {
        if (!Auth::can('manage_settings')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $email = $_POST['email'] ?? Auth::user()['email'];

        try {
            $emailService = new \Services\EmailService();
            $result = $emailService->send(
                $email,
                'Test d\'envoi d\'email - D-Evidences',
                '<h2>Email de test</h2><p>Si vous recevez cet email, la configuration email fonctionne correctement !</p><p>Date : ' . date('d/m/Y H:i:s') . '</p>'
            );

            if ($result) {
                View::json(['success' => true, 'message' => 'Email de test envoyé à ' . $email]);
            } else {
                View::json(['error' => 'Échec de l\'envoi'], 400);
            }
        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }

    public function referentials()
    {
        // Get all referential data
        $roleModel = new \Models\Role();

        View::render('admin.referentials', [
            'roles' => $roleModel->all()
        ]);
    }

    public function statuses()
    {
        $statusModel = new Status();
        $statuses = $statusModel->getAllGrouped();

        View::render('admin.statuses', [
            'statuses' => $statuses
        ]);
    }

    public function updateStatusColor()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::json(['error' => 'Méthode non autorisée'], 405);
        }

        $statusId = $_POST['status_id'] ?? null;
        $color = $_POST['color'] ?? null;

        if (!$statusId || !$color) {
            View::json(['error' => 'Données manquantes'], 400);
        }

        try {
            $statusModel = new Status();
            $statusModel->updateColor($statusId, $color);

            Session::flash('success', 'Couleur mise à jour avec succès');
            View::json(['success' => true]);
        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }
}
