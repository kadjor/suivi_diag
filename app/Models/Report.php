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
}
