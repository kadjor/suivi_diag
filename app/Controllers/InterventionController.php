<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Core\Session;
use Models\Intervention;
use Models\Order;
use Models\OrderEvent;

/**
 * Contrôleur de gestion des interventions
 */
class InterventionController extends Controller
{
    private $interventionModel;

    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }

        $this->interventionModel = new Intervention();
    }

    /**
     * Liste des interventions
     */
    public function index()
    {
        $user = Auth::user();

        // Si technicien, voir seulement ses interventions
        if ($user['role_name'] === 'technicien') {
            $interventions = $this->interventionModel->getByTechnician($user['id']);
        } else {
            // Sinon, voir toutes les interventions
            $interventions = $this->interventionModel->query(
                "SELECT i.*, o.order_number, s.name as site_name, s.address as site_address,
                        u.first_name as tech_first_name, u.last_name as tech_last_name
                 FROM interventions i
                 LEFT JOIN orders o ON i.order_id = o.id
                 LEFT JOIN sites s ON i.site_id = s.id
                 LEFT JOIN users u ON i.technician_id = u.id
                 ORDER BY i.scheduled_date DESC"
            );
        }

        View::render('interventions.index', [
            'interventions' => $interventions
        ]);
    }

    /**
     * Créer une intervention
     */
    public function create()
    {
        if (!Auth::can('manage_interventions')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $data = $_POST;

        if (empty($data['order_id']) || empty($data['scheduled_date'])) {
            View::json(['error' => 'Données manquantes'], 400);
        }

        try {
            $interventionId = $this->interventionModel->createIntervention($data);

            // Créer un événement pour la commande
            $eventModel = new OrderEvent();
            $eventModel->create([
                'order_id' => $data['order_id'],
                'event_type' => 'intervention_scheduled',
                'user_id' => Auth::id(),
                'description' => "Intervention planifiée pour le " . date('d/m/Y', strtotime($data['scheduled_date']))
            ]);

            Session::flash('success', 'Intervention créée avec succès');
            View::json(['success' => true, 'intervention_id' => $interventionId]);

        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Mettre à jour le statut d'une intervention
     */
    public function updateStatus()
    {
        $interventionId = $_POST['intervention_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $notes = $_POST['notes'] ?? null;

        if (!$interventionId || !$status) {
            View::json(['error' => 'Données manquantes'], 400);
        }

        $intervention = $this->interventionModel->find($interventionId);

        if (!$intervention) {
            View::json(['error' => 'Intervention introuvable'], 404);
        }

        // Vérifier que le technicien est bien assigné ou que l'utilisateur a les permissions
        $user = Auth::user();
        if ($user['role_name'] === 'technicien' && $intervention['technician_id'] != $user['id']) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        try {
            $updateData = ['status' => $status];

            if ($notes) {
                $updateData['notes'] = $notes;
            }

            // Si statut terminé, ajouter la date de complétion
            if ($status === 'completed') {
                $updateData['completed_at'] = date('Y-m-d H:i:s');
            }

            $this->interventionModel->update($interventionId, $updateData);

            // Créer un événement pour la commande associée
            if ($intervention['order_id']) {
                $eventModel = new OrderEvent();
                $eventModel->create([
                    'order_id' => $intervention['order_id'],
                    'event_type' => 'intervention_status_updated',
                    'user_id' => Auth::id(),
                    'description' => "Statut intervention mis à jour: $status"
                ]);
            }

            View::json(['success' => true, 'message' => 'Statut mis à jour']);

        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }
}
