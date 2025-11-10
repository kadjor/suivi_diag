<?php

namespace Models;

use Core\Model;

class EmailNotification extends Model
{
    protected $table = 'email_notifications';

    /**
     * Crée une notification
     */
    public function createNotification($data)
    {
        return $this->create([
            'type' => $data['type'],
            'recipient_email' => $data['recipient_email'],
            'recipient_name' => $data['recipient_name'] ?? null,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'related_order_id' => $data['related_order_id'] ?? null,
            'related_report_id' => $data['related_report_id'] ?? null,
            'status' => 'pending'
        ]);
    }

    /**
     * Marque comme envoyé
     */
    public function markAsSent($notificationId)
    {
        return $this->update($notificationId, [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Marque comme échoué
     */
    public function markAsFailed($notificationId, $errorMessage)
    {
        $notification = $this->find($notificationId);

        return $this->update($notificationId, [
            'status' => 'failed',
            'error_message' => $errorMessage,
            'retry_count' => ($notification['retry_count'] ?? 0) + 1
        ]);
    }

    /**
     * Récupère les notifications en attente
     */
    public function getPending($limit = 50)
    {
        $sql = "SELECT * FROM email_notifications
                WHERE status = 'pending'
                AND retry_count < 3
                ORDER BY sent_at ASC
                LIMIT ?";

        return $this->query($sql, [$limit]);
    }

    /**
     * Récupère l'historique des notifications d'une commande
     */
    public function getByOrder($orderId)
    {
        $sql = "SELECT * FROM email_notifications
                WHERE related_order_id = ?
                ORDER BY sent_at DESC";

        return $this->query($sql, [$orderId]);
    }
}
