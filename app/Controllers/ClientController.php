<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Core\Session;
use Models\Client;
use Models\Site;
use Helpers\Validator;

/**
 * Contrôleur de gestion des clients
 */
class ClientController extends Controller
{
    private $clientModel;

    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }

        $this->clientModel = new Client();
    }

    /**
     * Liste des clients
     */
    public function index()
    {
        $user = Auth::user();

        // Seuls admin et secrétariat peuvent voir tous les clients
        if (!in_array($user['role_name'], ['admin', 'secretariat'])) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/dashboard');
        }

        $clients = $this->clientModel->getAll();

        View::render('clients.index', [
            'clients' => $clients
        ]);
    }

    /**
     * Détails d'un client
     */
    public function show($id)
    {
        $user = Auth::user();

        // Vérifier les permissions
        if (!in_array($user['role_name'], ['admin', 'secretariat'])) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/dashboard');
        }

        $client = $this->clientModel->find($id);

        if (!$client) {
            Session::flash('error', 'Client introuvable');
            View::redirect('/clients');
        }

        $siteModel = new Site();
        $sites = $siteModel->getByClient($id);

        View::render('clients.show', [
            'client' => $client,
            'sites' => $sites
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $user = Auth::user();

        if (!in_array($user['role_name'], ['admin', 'secretariat'])) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/clients');
        }

        View::render('clients.create');
    }

    /**
     * Enregistrement d'un nouveau client
     */
    public function store()
    {
        $user = Auth::user();

        if (!in_array($user['role_name'], ['admin', 'secretariat'])) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/clients');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::redirect('/clients/create');
        }

        $data = [
            'organization_name' => $_POST['organization_name'] ?? '',
            'contact_name' => $_POST['contact_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'siret' => $_POST['siret'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'active' => 1
        ];

        // Validation
        $validator = new Validator($data, [
            'organization_name' => 'required',
            'contact_name' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'city' => 'required',
            'postal_code' => 'required'
        ]);

        if ($validator->fails()) {
            Session::flash('error', implode(', ', $validator->allErrors()));
            View::redirect('/clients/create');
            return;
        }

        try {
            $clientId = $this->clientModel->create($data);
            Session::flash('success', 'Client créé avec succès');
            View::redirect('/clients/' . $clientId);
        } catch (\Exception $e) {
            log_message("Failed to create client: " . $e->getMessage(), 'error');
            Session::flash('error', 'Erreur lors de la création du client');
            View::redirect('/clients/create');
        }
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $user = Auth::user();

        if (!in_array($user['role_name'], ['admin', 'secretariat'])) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/clients');
        }

        $client = $this->clientModel->find($id);

        if (!$client) {
            Session::flash('error', 'Client introuvable');
            View::redirect('/clients');
        }

        View::render('clients.edit', ['client' => $client]);
    }

    /**
     * Mise à jour d'un client
     */
    public function update($id)
    {
        $user = Auth::user();

        if (!in_array($user['role_name'], ['admin', 'secretariat'])) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/clients');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            View::redirect('/clients/' . $id . '/edit');
        }

        $data = [
            'organization_name' => $_POST['organization_name'] ?? '',
            'contact_name' => $_POST['contact_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'siret' => $_POST['siret'] ?? '',
            'notes' => $_POST['notes'] ?? '',
            'active' => isset($_POST['active']) ? 1 : 0
        ];

        // Validation
        $validator = new Validator($data, [
            'organization_name' => 'required',
            'contact_name' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'city' => 'required',
            'postal_code' => 'required'
        ]);

        if ($validator->fails()) {
            Session::flash('error', implode(', ', $validator->allErrors()));
            View::redirect('/clients/' . $id . '/edit');
            return;
        }

        try {
            $this->clientModel->update($id, $data);
            Session::flash('success', 'Client mis à jour avec succès');
            View::redirect('/clients/' . $id);
        } catch (\Exception $e) {
            log_message("Failed to update client: " . $e->getMessage(), 'error');
            Session::flash('error', 'Erreur lors de la mise à jour');
            View::redirect('/clients/' . $id . '/edit');
        }
    }
}
