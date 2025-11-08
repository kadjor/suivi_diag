<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Core\Session;
use Models\Site;
use Models\Client;
use Helpers\Validator;

class SiteController extends Controller
{
    private $siteModel;

    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }
        $this->siteModel = new Site();
    }

    public function index()
    {
        $user = Auth::user();
        $sites = $user['role_name'] === 'client'
            ? $this->siteModel->getByClient($user['client_id'])
            : $this->siteModel->getAll();

        View::render('sites.index', ['sites' => $sites]);
    }

    public function show($id)
    {
        $site = $this->siteModel->find($id);
        if (!$site) {
            Session::flash('error', 'Site introuvable');
            View::redirect('/sites');
        }

        $diagnosticModel = new \Models\Diagnostic();
        $diagnostics = $diagnosticModel->getBySite($id);

        // Récupérer les commandes pour ce site via le client
        $orderModel = new \Models\Order();
        $orders = $orderModel->query(
            "SELECT o.*, st.label as status_label, st.color as status_color
             FROM orders o
             LEFT JOIN statuses st ON o.status_id = st.id
             WHERE o.client_id = ?
             ORDER BY o.created_at DESC",
            [$site['client_id']]
        );

        // Récupérer les rapports pour ce site
        $reportModel = new \Models\Report();
        $reports = $reportModel->query(
            "SELECT r.*, o.order_number, u.first_name, u.last_name
             FROM reports r
             LEFT JOIN orders o ON r.order_id = o.id
             LEFT JOIN users u ON r.uploaded_by = u.id
             WHERE o.client_id = ?
             ORDER BY r.uploaded_at DESC",
            [$site['client_id']]
        );

        View::render('sites.show', [
            'site' => $site,
            'diagnostics' => $diagnostics,
            'orders' => $orders,
            'reports' => $reports
        ]);
    }

    public function create()
    {
        if (!Auth::can('manage_sites')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/sites');
        }

        $clientModel = new Client();
        View::render('sites.create', ['clients' => $clientModel->getAll()]);
    }

    public function store()
    {
        if (!Auth::can('manage_sites')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $data = $_POST;
        $validator = new Validator($data);
        $validator->required(['name', 'address', 'client_id']);

        if (!$validator->validate()) {
            Session::flash('error', implode(', ', $validator->getErrors()));
            View::redirect('/sites/create');
        }

        try {
            $siteId = $this->siteModel->create($data);
            Session::flash('success', 'Site créé avec succès');
            View::redirect("/sites/{$siteId}");
        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la création du site');
            View::redirect('/sites/create');
        }
    }

    public function edit($id)
    {
        if (!Auth::can('manage_sites')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/sites');
        }

        $site = $this->siteModel->find($id);
        if (!$site) {
            Session::flash('error', 'Site introuvable');
            View::redirect('/sites');
        }

        $clientModel = new Client();
        View::render('sites.edit', [
            'site' => $site,
            'clients' => $clientModel->getAll()
        ]);
    }

    public function update($id)
    {
        if (!Auth::can('manage_sites')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/sites');
        }

        $site = $this->siteModel->find($id);
        if (!$site) {
            Session::flash('error', 'Site introuvable');
            View::redirect('/sites');
        }

        $data = $_POST;
        $validator = new Validator($data, [
            'name' => 'required',
            'address' => 'required',
            'client_id' => 'required'
        ]);

        if ($validator->fails()) {
            Session::flash('error', implode(', ', $validator->allErrors()));
            View::redirect('/sites/' . $id . '/edit');
        }

        try {
            $this->siteModel->update($id, $data);
            Session::flash('success', 'Site mis à jour avec succès');
            View::redirect('/sites/' . $id);
        } catch (\Exception $e) {
            Session::flash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
            View::redirect('/sites/' . $id . '/edit');
        }
    }
}
