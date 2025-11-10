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
use Models\User;
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
        $order = $this->orderModel->getWithDetails($id);

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
        $reportModel = new \Models\Report();
        $interventionModel = new \Models\Intervention();
        $userModel = new \Models\User();

        View::render('orders.show', [
            'order' => $order,
            'events' => $orderEventModel->getByOrder($id),
            'reports' => $reportModel->getByOrderId($id),
            'interventions' => $interventionModel->getByOrderId($id),
            'technicians' => $userModel->getByRole('technicien'),
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
        $diagnosticTypeModel = new \Models\DiagnosticType();

        View::render('orders.create', [
            'clients' => $clientModel->getAll(),
            'diagnostic_types' => $diagnosticTypeModel->getActive()
        ]);
    }

    /**
     * Enregistrement d'une nouvelle commande
     */
    public function store()
    {
        if (!Auth::can('create_orders')) {
            View::json(['error' => 'Accès refusé'], 403);
            return;
        }

        try {
            // Récupérer et valider les données
            $clientId = $_POST['client_id'] ?? null;
            $siteId = $_POST['site_id'] ?? null;
            $numeroLot = $_POST['numero_lot'] ?? null;
            $diagnosticTypeIds = $_POST['diagnostic_types'] ?? [];

            if (!$clientId) {
                throw new \Exception('Client requis');
            }

            if (empty($diagnosticTypeIds)) {
                throw new \Exception('Au moins un diagnostic doit être sélectionné');
            }

            // Générer le numéro de commande
            $orderNumber = $this->orderModel->generateOrderNumber();

            // Gérer l'upload du PDF
            $pdfPath = null;
            if (isset($_FILES['bon_de_commande_pdf']) && $_FILES['bon_de_commande_pdf']['error'] === UPLOAD_ERR_OK) {
                $pdfPath = $this->handlePdfUpload($_FILES['bon_de_commande_pdf'], $orderNumber);
            }

            // Récupérer le statut initial
            $statusModel = new \Models\Status();
            $initialStatus = $statusModel->getByCode('order', 'pending');

            // Récupérer les destinataires des rapports
            $reportRecipients = $_POST['report_recipients'] ?? null;

            // Préparer les données de la commande
            $orderData = [
                'client_id' => $clientId,
                'site_id' => $siteId,
                'order_number' => $orderNumber,
                'numero_lot' => $numeroLot,
                'execution_address' => $_POST['execution_address'] ?? null,
                'execution_city' => $_POST['execution_city'] ?? null,
                'execution_postal_code' => $_POST['execution_postal_code'] ?? null,
                'execution_numero_porte' => $_POST['execution_numero_porte'] ?? null,
                'execution_niveau' => $_POST['execution_niveau'] ?? null,
                'bon_de_commande_pdf' => $pdfPath,
                'report_recipients' => $reportRecipients,
                'status_id' => $initialStatus['id'] ?? 1,
                'priority' => $_POST['priority'] ?? 'normal',
                'notes' => $_POST['notes'] ?? '',
                'created_by' => Auth::id(),
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Créer la commande avec les diagnostics
            $orderDiagnosticModel = new \Models\OrderDiagnostic();
            $orderId = $this->orderModel->createWithDiagnostics(
                $orderData,
                $diagnosticTypeIds,
                $_POST['diagnostic_notes'] ?? []
            );

            // Si aucun site n'est fourni mais qu'on a une adresse, créer le site automatiquement
            if (!$siteId && !empty($orderData['execution_address'])) {
                $this->orderModel->createSiteFromOrder($orderId);
            }

            // Créer l'événement de création
            $eventModel = new OrderEvent();
            $eventModel->create([
                'order_id' => $orderId,
                'event_type' => 'created',
                'user_id' => Auth::id(),
                'data' => json_encode(['order_number' => $orderNumber])
            ]);

            // Log audit
            AuditLog::log('order_created', 'orders', $orderId, [
                'order_number' => $orderNumber,
                'client_id' => $clientId
            ]);

            // Notifier le secrétariat par email
            try {
                $clientModel = new Client();
                $client = $clientModel->find($clientId);

                $creator = Auth::user();

                $order = $this->orderModel->find($orderId);
                $order['order_number'] = $orderNumber;
                $order['execution_address'] = $orderData['execution_address'];
                $order['execution_city'] = $orderData['execution_city'];
                $order['execution_postal_code'] = $orderData['execution_postal_code'];
                $order['execution_numero_porte'] = $orderData['execution_numero_porte'];
                $order['execution_niveau'] = $orderData['execution_niveau'];
                $order['numero_lot'] = $orderData['numero_lot'];

                \Helpers\Email::notifyOrderCreated($order, $client, $creator);

                // Marquer comme notifié
                $this->orderModel->update($orderId, [
                    'notification_sent' => true,
                    'notification_sent_at' => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                // Ne pas bloquer la création si l'email échoue
                error_log('Erreur notification: ' . $e->getMessage());
            }

            Session::flash('success', "Commande {$orderNumber} créée avec succès");
            View::redirect("/orders/{$orderId}");

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur: ' . $e->getMessage());
            View::redirect('/orders/create');
        }
    }

    /**
     * Gère l'upload du PDF du bon de commande
     */
    private function handlePdfUpload($file, $orderNumber)
    {
        $uploadDir = ROOT_PATH . '/public/uploads/orders/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            throw new \Exception('Seuls les fichiers PDF sont acceptés');
        }

        $filename = 'BON_' . $orderNumber . '_' . time() . '.pdf';
        $filepath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new \Exception('Erreur lors de l\'upload du PDF');
        }

        return '/uploads/orders/' . $filename;
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

    /**
     * Formulaire de modification
     */
    public function edit($id)
    {
        $order = $this->orderModel->getWithDetails($id);

        if (!$order) {
            Session::flash('error', 'Commande introuvable');
            View::redirect('/orders');
        }

        if (!Auth::can('manage_orders')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/orders');
        }

        $clientModel = new Client();
        $siteModel = new Site();
        $userModel = new User();

        View::render('orders.edit', [
            'order' => $order,
            'clients' => $clientModel->getAll(),
            'sites' => $siteModel->getAll(),
            'technicians' => $userModel->query("SELECT * FROM users WHERE role_id IN (SELECT id FROM roles WHERE name = 'technicien') AND active = 1")
        ]);
    }

    /**
     * Mettre à jour une commande
     */
    public function update($id)
    {
        if (!Auth::can('manage_orders')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/orders');
        }

        $order = $this->orderModel->find($id);

        if (!$order) {
            Session::flash('error', 'Commande introuvable');
            View::redirect('/orders');
        }

        $data = $_POST;

        $validator = new Validator($data);
        $validator->required(['client_id']);

        if (!$validator->validate()) {
            Session::flash('error', implode(', ', $validator->getErrors()));
            View::redirect('/orders/' . $id . '/edit');
        }

        try {
            $this->orderModel->update($id, [
                'client_id' => $data['client_id'],
                'requested_date' => !empty($data['requested_date']) ? $data['requested_date'] : null,
                'deadline_date' => !empty($data['deadline_date']) ? $data['deadline_date'] : null,
                'assigned_to' => !empty($data['assigned_to']) ? $data['assigned_to'] : null,
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'] ?? 'normal'
            ]);

            $eventModel = new OrderEvent();
            $eventModel->create([
                'order_id' => $id,
                'event_type' => 'updated',
                'user_id' => Auth::id(),
                'description' => "Commande mise à jour"
            ]);

            Session::flash('success', 'Commande mise à jour avec succès');
            View::redirect('/orders/' . $id);

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
            View::redirect('/orders/' . $id . '/edit');
        }
    }

    /**
     * Alias pour assignTechnician (pour correspondre à la route)
     */
    public function assign($id)
    {
        return $this->assignTechnician($id);
    }

    /**
     * Clôturer une commande
     */
    public function close($id)
    {
        if (!Auth::can('manage_orders')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $order = $this->orderModel->find($id);

        if (!$order) {
            View::json(['error' => 'Commande introuvable'], 404);
        }

        try {
            // Mettre à jour le statut vers "terminé"
            $this->orderModel->query(
                "UPDATE orders SET status_id = (SELECT id FROM statuses WHERE code = 'completed' LIMIT 1) WHERE id = ?",
                [$id]
            );

            $eventModel = new OrderEvent();
            $eventModel->create([
                'order_id' => $id,
                'event_type' => 'closed',
                'user_id' => Auth::id(),
                'description' => "Commande clôturée"
            ]);

            Session::flash('success', 'Commande clôturée avec succès');
            View::json(['success' => true, 'redirect' => '/orders/' . $id]);

        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Afficher la timeline d'une commande
     */
    public function timeline($id)
    {
        $order = $this->orderModel->find($id);

        if (!$order) {
            View::json(['error' => 'Commande introuvable'], 404);
        }

        // Vérifier les permissions
        $user = Auth::user();
        if ($user['role_name'] === 'client' && $order['client_id'] != $user['client_id']) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $orderEventModel = new OrderEvent();
        $events = $orderEventModel->getByOrder($id);

        View::json(['events' => $events]);
    }

    /**
     * Générer un accusé de réception (AR)
     */
    public function generateAcknowledgment($id)
    {
        if (!Auth::can('manage_orders')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/orders');
        }

        $order = $this->orderModel->find($id);

        if (!$order) {
            Session::flash('error', 'Commande introuvable');
            View::redirect('/orders');
        }

        try {
            // TODO: Implémenter la génération PDF avec TCPDF ou similaire
            // Pour l'instant, on retourne un message
            Session::flash('info', 'Génération d\'AR à implémenter');
            View::redirect('/orders/' . $id);

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la génération : ' . $e->getMessage());
            View::redirect('/orders/' . $id);
        }
    }

    /**
     * Upload d'un rapport par un technicien
     */
    public function uploadReport($orderId)
    {
        $user = Auth::user();

        // Seuls les techniciens et admins peuvent uploader
        if (!in_array($user['role_name'], ['technicien', 'admin'])) {
            View::json(['error' => 'Accès refusé'], 403);
            return;
        }

        try {
            // Vérifier que la commande existe
            $order = $this->orderModel->find($orderId);
            if (!$order) {
                throw new \Exception('Commande introuvable');
            }

            // Vérifier l'upload
            if (!isset($_FILES['report_file']) || $_FILES['report_file']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Fichier requis');
            }

            $file = $_FILES['report_file'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($extension !== 'pdf') {
                throw new \Exception('Seuls les fichiers PDF sont acceptés');
            }

            // Upload du fichier
            $uploadDir = ROOT_PATH . '/public/uploads/reports/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'RAPPORT_' . $order['order_number'] . '_' . time() . '.pdf';
            $filepath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new \Exception('Erreur lors de l\'upload');
            }

            // Enregistrer dans la base
            $reportModel = new \Models\OrderReport();
            $reportId = $reportModel->createReport([
                'order_id' => $orderId,
                'diagnostic_type_id' => $_POST['diagnostic_type_id'] ?? null,
                'report_file' => '/uploads/reports/' . $filename,
                'original_filename' => $file['name'],
                'uploaded_by' => Auth::id(),
                'file_size' => $file['size'],
                'notes' => $_POST['notes'] ?? null
            ]);

            // Notifier le client par email
            try {
                $clientModel = new Client();
                $client = $clientModel->find($order['client_id']);

                $report = $reportModel->find($reportId);
                $report['diagnostic_type_name'] = '';
                if (!empty($_POST['diagnostic_type_id'])) {
                    $dtModel = new \Models\DiagnosticType();
                    $dt = $dtModel->find($_POST['diagnostic_type_id']);
                    $report['diagnostic_type_name'] = $dt['name'] ?? '';
                }

                $technician = Auth::user();

                \Helpers\Email::notifyReportUploaded($order, $client, $report, $technician);

                // Marquer comme notifié
                $reportModel->markClientNotified($reportId);
            } catch (\Exception $e) {
                error_log('Erreur notification rapport: ' . $e->getMessage());
            }

            Session::flash('success', 'Rapport uploadé et client notifié');
            View::redirect("/orders/{$orderId}");

        } catch (\Exception $e) {
            Session::flash('error', 'Erreur: ' . $e->getMessage());
            View::redirect("/orders/{$orderId}");
        }
    }
}
