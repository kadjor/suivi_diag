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
}
