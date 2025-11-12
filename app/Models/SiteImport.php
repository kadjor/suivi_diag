<?php

namespace Models;

use Core\Model;

class SiteImport extends Model
{
    protected $table = 'site_imports';

    // Désactiver les timestamps automatiques (created_at/updated_at)
    // car la table site_imports n'a pas ces colonnes dans le schema officiel
    protected $timestamps = false;

    /**
     * Crée un nouvel import
     */
    public function createImport($clientId, $filename, $filepath, $userId)
    {
        return $this->create([
            'client_id' => $clientId,
            'filename' => $filename,
            'filepath' => $filepath,
            'imported_by' => $userId,
            'status' => 'pending',
            'imported_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Marque l'import comme en cours de traitement
     */
    public function startProcessing($importId, $rowsTotal)
    {
        return $this->update($importId, [
            'status' => 'processing',
            'rows_total' => $rowsTotal,
            'rows_processed' => 0,
            'rows_success' => 0,
            'rows_errors' => 0
        ]);
    }

    /**
     * Met à jour la progression de l'import
     */
    public function updateProgress($importId, $rowsProcessed, $rowsSuccess, $rowsErrors, $logs = null)
    {
        $data = [
            'rows_processed' => $rowsProcessed,
            'rows_success' => $rowsSuccess,
            'rows_errors' => $rowsErrors
        ];

        if ($logs !== null) {
            $data['log_json'] = json_encode($logs);
        }

        return $this->update($importId, $data);
    }

    /**
     * Marque l'import comme terminé
     */
    public function completeImport($importId, $columnMapping = null)
    {
        $data = [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ];

        if ($columnMapping !== null) {
            $data['column_mapping'] = json_encode($columnMapping);
        }

        return $this->update($importId, $data);
    }

    /**
     * Marque l'import comme échoué
     */
    public function failImport($importId, $errorMessage)
    {
        return $this->update($importId, [
            'status' => 'failed',
            'log_json' => json_encode(['error' => $errorMessage]),
            'completed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Récupère les imports d'un client
     */
    public function getByClient($clientId, $limit = 10)
    {
        $sql = "SELECT si.*, u.first_name, u.last_name, c.organization_name
                FROM site_imports si
                LEFT JOIN users u ON si.imported_by = u.id
                LEFT JOIN clients c ON si.client_id = c.id
                WHERE si.client_id = ?
                ORDER BY si.imported_at DESC
                LIMIT ?";

        return $this->query($sql, [$clientId, $limit]);
    }

    /**
     * Récupère tous les imports avec détails
     */
    public function getAllWithDetails($limit = 50)
    {
        $sql = "SELECT si.*, u.first_name, u.last_name, c.organization_name
                FROM site_imports si
                LEFT JOIN users u ON si.imported_by = u.id
                LEFT JOIN clients c ON si.client_id = c.id
                ORDER BY si.imported_at DESC
                LIMIT ?";

        return $this->query($sql, [$limit]);
    }

    /**
     * Récupère un import avec tous ses détails
     */
    public function getWithDetails($importId)
    {
        $sql = "SELECT si.*, u.first_name, u.last_name, u.email,
                c.organization_name, c.id as client_id
                FROM site_imports si
                LEFT JOIN users u ON si.imported_by = u.id
                LEFT JOIN clients c ON si.client_id = c.id
                WHERE si.id = ?";

        return $this->queryOne($sql, [$importId]);
    }
}
