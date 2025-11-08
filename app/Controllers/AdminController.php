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
        $settingsModel = new \Models\Setting();
        $settings = $settingsModel->getAllAsArray();

        View::render('admin.settings', ['settings' => $settings]);
    }

    public function updateSettings()
    {
        if (!Auth::can('manage_settings')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/dashboard');
        }

        try {
            $settingsModel = new \Models\Setting();
            $data = $_POST;

            // Sauvegarder chaque paramètre
            foreach ($data as $key => $value) {
                $settingsModel->set($key, $value);
            }

            Session::flash('success', 'Paramètres mis à jour avec succès');
        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }

        View::redirect('/admin/settings');
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
