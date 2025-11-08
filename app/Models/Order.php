<?php

namespace Models;

use Core\Model;

class Order extends Model
{
    protected $table = 'orders';

    public function generateOrderNumber()
    {
        $date = date('Ymd');
        $count = $this->count(['order_number' => ['LIKE', "CMD-{$date}-%"]]) + 1;
        return sprintf('CMD-%s-%04d', $date, $count);
    }

    public function getWithDetails($id)
    {
        $sql = "SELECT o.*, c.organization_name as client_name, c.email as client_email,
                s.label as status_label, s.color as status_color,
                u1.first_name as creator_first_name, u1.last_name as creator_last_name,
                u2.first_name as technician_first_name, u2.last_name as technician_last_name
                FROM orders o
                LEFT JOIN clients c ON o.client_id = c.id
                LEFT JOIN statuses s ON o.status_id = s.id
                LEFT JOIN users u1 ON o.created_by = u1.id
                LEFT JOIN users u2 ON o.assigned_to = u2.id
                WHERE o.id = ?";
        return $this->queryOne($sql, [$id]);
    }

    public function getEvents($orderId)
    {
        $orderEventModel = new OrderEvent();
        return $orderEventModel->where(['order_id' => $orderId], 'timestamp ASC');
    }

    public function addEvent($orderId, $eventType, $userId, $data = null)
    {
        $orderEventModel = new OrderEvent();
        return $orderEventModel->create([
            'order_id' => $orderId,
            'event_type' => $eventType,
            'user_id' => $userId,
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data ? json_encode($data) : null,
            'ip_address' => getClientIp(),
            'is_system' => false
        ]);
    }

    public function getTimeline($orderId)
    {
        $sql = "SELECT oe.*, u.first_name, u.last_name, u.username
                FROM order_events oe
                LEFT JOIN users u ON oe.user_id = u.id
                WHERE oe.order_id = ?
                ORDER BY oe.timestamp DESC";
        return $this->query($sql, [$orderId]);
    }

    public function updateStatus($orderId, $statusCode, $userId)
    {
        // Récupérer l'ID du statut
        $status = $this->queryOne("SELECT id FROM statuses WHERE category = 'order' AND code = ?", [$statusCode]);
        if (!$status) return false;

        $this->update($orderId, ['status_id' => $status['id']]);
        $this->addEvent($orderId, $statusCode, $userId);
        return true;
    }

    /**
     * Récupère toutes les commandes avec pagination
     */
    public function getAll($page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT o.*, c.organization_name as client_name,
                s.label as status_label, s.color as status_color,
                u.first_name as technician_first_name, u.last_name as technician_last_name
                FROM orders o
                LEFT JOIN clients c ON o.client_id = c.id
                LEFT JOIN statuses s ON o.status_id = s.id
                LEFT JOIN users u ON o.assigned_to = u.id
                ORDER BY o.created_at DESC
                LIMIT ? OFFSET ?";
        return $this->query($sql, [$limit, $offset]);
    }

    /**
     * Compte les commandes par code de statut
     */
    public function countByStatus($statusCode)
    {
        $sql = "SELECT COUNT(*) as count FROM orders o
                JOIN statuses s ON o.status_id = s.id
                WHERE s.code = ? AND s.category = 'order'";
        $result = $this->queryOne($sql, [$statusCode]);
        return $result['count'] ?? 0;
    }

    /**
     * Récupère les commandes récentes
     */
    public function getRecent($limit = 10)
    {
        $sql = "SELECT o.*, c.organization_name as client_name,
                s.label as status_label, s.color as status_color
                FROM orders o
                LEFT JOIN clients c ON o.client_id = c.id
                LEFT JOIN statuses s ON o.status_id = s.id
                ORDER BY o.created_at DESC
                LIMIT ?";
        return $this->query($sql, [$limit]);
    }

    /**
     * Récupère les commandes par mois (statistiques)
     */
    public function getOrdersByMonth($months = 12)
    {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
                FROM orders
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC";
        return $this->query($sql, [$months]);
    }

