<?php

namespace Models;

use Core\Model;

class Report extends Model
{
    protected $table = 'reports';
    protected $timestamps = false;

    public function incrementDownload($reportId)
    {
        $this->query("UPDATE reports SET download_count = download_count + 1 WHERE id = ?", [$reportId]);
    }

    /**
     * Récupère tous les rapports avec détails
     */
    public function getAll($limit = 50)
    {
        $sql = "SELECT r.*, o.order_number, c.organization_name as client_name,
                u.first_name as uploader_first_name, u.last_name as uploader_last_name
                FROM reports r
                LEFT JOIN orders o ON r.order_id = o.id
                LEFT JOIN clients c ON o.client_id = c.id
                LEFT JOIN users u ON r.uploaded_by = u.id
                ORDER BY r.uploaded_at DESC
                LIMIT ?";
        return $this->query($sql, [$limit]);
    }

    /**
     * Récupère les rapports d'un client
     */
    public function getByClient($clientId, $limit = 50)
    {
        $sql = "SELECT r.*, o.order_number,
                u.first_name as uploader_first_name, u.last_name as uploader_last_name
                FROM reports r
                LEFT JOIN orders o ON r.order_id = o.id
                LEFT JOIN users u ON r.uploaded_by = u.id
                WHERE o.client_id = ?
                ORDER BY r.uploaded_at DESC
                LIMIT ?";
        return $this->query($sql, [$clientId, $limit]);
    }

    /**
     * Récupère les rapports pour une commande spécifique
     */
    public function getByOrderId($orderId)
    {
        $sql = "SELECT r.*,
                       u.first_name as uploader_first_name,
                       u.last_name as uploader_last_name
                FROM reports r
                LEFT JOIN users u ON r.uploaded_by = u.id
                WHERE r.order_id = ?
                ORDER BY r.uploaded_at DESC";

        return $this->query($sql, [$orderId]);
    }

    /**
     * Recherche de rapports
     */
    public function search($searchTerm)
    {
        $searchPattern = '%' . $searchTerm . '%';

        $sql = "SELECT r.*,
                       o.order_number,
                       c.organization_name as client_name,
                       u.first_name as uploader_first_name,
                       u.last_name as uploader_last_name
                FROM reports r
                LEFT JOIN orders o ON r.order_id = o.id
                LEFT JOIN clients c ON o.client_id = c.id
                LEFT JOIN users u ON r.uploaded_by = u.id
                WHERE r.filename LIKE ?
                   OR r.original_filename LIKE ?
                   OR o.order_number LIKE ?
                   OR c.organization_name LIKE ?
                ORDER BY r.uploaded_at DESC";

        return $this->query($sql, [$searchPattern, $searchPattern, $searchPattern, $searchPattern]);
    }

    /**
     * Récupère les statistiques des rapports
     */
    public function getStats()
    {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(file_size) as total_size,
                    COUNT(DISTINCT order_id) as orders_with_reports
                FROM reports";

        $result = $this->query($sql);
        return $result[0] ?? ['total' => 0, 'total_size' => 0, 'orders_with_reports' => 0];
    }
}
