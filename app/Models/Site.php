<?php

namespace Models;

use Core\Model;

class Site extends Model
{
    protected $table = 'sites';

    public function getClient()
    {
        return $this->queryOne("SELECT * FROM clients WHERE id = ?", [$this->client_id]);
    }

    public function getDiagnostics($filters = [])
    {
        $where = ["site_id = ?"];
        $params = [$this->id];

        if (!empty($filters['type'])) {
            $where[] = "diagnostic_type_id = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "date >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "date <= ?";
            $params[] = $filters['date_to'];
        }

        $sql = "SELECT d.*, dt.name as type_name, dt.code as type_code FROM diagnostics d
                LEFT JOIN diagnostic_types dt ON d.diagnostic_type_id = dt.id
                WHERE " . implode(' AND ', $where) . " ORDER BY d.date DESC";

        return $this->query($sql, $params);
    }

    public function getAllWithDiagnostics($clientId = null)
    {
        $where = $clientId ? "WHERE s.client_id = ?" : "";
        $params = $clientId ? [$clientId] : [];

        $sql = "SELECT s.*, c.organization_name as client_name,
                COUNT(DISTINCT d.id) as diagnostics_count,
                MAX(d.date) as last_diagnostic_date
                FROM sites s
                LEFT JOIN clients c ON s.client_id = c.id
                LEFT JOIN diagnostics d ON s.id = d.site_id
                {$where}
                GROUP BY s.id
                ORDER BY s.name";

        return $this->query($sql, $params);
    }

    /**
     * Récupère les sites d'un client
     */
    public function getByClient($clientId)
    {
        $sql = "SELECT s.*,
                COUNT(DISTINCT d.id) as diagnostics_count,
                MAX(d.date) as last_diagnostic_date
                FROM sites s
                LEFT JOIN diagnostics d ON s.id = d.site_id
                WHERE s.client_id = ?
                GROUP BY s.id
                ORDER BY s.name";

        return $this->query($sql, [$clientId]);
    }
}
