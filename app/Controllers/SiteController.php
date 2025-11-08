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

        // Filtrer par client si c'est un client connecté
        if ($user['role_name'] === 'client') {
            $sites = $this->siteModel->getByClient($user['client_id']);
            $clientModel = new Client();
            $client = $clientModel->find($user['client_id']);

            View::render('sites.index', [
                'sites' => $sites,
                'client' => $client,
                'isClientView' => true
            ]);
        } else {
            // Pour admin/bureau/techniciens : afficher tous les clients avec recherche
            $clientModel = new Client();
            $clients = $clientModel->getAll();

            // Si un client est sélectionné via paramètre GET
            $selectedClientId = $_GET['client_id'] ?? null;
            $sites = $selectedClientId
                ? $this->siteModel->getByClient($selectedClientId)
                : [];

            // Recherche d'adresse si fournie
            $searchAddress = $_GET['search'] ?? null;
            if ($searchAddress && $selectedClientId) {
                $sites = $this->siteModel->searchByAddress($selectedClientId, $searchAddress);
            }

            View::render('sites.index', [
                'sites' => $sites,
                'clients' => $clients,
                'selectedClientId' => $selectedClientId,
                'searchAddress' => $searchAddress,
                'isClientView' => false
            ]);
        }
    }

    /**
     * API: Autocomplete pour recherche de clients (min 3 caractères)
     */
    public function searchClients()
    {
        // Nettoyer les buffers
        while (ob_get_level()) {
            ob_end_clean();
        }

        $query = $_GET['q'] ?? '';

        if (strlen($query) < 3) {
            View::json(['clients' => []]);
            return;
        }

        $clientModel = new Client();
        $clients = $clientModel->search($query);

        View::json(['clients' => $clients]);
    }

    /**
     * API: Rechercher des sites par adresse pour un client
     */
    public function searchSites()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $clientId = $_GET['client_id'] ?? null;
        $query = $_GET['q'] ?? '';

        if (!$clientId) {
            View::json(['error' => 'client_id requis'], 400);
            return;
        }

        $sites = $this->siteModel->searchByAddress($clientId, $query);
        View::json(['sites' => $sites]);
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

    /**
     * Page d'import Excel du patrimoine
     */
    public function import()
    {
        if (!Auth::can('manage_sites')) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/sites');
        }

        $clientModel = new Client();
        $clients = $clientModel->getAll();

        View::render('sites.import', ['clients' => $clients]);
    }

    /**
     * Upload et prévisualisation du fichier Excel
     */
    public function uploadExcel()
    {
        if (!Auth::can('manage_sites')) {
            View::json(['error' => 'Accès refusé'], 403);
            return;
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $clientId = $_POST['client_id'] ?? null;

            if (!$clientId) {
                throw new \Exception('Client requis');
            }

            if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Fichier invalide');
            }

            $file = $_FILES['excel_file'];
            $allowedExtensions = ['xls', 'xlsx'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedExtensions)) {
                throw new \Exception('Format de fichier non supporté. Utilisez .xls ou .xlsx');
            }

            // Sauvegarder temporairement le fichier
            $uploadDir = ROOT_PATH . '/storage/uploads/temp/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'import_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new \Exception('Erreur lors de l\'upload du fichier');
            }

            // Lire les en-têtes et premières lignes avec PhpSpreadsheet
            require_once ROOT_PATH . '/libs/PhpSpreadsheet/autoload.php';

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filepath);
            $worksheet = $spreadsheet->getActiveSheet();
            $headers = [];
            $sampleRows = [];

            // Lire la première ligne (en-têtes)
            $headerRow = $worksheet->getRowIterator(1, 1)->current();
            foreach ($headerRow->getCellIterator() as $cell) {
                $headers[] = $cell->getValue();
            }

            // Lire les 5 premières lignes de données
            $rowIterator = $worksheet->getRowIterator(2, 6);
            foreach ($rowIterator as $row) {
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $sampleRows[] = $rowData;
            }

            View::json([
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'headers' => $headers,
                'sampleRows' => $sampleRows,
                'totalRows' => $worksheet->getHighestRow() - 1
            ]);

        } catch (\Exception $e) {
            View::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Traiter l'import Excel avec mapping des colonnes
     */
    public function processExcelImport()
    {
        if (!Auth::can('manage_sites')) {
            View::json(['error' => 'Accès refusé'], 403);
            return;
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $clientId = $_POST['client_id'] ?? null;
            $filepath = $_POST['filepath'] ?? null;
            $mapping = json_decode($_POST['mapping'] ?? '{}', true);

            if (!$clientId || !$filepath || !$mapping) {
                throw new \Exception('Paramètres manquants');
            }

            if (!file_exists($filepath)) {
                throw new \Exception('Fichier introuvable');
            }

            // Charger le fichier Excel
            require_once ROOT_PATH . '/libs/PhpSpreadsheet/autoload.php';
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filepath);
            $worksheet = $spreadsheet->getActiveSheet();

            $results = [
                'success' => 0,
                'errors' => [],
                'warnings' => []
            ];

            // Enregistrer l'import
            $importModel = new \Models\SiteImport();
            $importId = $importModel->create([
                'client_id' => $clientId,
                'filename' => basename($filepath),
                'filepath' => $filepath,
                'imported_by' => Auth::id(),
                'status' => 'processing',
                'column_mapping' => json_encode($mapping),
                'rows_total' => $worksheet->getHighestRow() - 1
            ]);

            // Traiter chaque ligne
            $rowNumber = 1;
            foreach ($worksheet->getRowIterator(2) as $row) {
                $rowNumber++;
                $rowData = [];

                foreach ($row->getCellIterator() as $cellIndex => $cell) {
                    $columnLetter = $cell->getColumn();
                    $rowData[$columnLetter] = $cell->getValue();
                }

                try {
                    // Mapper les données selon le mapping fourni
                    $siteData = [
                        'client_id' => $clientId
                    ];

                    foreach ($mapping as $dbField => $excelColumn) {
                        if ($excelColumn && isset($rowData[$excelColumn])) {
                            $siteData[$dbField] = $rowData[$excelColumn];
                        }
                    }

                    // Validation des champs obligatoires
                    $required = ['numero_groupe', 'numero_lot', 'address', 'city', 'postal_code'];
                    foreach ($required as $field) {
                        if (empty($siteData[$field])) {
                            throw new \Exception("Champ obligatoire manquant: $field");
                        }
                    }

                    // Créer ou mettre à jour le site
                    $existingSite = $this->siteModel->findByGroupAndLot(
                        $clientId,
                        $siteData['numero_groupe'],
                        $siteData['numero_lot']
                    );

                    if ($existingSite) {
                        $this->siteModel->update($existingSite['id'], $siteData);
                        $results['warnings'][] = "Ligne $rowNumber: Site mis à jour (groupe: {$siteData['numero_groupe']}, lot: {$siteData['numero_lot']})";
                    } else {
                        $this->siteModel->create($siteData);
                    }

                    $results['success']++;

                } catch (\Exception $e) {
                    $results['errors'][] = "Ligne $rowNumber: " . $e->getMessage();
                }
            }

            // Mettre à jour l'import
            $importModel->update($importId, [
                'status' => empty($results['errors']) ? 'completed' : 'completed',
                'rows_processed' => $rowNumber - 1,
                'rows_success' => $results['success'],
                'rows_errors' => count($results['errors']),
                'log_json' => json_encode($results),
                'completed_at' => date('Y-m-d H:i:s')
            ]);

            // Supprimer le fichier temporaire
            @unlink($filepath);

            View::json([
                'success' => true,
                'results' => $results,
                'importId' => $importId
            ]);

        } catch (\Exception $e) {
            View::json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
