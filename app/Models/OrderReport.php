<?php

namespace Models;

use Core\Model;

class OrderReport extends Model
{
    protected $table = 'order_reports';

    /**
     * Récupère tous les rapports d'une commande
     */
    public function getByOrder($orderId)
    {
        $sql = "SELECT or.*, dt.name as diagnostic_type_name, dt.code as diagnostic_type_code,
                u.first_name, u.last_name
                FROM order_reports or
                LEFT JOIN diagnostic_types dt ON or.diagnostic_type_id = dt.id
                LEFT JOIN users u ON or.uploaded_by = u.id
                WHERE or.order_id = ?
                ORDER BY or.uploaded_at DESC";

        return $this->query($sql, [$orderId]);
    }

    /**
     * Crée un rapport
     */
    public function createReport($data)
    {
        return $this->create([
            'order_id' => $data['order_id'],
            'diagnostic_type_id' => $data['diagnostic_type_id'] ?? null,
            'report_file' => $data['report_file'],
            'original_filename' => $data['original_filename'],
            'uploaded_by' => $data['uploaded_by'],
            'file_size' => $data['file_size'] ?? null,
            'notes' => $data['notes'] ?? null,
            'uploaded_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Marque le client comme notifié
     */
    public function markClientNotified($reportId)
    {
        return $this->update($reportId, [
            'client_notified' => true,
            'client_notified_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Récupère les rapports non notifiés
     */
    public function getPendingNotifications()
    {
        $sql = "SELECT or.*, o.order_number, o.client_id,
                c.organization_name, c.email as client_email
                FROM order_reports or
                JOIN orders o ON or.order_id = o.id
                JOIN clients c ON o.client_id = c.id
                WHERE or.client_notified = 0
                ORDER BY or.uploaded_at ASC";

        return $this->query($sql);
    }

    /**
     * Compte les rapports par commande
     */
    public function countByOrder($orderId)
    {
        $sql = "SELECT COUNT(*) as count FROM order_reports WHERE order_id = ?";
        $result = $this->queryOne($sql, [$orderId]);
        return $result['count'] ?? 0;
    }
}
