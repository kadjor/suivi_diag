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

    /**
     * Alias pour getMessages (cohérence avec d'autres modèles)
     */
    public function getByOrder($orderId)
    {
        return $this->getMessages($orderId);
    }

    /**
     * Récupère les messages récents pour un utilisateur
     */
    public function getRecent($userId, $limit = 50)
    {
        $sql = "SELECT m.*, o.order_number,
                u.first_name as sender_first_name, u.last_name as sender_last_name,
                r.first_name as recipient_first_name, r.last_name as recipient_last_name
                FROM messages m
                LEFT JOIN orders o ON m.order_id = o.id
                LEFT JOIN users u ON m.user_id = u.id
                LEFT JOIN users r ON m.recipient_id = r.id
                WHERE m.user_id = ? OR m.recipient_id = ? OR m.recipient_id IS NULL
                ORDER BY m.created_at DESC
                LIMIT ?";
        return $this->query($sql, [$userId, $userId, $limit]);
    }
}
