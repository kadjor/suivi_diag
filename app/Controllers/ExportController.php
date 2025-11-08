<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Models\Order;
use Models\Site;
use Models\Diagnostic;
use Models\Intervention;

class ExportController extends Controller
{
    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }
    }

    public function orders()
    {
        if (!Auth::can('export_data')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $format = $_GET['format'] ?? 'xlsx';
        $orderModel = new Order();
        $orders = $orderModel->getAll();

        if ($format === 'xlsx') {
            $this->exportToExcel($orders, 'commandes');
        } elseif ($format === 'csv') {
            $this->exportToCsv($orders, 'commandes');
        } else {
            View::json(['error' => 'Format invalide'], 400);
        }
    }

    public function sites()
    {
        if (!Auth::can('export_data')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $format = $_GET['format'] ?? 'xlsx';
        $siteModel = new Site();
        $sites = $siteModel->getAll();

        if ($format === 'xlsx') {
            $this->exportToExcel($sites, 'sites');
        } else {
            $this->exportToCsv($sites, 'sites');
        }
    }

    public function interventions()
    {
        if (!Auth::can('export_data')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $format = $_GET['format'] ?? 'xlsx';
        $interventionModel = new Intervention();
        $interventions = $interventionModel->query(
            "SELECT i.*, o.order_number, s.name as site_name,
                    u.first_name as tech_first_name, u.last_name as tech_last_name
             FROM interventions i
             LEFT JOIN orders o ON i.order_id = o.id
             LEFT JOIN sites s ON i.site_id = s.id
             LEFT JOIN users u ON i.technician_id = u.id
             ORDER BY i.scheduled_date DESC"
        );

        if ($format === 'xlsx') {
            $this->exportToExcel($interventions, 'interventions');
        } else {
            $this->exportToCsv($interventions, 'interventions');
        }
    }

    public function diagnostics()
    {
        if (!Auth::can('export_data')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $format = $_GET['format'] ?? 'xlsx';
        $diagnosticModel = new Diagnostic();
        $diagnostics = $diagnosticModel->query(
            "SELECT d.*, s.name as site_name, s.address as site_address,
                    c.organization_name as client_name
             FROM diagnostics d
             LEFT JOIN sites s ON d.site_id = s.id
             LEFT JOIN clients c ON s.client_id = c.id
             ORDER BY d.date DESC"
        );

        if ($format === 'xlsx') {
            $this->exportToExcel($diagnostics, 'diagnostics');
        } else {
            $this->exportToCsv($diagnostics, 'diagnostics');
        }
    }

    private function exportToExcel($data, $filename)
    {
        // TODO: Implémenter avec PhpSpreadsheet
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}.xlsx\"");
        echo "Export Excel - TODO";
        exit;
    }

    private function exportToCsv($data, $filename)
    {
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"{$filename}.csv\"");

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }
}