    /**
     * Récupère les commandes par statut
     */
    public function getByStatus($statusCode, $limit = 10)
    {
        $sql = "SELECT o.*, c.organization_name as client_name,
                s.label as status_label, s.color as status_color
                FROM orders o
                LEFT JOIN clients c ON o.client_id = c.id
                JOIN statuses s ON o.status_id = s.id
                WHERE s.code = ? AND s.category = 'order'
                ORDER BY o.created_at DESC
                LIMIT ?";
        return $this->query($sql, [$statusCode, $limit]);
    }

    /**
     * Récupère les commandes à planifier (sans date planifiée)
     */
    public function getOrdersToSchedule($limit = 20)
    {
        $sql = "SELECT o.*, c.organization_name as client_name,
                s.label as status_label, s.color as status_color
                FROM orders o
                LEFT JOIN clients c ON o.client_id = c.id
                LEFT JOIN statuses s ON o.status_id = s.id
                WHERE o.assigned_to IS NOT NULL
                AND NOT EXISTS (
                    SELECT 1 FROM interventions i
                    WHERE i.order_id = o.id AND i.scheduled_date IS NOT NULL
                )
                ORDER BY o.priority DESC, o.deadline_date ASC
                LIMIT ?";
        return $this->query($sql, [$limit]);
    }

    /**
     * Récupère les commandes assignées à un technicien
     */
    public function getAssignedToTechnician($technicianId)
    {
        $sql = "SELECT o.*, c.organization_name as client_name,
                s.label as status_label, s.color as status_color
                FROM orders o
                LEFT JOIN clients c ON o.client_id = c.id
                LEFT JOIN statuses s ON o.status_id = s.id
                WHERE o.assigned_to = ?
                ORDER BY o.deadline_date ASC, o.priority DESC";
        return $this->query($sql, [$technicianId]);
    }

    /**
     * Récupère les commandes d'un client
     */
    public function getByClient($clientId, $limit = 10)
    {
        $sql = "SELECT o.*, s.label as status_label, s.color as status_color
                FROM orders o
                LEFT JOIN statuses s ON o.status_id = s.id
                WHERE o.client_id = ?
                ORDER BY o.created_at DESC
                LIMIT ?";
        return $this->query($sql, [$clientId, $limit]);
    }

    /**
     * Récupère les commandes avec rapports en attente pour un client
     */
    public function getWithPendingReports($clientId)
    {
        $sql = "SELECT o.*, s.label as status_label, s.color as status_color
                FROM orders o
                LEFT JOIN statuses s ON o.status_id = s.id
                WHERE o.client_id = ?
                AND s.code IN ('report_pending', 'completed')
                ORDER BY o.updated_at DESC";
        return $this->query($sql, [$clientId]);
    }

    /**
     * Statistiques : commandes par statut
     */
    public function getOrdersByStatus()
    {
        $sql = "SELECT s.code, s.label, COUNT(o.id) as count, s.color
                FROM statuses s
                LEFT JOIN orders o ON o.status_id = s.id
                WHERE s.category = 'order'
                GROUP BY s.id, s.code, s.label, s.color
                ORDER BY s.sort_order";
        return $this->query($sql);
    }

    /**
     * Statistiques : commandes par type de diagnostic
     */
    public function getOrdersByType()
    {
        $sql = "SELECT dt.code, dt.name, COUNT(DISTINCT o.id) as count, dt.color
                FROM diagnostic_types dt
                LEFT JOIN diagnostics d ON d.diagnostic_type_id = dt.id
                LEFT JOIN orders o ON d.order_id = o.id
                WHERE dt.active = 1
                GROUP BY dt.id, dt.code, dt.name, dt.color
                ORDER BY count DESC";
        return $this->query($sql);
    }

    /**
     * Statistiques : revenus (placeholder - à adapter selon votre logique métier)
     */
    public function getRevenue($period = 'month')
    {
        // Cette méthode est un placeholder
        // Vous devrez l'adapter selon votre modèle de facturation
        return [
            'total' => 0,
            'period' => $period,
            'data' => []
        ];
    }
}
