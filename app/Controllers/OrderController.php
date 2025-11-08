<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Core\Session;
use Models\Order;
use Models\Client;
use Models\Site;
use Models\OrderEvent;
use Models\AuditLog;
use Helpers\Validator;

/**
 * Contrôleur de gestion des commandes
 */
class OrderController extends Controller
{
    private $orderModel;

    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }

        $this->orderModel = new Order();
    }

    /**
     * Liste des commandes
     */
    public function index()
    {
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? null;
        $user = Auth::user();

        if ($user['role_name'] === 'client') {
            $orders = $this->orderModel->getByClient($user['client_id'], 20, $page);
        } else {
            $orders = $status
                ? $this->orderModel->getByStatus($status, 20, $page)
                : $this->orderModel->getAll($page, 20);
        }

        View::render('orders.index', [
            'orders' => $orders,
            'current_status' => $status
        ]);
    }

    /**
     * Détails d'une commande
     */
    public function show($id)
    {
        $order = $this->orderModel->find($id);

        if (!$order) {
            Session::flash('error', 'Commande introuvable');
            View::redirect('/orders');
        }

        // Vérifier les permissions
        $user = Auth::user();
        if ($user['role_name'] === 'client' && $order['client_id'] != $user['client_id']) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/orders');
        }

        $orderEventModel = new OrderEvent();

        View::render('orders.show', [
            'order' => $order,
            'events' => $orderEventModel->getByOrder($id),
            'can_edit' => Auth::can('manage_orders')
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        if (!Auth::can('create_orders')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/orders');
        }

        $clientModel = new Client();
        $siteModel = new Site();

        View::render('orders.create', [
            'clients' => $clientModel->getAll(),
            'sites' => $siteModel->getAll()
        ]);
    }

    /**
     * Enregistrement d'une nouvelle commande
     */
    public function store()
    {
        if (!Auth::can('create_orders')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $data = [
            'client_id' => $_POST['client_id'] ?? null,
            'site_id' => $_POST['site_id'] ?? null,
            'reference' => $_POST['reference'] ?? $this->orderModel->generateReference(),
            'diagnostic_types' => $_POST['diagnostic_types'] ?? [],
            'priority' => $_POST['priority'] ?? 'normal',
            'notes' => $_POST['notes'] ?? ''
        ];

        $validator = new Validator($data);
        $validator->required(['client_id', 'site_id']);

        if (!$validator->validate()) {
            Session::flash('error', implode(', ', $validator->getErrors()));
            View::redirect('/orders/create');
        }

        try {
            $orderId = $this->orderModel->create([
                'client_id' => $data['client_id'],
                'site_id' => $data['site_id'],
                'reference' => $data['reference'],
                'status' => 'draft',
                'priority' => $data['priority'],
                'notes' => $data['notes'],
                'created_by' => Auth::id()
            ]);

            // Créer l'événement de création
            $eventModel = new OrderEvent();
            $eventModel->create([
                'order_id' => $orderId,
                'event_type' => 'created',
                'user_id' => Auth::id(),
                'description' => 'Commande créée'
            ]);

            // Log audit
            AuditLog::log('order_created', 'orders', $orderId);

            Session::flash('success', 'Commande créée avec succès');
            View::redirect("/orders/{$orderId}");

        } catch (\Exception $e) {
            log_message("Order creation failed: " . $e->getMessage(), 'error');
            Session::flash('error', 'Erreur lors de la création de la commande');
            View::redirect('/orders/create');
        }
    }

    /**
     * Mettre à jour le statut
     */
    public function updateStatus($id)
    {
        if (!Auth::can('manage_orders')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $newStatus = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? '';

        try {
            $this->orderModel->updateStatus($id, $newStatus, Auth::id(), $notes);

            Session::flash('success', 'Statut mis à jour');
            View::json(['success' => true]);

        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Assigner un technicien
     */
    public function assignTechnician($id)
    {
        if (!Auth::can('assign_orders')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $technicianId = $_POST['technician_id'] ?? null;

        if (!$technicianId) {
            View::json(['error' => 'Technicien requis'], 400);
        }

        try {
            $this->orderModel->update($id, ['technician_id' => $technicianId]);

            $eventModel = new OrderEvent();
            $eventModel->create([
                'order_id' => $id,
                'event_type' => 'assigned',
                'user_id' => Auth::id(),
                'description' => "Commande assignée au technicien"
            ]);

            AuditLog::log('order_assigned', 'orders', $id);

            View::json(['success' => true]);

        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Supprimer une commande
     */
    public function delete($id)
    {
        if (!Auth::can('manage_orders')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        try {
            $this->orderModel->delete($id);
            AuditLog::log('order_deleted', 'orders', $id);

            Session::flash('success', 'Commande supprimée');
            View::redirect('/orders');

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la suppression');
            View::redirect('/orders');
        }
    }
}
