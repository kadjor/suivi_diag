<?php

namespace Models;

use Core\Model;

class Message extends Model
{
    protected $table = 'messages';
    protected $timestamps = false;

    public function getMessages($orderId)
    {
        $sql = "SELECT m.*, u.first_name, u.last_name, u.username,
                r.first_name as recipient_first_name, r.last_name as recipient_last_name
                FROM messages m
                LEFT JOIN users u ON m.user_id = u.id
                LEFT JOIN users r ON m.recipient_id = r.id
                WHERE m.order_id = ?
                ORDER BY m.created_at ASC";
        return $this->query($sql, [$orderId]);
    }
}
