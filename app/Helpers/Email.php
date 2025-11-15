<?php

namespace Helpers;

use Models\EmailNotification;

class Email
{
    private static $from = 'noreply@suivi-diagnostics.fr';
    private static $fromName = 'Plateforme Suivi Diagnostics';

    /**
     * Envoi d'email via PHP mail()
     */
    public static function send($to, $subject, $body, $options = [])
    {
        $headers = [
            'From: ' . (self::$fromName ?? '') . ' <' . (self::$from) . '>',
            'Reply-To: ' . ($options['reply_to'] ?? self::$from),
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        ];

        $success = mail($to, $subject, $body, implode("\r\n", $headers));

        // Logger la notification
        $notificationModel = new EmailNotification();
        $notificationId = $notificationModel->createNotification([
            'type' => $options['type'] ?? 'generic',
            'recipient_email' => $to,
            'recipient_name' => $options['recipient_name'] ?? null,
            'subject' => $subject,
            'body' => $body,
            'related_order_id' => $options['order_id'] ?? null,
            'related_report_id' => $options['report_id'] ?? null
        ]);

        if ($success) {
            $notificationModel->markAsSent($notificationId);
        } else {
            $notificationModel->markAsFailed($notificationId, 'mail() returned false');
        }

        return $success;
    }

    /**
     * Notification création de commande au secrétariat
     */
    public static function notifyOrderCreated($order, $client, $creator)
    {
        $secretariatEmails = self::getSecretariatEmails();

        if (empty($secretariatEmails)) {
            return false;
        }

        $subject = "Nouvelle commande {$order['order_number']} créée";

        $body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2 style='color: #2c3e50;'>Nouvelle commande créée</h2>

            <p><strong>Numéro de commande :</strong> {$order['order_number']}</p>
            <p><strong>Client :</strong> {$client['organization_name']}</p>
            <p><strong>Adresse :</strong> {$order['execution_address']}, {$order['execution_postal_code']} {$order['execution_city']}</p>

            " . (!empty($order['execution_numero_porte']) ? "<p><strong>N° de porte :</strong> {$order['execution_numero_porte']}</p>" : "") . "
            " . (!empty($order['execution_niveau']) ? "<p><strong>Étage :</strong> {$order['execution_niveau']}</p>" : "") . "
            " . (!empty($order['numero_lot']) ? "<p><strong>N° de lot :</strong> {$order['numero_lot']}</p>" : "") . "

            <p><strong>Créée par :</strong> {$creator['first_name']} {$creator['last_name']}</p>
            <p><strong>Date :</strong> " . date('d/m/Y à H:i') . "</p>

            <p style='margin-top: 20px;'>
                <a href='" . self::getBaseUrl() . "/orders/{$order['id']}'
                   style='background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>
                    Voir la commande
                </a>
            </p>
        </body>
        </html>
        ";

        foreach ($secretariatEmails as $email) {
            self::send($email, $subject, $body, [
                'type' => 'order_created',
                'order_id' => $order['id']
            ]);
        }

        return true;
    }

    /**
     * Notification dépôt de rapport au client
     */
    public static function notifyReportUploaded($order, $client, $report, $technician)
    {
        // Récupérer les destinataires
        $recipients = [];

        // Client principal
        if (!empty($client['email'])) {
            $recipients[] = $client['email'];
        }

        // Destinataires additionnels de la commande
        if (!empty($order['report_recipients'])) {
            $additionalRecipients = json_decode($order['report_recipients'], true);
            if (is_array($additionalRecipients)) {
                $recipients = array_merge($recipients, $additionalRecipients);
            }
        }

        $recipients = array_unique($recipients);

        if (empty($recipients)) {
            return false;
        }

        $subject = "Nouveau rapport disponible - Commande {$order['order_number']}";

        $diagnosticType = !empty($report['diagnostic_type_name']) ? $report['diagnostic_type_name'] : 'Diagnostic';

        $body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <h2 style='color: #2c3e50;'>Nouveau rapport disponible</h2>

            <p>Bonjour,</p>

            <p>Un nouveau rapport est disponible pour votre commande :</p>

            <div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0;'>
                <p><strong>Commande :</strong> {$order['order_number']}</p>
                <p><strong>Type de diagnostic :</strong> {$diagnosticType}</p>
                <p><strong>Fichier :</strong> {$report['original_filename']}</p>
                <p><strong>Déposé par :</strong> {$technician['first_name']} {$technician['last_name']}</p>
                <p><strong>Date :</strong> " . date('d/m/Y à H:i') . "</p>
            </div>

            <p style='margin-top: 20px;'>
                <a href='" . self::getBaseUrl() . "/orders/{$order['id']}'
                   style='background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>
                    Accéder à mes rapports
                </a>
            </p>

            <p style='color: #666; font-size: 12px; margin-top: 30px;'>
                Vous recevez cet email car vous êtes destinataire des rapports de cette commande.
            </p>
        </body>
        </html>
        ";

        foreach ($recipients as $recipient) {
            self::send($recipient, $subject, $body, [
                'type' => 'report_uploaded',
                'order_id' => $order['id'],
                'report_id' => $report['id'],
                'recipient_name' => $client['organization_name']
            ]);
        }

        return true;
    }

    /**
     * Récupère les emails du secrétariat
     */
    private static function getSecretariatEmails()
    {
        $db = \Core\Database::getConnection();

        $sql = "SELECT email FROM users
                WHERE role_id = (SELECT id FROM roles WHERE name = 'secretariat' LIMIT 1)
                AND active = 1";

        $result = $db->query($sql);
        $emails = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if (!empty($row['email'])) {
                    $emails[] = $row['email'];
                }
            }
        }

        return $emails;
    }

    /**
     * Récupère l'URL de base
     */
    private static function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $protocol . '://' . $host;
    }
}
