<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Core\Session;
use Models\Report;
use Models\Order;
use Helpers\FileUpload;

class ReportController extends Controller
{
    private $reportModel;

    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }
        $this->reportModel = new Report();
    }

    public function index()
    {
        $user = Auth::user();
        $searchTerm = $_GET['search'] ?? '';

        if ($searchTerm) {
            $reports = $this->reportModel->search($searchTerm);
        } else {
            $reports = $user['role_name'] === 'client'
                ? $this->reportModel->getByClient($user['client_id'])
                : $this->reportModel->getAll();
        }

        $stats = $this->reportModel->getStats();

        View::render('reports.index', [
            'reports' => $reports,
            'stats' => $stats,
            'searchTerm' => $searchTerm
        ]);
    }

    public function upload()
    {
        if (!Auth::can('upload_reports')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $orderId = $_POST['order_id'] ?? null;
        $type = $_POST['type'] ?? 'diagnostic';

        if (!$orderId) {
            View::json(['error' => 'Commande requise'], 400);
        }

        try {
            $fileUpload = new FileUpload();
            $uploadedFile = $fileUpload->upload($_FILES['file'], 'reports');

            $reportId = $this->reportModel->create([
                'order_id' => $orderId,
                'type' => $type,
                'filename' => $uploadedFile['filename'],
                'file_path' => $uploadedFile['path'],
                'file_size' => $uploadedFile['size'],
                'uploaded_by' => Auth::id()
            ]);

            Session::flash('success', 'Rapport téléversé avec succès');
            View::json(['success' => true, 'report_id' => $reportId]);

        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }

    public function download($id)
    {
        $report = $this->reportModel->find($id);

        if (!$report) {
            Session::flash('error', 'Rapport introuvable');
            View::redirect('/reports');
        }

        // Vérifier les permissions
        $user = Auth::user();
        $orderModel = new Order();
        $order = $orderModel->find($report['order_id']);

        if ($user['role_name'] === 'client' && $order['client_id'] != $user['client_id']) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/reports');
        }

        $filePath = __DIR__ . '/../../public/' . $report['file_path'];

        if (!file_exists($filePath)) {
            Session::flash('error', 'Fichier introuvable');
            View::redirect('/reports');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $report['filename'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Import de données depuis Excel
     */
    public function importExcel()
    {
        if (!Auth::can('manage_reports')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        if (!isset($_FILES['excel_file'])) {
            View::json(['error' => 'Fichier manquant'], 400);
        }

        $file = $_FILES['excel_file'];

        // Vérifier le type de fichier
        $allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        if (!in_array($file['type'], $allowedTypes)) {
            View::json(['error' => 'Format de fichier invalide'], 400);
        }

        try {
            // TODO: Implémenter le parsing Excel avec PhpSpreadsheet
            // Pour l'instant, retourner un message
            View::json([
                'success' => true,
                'message' => 'Import Excel à implémenter',
                'preview' => []
            ]);

        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Confirmer l'import de données
     */
    public function confirmImport()
    {
        if (!Auth::can('manage_reports')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        // TODO: Implémenter la confirmation d'import
        View::json([
            'success' => true,
            'message' => 'Confirmation d\'import à implémenter'
        ]);
    }
}
