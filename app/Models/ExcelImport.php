<?php

namespace Models;

use Core\Model;

/**
 * Gestionnaire d'import Excel pour la cartographie
 *
 * Permet d'importer des fichiers Excel contenant :
 * - Sites (adresses, coordonnées GPS)
 * - Diagnostics (types, dates, résultats)
 */
class ExcelImport extends Model
{
    protected $table = 'excel_imports';

    /**
     * Valide et importe un fichier Excel
     *
     * @param string $filePath Chemin du fichier Excel
     * @param int $userId ID de l'utilisateur qui importe
     * @param int $clientId ID du client concerné
     * @return array Résultat de l'import
     */
    public function importFile($filePath, $userId, $clientId)
    {
        $results = [
            'success' => false,
            'sites_created' => 0,
            'sites_updated' => 0,
            'diagnostics_created' => 0,
            'errors' => []
        ];

        try {
            // Vérifier que PhpSpreadsheet est disponible
            if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                throw new \Exception('PhpSpreadsheet library not found');
            }

            // Charger le fichier
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Vérifier l'en-tête
            if (!$this->validateHeaders($rows[0])) {
                throw new \Exception('Invalid Excel headers. Expected: Nom Site, Adresse, Code Postal, Ville, Latitude, Longitude, Type Diagnostic, Date Diagnostic, Résultat');
            }

            // Traiter chaque ligne (sauf l'en-tête)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                try {
                    // Extraire les données
                    $siteData = [
                        'name' => trim($row[0] ?? ''),
                        'address' => trim($row[1] ?? ''),
                        'postal_code' => trim($row[2] ?? ''),
                        'city' => trim($row[3] ?? ''),
                        'latitude' => $this->parseCoordinate($row[4] ?? null),
                        'longitude' => $this->parseCoordinate($row[5] ?? null),
                        'client_id' => $clientId
                    ];

                    $diagnosticData = [
                        'type' => strtolower(trim($row[6] ?? '')),
                        'date' => $this->parseDate($row[7] ?? null),
                        'result' => trim($row[8] ?? '')
                    ];

                    // Valider les données minimales
                    if (empty($siteData['name']) || empty($siteData['address'])) {
                        $results['errors'][] = "Ligne " . ($i + 1) . ": Nom et adresse requis";
                        continue;
                    }

                    // Chercher ou créer le site
                    $site = $this->findOrCreateSite($siteData);

                    if ($site['created']) {
                        $results['sites_created']++;
                    } else {
                        $results['sites_updated']++;
                    }

                    // Créer le diagnostic si les données sont présentes
                    if (!empty($diagnosticData['type']) && !empty($diagnosticData['date'])) {
                        $diagnostic = $this->createDiagnostic($site['id'], $diagnosticData);
                        if ($diagnostic) {
                            $results['diagnostics_created']++;
                        }
                    }

                } catch (\Exception $e) {
                    $results['errors'][] = "Ligne " . ($i + 1) . ": " . $e->getMessage();
                }
            }

            // Enregistrer l'import dans l'historique
            $this->logImport($userId, $clientId, $filePath, $results);

            $results['success'] = true;

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Valide les en-têtes du fichier Excel
     */
    private function validateHeaders($headers)
    {
        $expectedHeaders = [
            'Nom Site',
            'Adresse',
            'Code Postal',
            'Ville',
            'Latitude',
            'Longitude',
            'Type Diagnostic',
            'Date Diagnostic',
            'Résultat'
        ];

        if (count($headers) < count($expectedHeaders)) {
            return false;
        }

        foreach ($expectedHeaders as $index => $expected) {
            if (trim($headers[$index] ?? '') !== $expected) {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse une coordonnée GPS
     */
    private function parseCoordinate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $coordinate = floatval($value);

        // Validation basique
        if ($coordinate < -180 || $coordinate > 180) {
            return null;
        }

        return $coordinate;
    }

    /**
     * Parse une date
     */
    private function parseDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Si c'est un numéro de série Excel
        if (is_numeric($value)) {
            $timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value);
            return date('Y-m-d', $timestamp);
        }

        // Sinon essayer de parser la date
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    /**
     * Trouve ou crée un site
     */
    private function findOrCreateSite($data)
    {
        $siteModel = new Site();

        // Chercher un site existant avec la même adresse
        $existing = $siteModel->findByAddress(
            $data['address'],
            $data['client_id']
        );

        if ($existing) {
            // Mettre à jour les coordonnées si elles sont fournies
            if ($data['latitude'] && $data['longitude']) {
                $siteModel->update($existing['id'], [
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude']
                ]);
            }

            return [
                'id' => $existing['id'],
                'created' => false
            ];
        }

        // Créer un nouveau site
        $siteId = $siteModel->create($data);

        return [
            'id' => $siteId,
            'created' => true
        ];
    }

    /**
     * Crée un diagnostic
     */
    private function createDiagnostic($siteId, $data)
    {
        $diagnosticModel = new Diagnostic();

        return $diagnosticModel->create([
            'site_id' => $siteId,
            'type' => $data['type'],
            'diagnostic_date' => $data['date'],
            'result' => $data['result'],
            'status' => 'completed'
        ]);
    }

    /**
     * Enregistre l'import dans l'historique
     */
    private function logImport($userId, $clientId, $filePath, $results)
    {
        return $this->create([
            'user_id' => $userId,
            'client_id' => $clientId,
            'filename' => basename($filePath),
            'sites_created' => $results['sites_created'],
            'sites_updated' => $results['sites_updated'],
            'diagnostics_created' => $results['diagnostics_created'],
            'errors_count' => count($results['errors']),
            'errors' => json_encode($results['errors']),
            'imported_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Récupère l'historique des imports pour un client
     */
    public function getImportHistory($clientId, $limit = 10)
    {
        $query = "
            SELECT ei.*, u.username, u.first_name, u.last_name
            FROM {$this->table} ei
            LEFT JOIN users u ON ei.user_id = u.id
            WHERE ei.client_id = :client_id
            ORDER BY ei.imported_at DESC
            LIMIT :limit
        ";

        return $this->db->select($query, [
            'client_id' => $clientId,
            'limit' => $limit
        ]);
    }

    /**
     * Génère un fichier Excel modèle
     */
    public static function generateTemplate($outputPath)
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            throw new \Exception('PhpSpreadsheet library not found');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $headers = [
            'Nom Site',
            'Adresse',
            'Code Postal',
            'Ville',
            'Latitude',
            'Longitude',
            'Type Diagnostic',
            'Date Diagnostic',
            'Résultat'
        ];

        $sheet->fromArray([$headers], null, 'A1');

        // Exemple de données
        $exampleData = [
            'Site Exemple 1',
            '123 Rue de la République',
            '75001',
            'Paris',
            '48.8566',
            '2.3522',
            'amiante',
            date('Y-m-d'),
            'Négatif'
        ];

        $sheet->fromArray([$exampleData], null, 'A2');

        // Styles
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0E0E0']
            ]
        ];

        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

        // Largeurs de colonnes
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);

        // Sauvegarder
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($outputPath);

        return $outputPath;
    }
}
