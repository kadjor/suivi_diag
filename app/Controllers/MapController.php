<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Models\Site;
use Models\Diagnostic;

class MapController extends Controller
{
    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }
    }

    public function index()
    {
        View::render('map.index');
    }

    public function getSites()
    {
        $user = Auth::user();
        $siteModel = new Site();

        $filters = [
            'client_id' => $_GET['client_id'] ?? null,
            'diagnostic_type' => $_GET['diagnostic_type'] ?? null
        ];

        if ($user['role_name'] === 'client') {
            $filters['client_id'] = $user['client_id'];
        }

        $sites = $siteModel->getWithDiagnostics($filters);

        View::json(['sites' => $sites]);
    }

    public function getSiteDetails($id)
    {
        $siteModel = new Site();
        $diagnosticModel = new Diagnostic();

        $site = $siteModel->find($id);
        if (!$site) {
            View::json(['error' => 'Site introuvable'], 404);
        }

        $diagnostics = $diagnosticModel->getBySite($id);

        View::json([
            'site' => $site,
            'diagnostics' => $diagnostics
        ]);
    }
}
