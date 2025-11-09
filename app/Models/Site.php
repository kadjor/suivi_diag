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

    /**
     * Récupère tous les sites avec stats
     */
    public function getAll()
    {
        return $this->getAllWithDiagnostics();
    }

    /**
     * Récupère les sites avec diagnostics selon des filtres
     */
    public function getWithDiagnostics($filters = [])
    {
        $where = [];
        $params = [];

        if (!empty($filters['client_id'])) {
            $where[] = "s.client_id = ?";
            $params[] = $filters['client_id'];
        }

        if (!empty($filters['diagnostic_type'])) {
            $where[] = "d.diagnostic_type_id = ?";
            $params[] = $filters['diagnostic_type'];
        }

        $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

        $sql = "SELECT s.*, c.organization_name as client_name,
                s.latitude, s.longitude,
                COUNT(DISTINCT d.id) as diagnostics_count,
                MAX(d.date) as last_diagnostic_date
                FROM sites s
                LEFT JOIN clients c ON s.client_id = c.id
                LEFT JOIN diagnostics d ON s.id = d.site_id
                {$whereClause}
                GROUP BY s.id
                HAVING s.latitude IS NOT NULL AND s.longitude IS NOT NULL
                ORDER BY s.name";

        return $this->query($sql, $params);
    }

    /**
     * Recherche de sites par adresse (autocomplete)
     */
    public function searchByAddress($query, $clientId = null)
    {
        $where = ["(s.address LIKE ? OR s.city LIKE ? OR s.postal_code LIKE ?)"];
        $searchParam = "%{$query}%";
        $params = [$searchParam, $searchParam, $searchParam];

        if ($clientId !== null) {
            $where[] = "s.client_id = ?";
            $params[] = $clientId;
        }

        $sql = "SELECT s.*, c.organization_name as client_name
                FROM sites s
                LEFT JOIN clients c ON s.client_id = c.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY s.address
                LIMIT 20";

        return $this->query($sql, $params);
    }

    /**
     * Trouve un site par numéro de groupe et numéro de lot
     */
    public function findByGroupAndLot($numeroGroupe, $numeroLot, $clientId)
    {
        $sql = "SELECT * FROM sites
                WHERE numero_groupe = ?
                AND numero_lot = ?
                AND client_id = ?
                LIMIT 1";

        return $this->queryOne($sql, [$numeroGroupe, $numeroLot, $clientId]);
    }

    /**
     * Crée ou met à jour un site depuis import Excel
     */
    public function createOrUpdateFromImport($clientId, $data)
    {
        // Vérifier si le site existe déjà
        $existing = $this->findByGroupAndLot(
            $data['numero_groupe'],
            $data['numero_lot'],
            $clientId
        );

        $siteData = [
            'client_id' => $clientId,
            'numero_groupe' => $data['numero_groupe'],
            'numero_lot' => $data['numero_lot'],
            'nom_groupe' => $data['nom_groupe'] ?? null,
            'address' => $data['address'],
            'city' => $data['city'],
            'postal_code' => $data['postal_code'],
            'numero_porte' => $data['numero_porte'] ?? null,
            'niveau' => $data['niveau'] ?? null,
            'identifiant_fiscal' => $data['identifiant_fiscal'] ?? null,
            'nommage_rapport' => $data['nommage_rapport'] ?? null,
            'numero_batiment' => $data['numero_batiment'] ?? null,
            'numero_entree' => $data['numero_entree'] ?? null,
            'numero_batiment_brgm' => $data['numero_batiment_brgm'] ?? null,
            'cadastre' => $data['cadastre'] ?? null,
            'numero_gardien' => $data['numero_gardien'] ?? null,
        ];

        if ($existing) {
            // Mettre à jour
            $this->update($existing['id'], $siteData);
            return $existing['id'];
        } else {
            // Créer - Ajouter un nom par défaut
            $siteData['name'] = trim(
                ($data['nommage_rapport'] ?? '') . ' - ' .
                ($data['address'] ?? '') . ' - Lot ' .
                ($data['numero_lot'] ?? '')
            );
            return $this->create($siteData);
        }
    }

    /**
     * Récupère les sites du patrimoine avec filtres avancés
     */
    public function getPatrimoine($clientId, $filters = [])
    {
        $where = ["s.client_id = ?"];
        $params = [$clientId];

        if (!empty($filters['search'])) {
            $where[] = "(s.address LIKE ? OR s.numero_lot LIKE ? OR s.numero_groupe LIKE ?)";
            $search = "%{$filters['search']}%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['numero_groupe'])) {
            $where[] = "s.numero_groupe = ?";
            $params[] = $filters['numero_groupe'];
        }

        $sql = "SELECT s.*,
                COUNT(DISTINCT o.id) as orders_count,
                MAX(o.created_at) as last_order_date
                FROM sites s
                LEFT JOIN orders o ON s.id = o.site_id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY s.id
                ORDER BY s.numero_groupe, s.numero_lot";

        return $this->query($sql, $params);
    }
}

