<?php

namespace Models;

use Core\Model;

class OrderEvent extends Model
{
    protected $table = 'order_events';
    protected $timestamps = false;

    /**
     * Récupère les événements d'une commande
     */
    public function getByOrder($orderId)
    {
        $sql = "SELECT oe.*, u.first_name, u.last_name, u.username
                FROM order_events oe
                LEFT JOIN users u ON oe.user_id = u.id
                WHERE oe.order_id = ?
                ORDER BY oe.timestamp DESC";
        return $this->query($sql, [$orderId]);
    }
}
