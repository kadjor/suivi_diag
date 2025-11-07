<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Models\Order;
use Models\Site;
use Models\Diagnostic;
use Models\Intervention;

/**
 * Contrôleur du tableau de bord
 */
class DashboardController extends Controller
{
    public function __construct()
    {
        // Vérifier l'authentification
        if (!Auth::check()) {
            View::redirect('/login');
        }
    }

    /**
     * Affiche le tableau de bord selon le rôle
     */
    public function index()
    {
        $user = Auth::user();

        switch ($user['role_name']) {
            case 'admin':
                $this->adminDashboard();
                break;
            case 'secretariat':
                $this->secretaryDashboard();
                break;
            case 'technicien':
                $this->technicianDashboard();
                break;
            case 'client':
                $this->clientDashboard();
                break;
            default:
                View::redirect('/login');
        }
    }

    /**
     * Dashboard Administrateur
     */
    private function adminDashboard()
    {
        $orderModel = new Order();
        $siteModel = new Site();
        $diagnosticModel = new Diagnostic();

        $data = [
            'total_orders' => $orderModel->count(),
            'pending_orders' => $orderModel->countByStatus('pending'),
            'in_progress_orders' => $orderModel->countByStatus('in_progress'),
            'completed_orders' => $orderModel->countByStatus('completed'),
            'total_sites' => $siteModel->count(),
            'total_diagnostics' => $diagnosticModel->count(),
            'recent_orders' => $orderModel->getRecent(10),
            'orders_by_month' => $orderModel->getOrdersByMonth(12)
        ];

        View::render('dashboard.admin', $data);
    }

    /**
     * Dashboard Secrétariat
     */
    private function secretaryDashboard()
    {
        $orderModel = new Order();

        $data = [
            'pending_orders' => $orderModel->getByStatus('pending', 10),
            'confirmed_orders' => $orderModel->getByStatus('confirmed', 10),
            'recent_orders' => $orderModel->getRecent(10),
            'orders_to_plan' => $orderModel->getOrdersToSchedule(20)
        ];

        View::render('dashboard.secretary', $data);
    }

    /**
     * Dashboard Technicien
     */
    private function technicianDashboard()
    {
        $user = Auth::user();
        $interventionModel = new Intervention();
        $orderModel = new Order();

        $data = [
            'my_interventions' => $interventionModel->getByTechnician($user['id'], 'scheduled'),
            'todays_appointments' => $interventionModel->getTodayAppointments($user['id']),
            'upcoming_appointments' => $interventionModel->getUpcomingAppointments($user['id'], 7),
            'assigned_orders' => $orderModel->getAssignedToTechnician($user['id'])
        ];

        View::render('dashboard.technician', $data);
    }

    /**
     * Dashboard Client
     */
    private function clientDashboard()
    {
        $user = Auth::user();
        $orderModel = new Order();
        $siteModel = new Site();
        $diagnosticModel = new Diagnostic();

        // Récupérer le client_id associé à cet utilisateur
        $clientId = $user['client_id'] ?? null;

        if (!$clientId) {
            View::render('dashboard.client', [
                'error' => 'Aucun client associé à votre compte'
            ]);
            return;
        }

        $data = [
            'my_orders' => $orderModel->getByClient($clientId, 10),
            'my_sites' => $siteModel->getByClient($clientId),
            'my_diagnostics' => $diagnosticModel->getByClient($clientId),
            'pending_reports' => $orderModel->getWithPendingReports($clientId)
        ];

        View::render('dashboard.client', $data);
    }

    /**
     * Statistiques (AJAX)
     */
    public function stats()
    {
        if (!Auth::can('view_statistics')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $period = $_GET['period'] ?? 'month';
        $orderModel = new Order();

        $stats = [
            'orders_by_status' => $orderModel->getOrdersByStatus(),
            'orders_by_type' => $orderModel->getOrdersByType(),
            'revenue' => $orderModel->getRevenue($period)
        ];

        View::json($stats);
    }
}
