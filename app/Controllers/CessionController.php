<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Core\Session;
use Models\Cession;
use Models\CessionSite;
use Models\Client;
use Models\Site;
use Models\AuditLog;

/**
 * Contrôleur de gestion des cessions
 */
class CessionController extends Controller
{
    private $cessionModel;
    private $cessionSiteModel;

    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }

        $this->cessionModel = new Cession();
        $this->cessionSiteModel = new CessionSite();
    }

    /**
     * Liste des cessions
     */
    public function index()
    {
        if (!Auth::can('view_cessions')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/dashboard');
        }

        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? null;
        $user = Auth::user();

        // Si client, voir uniquement ses cessions
        if ($user['role_name'] === 'client') {
            $cessions = $this->cessionModel->getByClient($user['client_id'], 20, $page);
        } else {
            $cessions = $this->cessionModel->getAll($page, 20, $status);
        }

        // Statistiques
        $stats = [
            'draft' => $this->cessionModel->countByStatus('draft'),
            'pending_validation' => $this->cessionModel->countByStatus('pending_validation'),
            'validated' => $this->cessionModel->countByStatus('validated'),
            'in_progress' => $this->cessionModel->countByStatus('in_progress'),
            'completed' => $this->cessionModel->countByStatus('completed'),
            'cancelled' => $this->cessionModel->countByStatus('cancelled')
        ];

        View::render('cessions/index', [
            'cessions' => $cessions,
            'current_status' => $status,
            'stats' => $stats,
            'current_page' => $page
        ]);
    }

    /**
     * Détails d'une cession
     */
    public function show($id)
    {
        $cession = $this->cessionModel->getWithDetails($id);

        if (!$cession) {
            Session::flash('error', 'Cession introuvable');
            View::redirect('/cessions');
        }

        // Vérifier les permissions
        $user = Auth::user();
        if ($user['role_name'] === 'client') {
            if ($cession['from_client_id'] != $user['client_id'] &&
                $cession['to_client_id'] != $user['client_id']) {
                Session::flash('error', 'Accès refusé');
                View::redirect('/cessions');
            }
        }

        // Récupérer les sites
        $sites = $this->cessionSiteModel->getByCession($id);

        // Récupérer les statistiques
        $stats = $this->cessionSiteModel->getStats($id);

        // Récupérer la timeline
        $timeline = $this->cessionModel->getTimeline($id);

        // Récupérer les documents
        $documents = $this->cessionModel->getDocuments($id);

        View::render('cessions/show', [
            'cession' => $cession,
            'sites' => $sites,
            'stats' => $stats,
            'timeline' => $timeline,
            'documents' => $documents,
            'can_edit' => Auth::can('manage_cessions') && $this->cessionModel->canBeModified($id),
            'can_validate' => Auth::can('validate_cessions') && $this->cessionModel->canBeValidated($id),
            'can_cancel' => Auth::can('manage_cessions') && $this->cessionModel->canBeCancelled($id)
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        if (!Auth::can('create_cessions')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/cessions');
        }

        $clientModel = new Client();
        $clients = $clientModel->where(['active' => true], 'organization_name ASC');

        View::render('cessions/create', [
            'clients' => $clients
        ]);
    }

    /**
     * Enregistrement d'une nouvelle cession
     */
    public function store()
    {
        if (!Auth::can('create_cessions')) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $data = $this->getJsonInput();
        $user = Auth::user();

        // Validation
        $errors = $this->validateCessionData($data);
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'errors' => $errors], 400);
        }

        // Vérifier que les clients sont différents
        if ($data['from_client_id'] == $data['to_client_id']) {
            $this->jsonResponse(['success' => false, 'message' => 'Les clients cédant et cessionnaire doivent être différents'], 400);
        }

        // Créer la cession
        $cessionNumber = $this->cessionModel->generateCessionNumber();

        $cessionId = $this->cessionModel->create([
            'cession_number' => $cessionNumber,
            'from_client_id' => $data['from_client_id'],
            'to_client_id' => $data['to_client_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'effective_date' => $data['effective_date'] ?? null,
            'status' => 'draft',
            'created_by' => $user['id']
        ]);

        if (!$cessionId) {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de la création'], 500);
        }

        // Ajouter un événement
        $this->cessionModel->addEvent($cessionId, 'created', $user['id'], [
            'from_client_id' => $data['from_client_id'],
            'to_client_id' => $data['to_client_id']
        ]);

        // Audit log
        AuditLog::log('create', 'cession', $cessionId, ['cession_number' => $cessionNumber]);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Cession créée avec succès',
            'cession_id' => $cessionId,
            'cession_number' => $cessionNumber
        ]);
    }

    /**
     * Ajouter un site à une cession
     */
    public function addSite($id)
    {
        if (!Auth::can('manage_cessions')) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $data = $this->getJsonInput();
        $siteId = $data['site_id'] ?? null;

        if (!$siteId) {
            $this->jsonResponse(['success' => false, 'message' => 'Site non spécifié'], 400);
        }

        // Vérifier si le site peut être ajouté
        $canAdd = $this->cessionSiteModel->canAddSite($id, $siteId);
        if (!$canAdd['success']) {
            $this->jsonResponse($canAdd, 400);
        }

        // Ajouter le site
        $result = $this->cessionSiteModel->addSite($id, $siteId);

        if (!$result) {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors de l\'ajout du site'], 500);
        }

        // Ajouter un événement
        $user = Auth::user();
        $this->cessionModel->addEvent($id, 'site_added', $user['id'], ['site_id' => $siteId]);

        $this->jsonResponse(['success' => true, 'message' => 'Site ajouté avec succès']);
    }

    /**
     * Retirer un site d'une cession
     */
    public function removeSite($id)
    {
        if (!Auth::can('manage_cessions')) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $data = $this->getJsonInput();
        $siteId = $data['site_id'] ?? null;

        if (!$siteId) {
            $this->jsonResponse(['success' => false, 'message' => 'Site non spécifié'], 400);
        }

        // Retirer le site
        $result = $this->cessionSiteModel->removeSite($id, $siteId);

        if (!$result) {
            $this->jsonResponse(['success' => false, 'message' => 'Erreur lors du retrait du site'], 500);
        }

        // Ajouter un événement
        $user = Auth::user();
        $this->cessionModel->addEvent($id, 'site_removed', $user['id'], ['site_id' => $siteId]);

        $this->jsonResponse(['success' => true, 'message' => 'Site retiré avec succès']);
    }

    /**
     * Soumettre pour validation
     */
    public function submit($id)
    {
        if (!Auth::can('manage_cessions')) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $cession = $this->cessionModel->findById($id);
        if (!$cession) {
            $this->jsonResponse(['success' => false, 'message' => 'Cession introuvable'], 404);
        }

        if ($cession['status'] !== 'draft') {
            $this->jsonResponse(['success' => false, 'message' => 'La cession ne peut plus être soumise'], 400);
        }

        if ($cession['total_sites'] == 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Aucun site ajouté à la cession'], 400);
        }

        // Changer le statut
        $user = Auth::user();
        $this->cessionModel->updateStatus($id, 'pending_validation', $user['id']);

        $this->jsonResponse(['success' => true, 'message' => 'Cession soumise pour validation']);
    }

    /**
     * Valider une cession
     */
    public function validate($id)
    {
        if (!Auth::can('validate_cessions')) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $cession = $this->cessionModel->findById($id);
        if (!$cession) {
            $this->jsonResponse(['success' => false, 'message' => 'Cession introuvable'], 404);
        }

        if (!$this->cessionModel->canBeValidated($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'La cession ne peut pas être validée'], 400);
        }

        // Changer le statut
        $user = Auth::user();
        $this->cessionModel->updateStatus($id, 'validated', $user['id']);

        // Audit log
        AuditLog::log('validate', 'cession', $id);

        $this->jsonResponse(['success' => true, 'message' => 'Cession validée avec succès']);
    }

    /**
     * Démarrer le transfert des sites
     */
    public function startTransfer($id)
    {
        if (!Auth::can('manage_cessions')) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $cession = $this->cessionModel->findById($id);
        if (!$cession) {
            $this->jsonResponse(['success' => false, 'message' => 'Cession introuvable'], 404);
        }

        if ($cession['status'] !== 'validated') {
            $this->jsonResponse(['success' => false, 'message' => 'La cession doit être validée'], 400);
        }

        // Changer le statut
        $user = Auth::user();
        $this->cessionModel->updateStatus($id, 'in_progress', $user['id']);

        // Transférer tous les sites
        $this->cessionModel->transferAllSites($id, $user['id']);

        $this->jsonResponse(['success' => true, 'message' => 'Transfert démarré']);
    }

    /**
     * Annuler une cession
     */
    public function cancel($id)
    {
        if (!Auth::can('manage_cessions')) {
            $this->jsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        $data = $this->getJsonInput();
        $reason = $data['cancellation_reason'] ?? null;

        if (!$this->cessionModel->canBeCancelled($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'La cession ne peut plus être annulée'], 400);
        }

        // Changer le statut
        $user = Auth::user();
        $this->cessionModel->updateStatus($id, 'cancelled', $user['id'], [
            'cancellation_reason' => $reason
        ]);

        // Audit log
        AuditLog::log('cancel', 'cession', $id, ['reason' => $reason]);

        $this->jsonResponse(['success' => true, 'message' => 'Cession annulée']);
    }

    /**
     * API: Récupérer les sites d'un client
     */
    public function getSitesByClient()
    {
        $clientId = $_GET['client_id'] ?? null;

        if (!$clientId) {
            $this->jsonResponse(['success' => false, 'message' => 'Client non spécifié'], 400);
        }

        $siteModel = new Site();
        $sites = $siteModel->where(['client_id' => $clientId], 'name ASC');

        $this->jsonResponse(['success' => true, 'sites' => $sites]);
    }

    /**
     * Validation des données de cession
     */
    private function validateCessionData($data)
    {
        $errors = [];

        if (empty($data['from_client_id'])) {
            $errors['from_client_id'] = 'Le client cédant est requis';
        }

        if (empty($data['to_client_id'])) {
            $errors['to_client_id'] = 'Le client cessionnaire est requis';
        }

        if (empty($data['title'])) {
            $errors['title'] = 'Le titre est requis';
        } elseif (strlen($data['title']) < 5) {
            $errors['title'] = 'Le titre doit contenir au moins 5 caractères';
        }

        return $errors;
    }

    /**
     * Réponse JSON
     */
    private function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Récupère les données JSON de la requête
     */
    private function getJsonInput()
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
}
