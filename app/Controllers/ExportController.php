<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Models\Order;
use Models\Site;
use Models\Diagnostic;

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
