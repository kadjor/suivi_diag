<?php

namespace Services;

/**
 * Service d'envoi d'emails
 * Version simple utilisant mail() de PHP
 * TODO: Migrer vers PHPMailer pour plus de fonctionnalités
 */
class EmailService
{
    private $from;
    private $fromName;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/app.php';
        $this->from = $config['email']['from'] ?? 'noreply@d-evidences.fr';
        $this->fromName = $config['email']['from_name'] ?? 'D-Evidences';
    }

    /**
     * Envoie un email simple
     */
    public function send($to, $subject, $body, $isHtml = true)
    {
        $headers = [];
        $headers[] = 'From: ' . $this->fromName . ' <' . $this->from . '>';
        $headers[] = 'Reply-To: ' . $this->from;
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        if ($isHtml) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        }

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /**
     * Envoie un email à plusieurs destinataires
     */
    public function sendMultiple($recipients, $subject, $body, $isHtml = true)
    {
        $results = [];
        foreach ($recipients as $recipient) {
            $results[$recipient] = $this->send($recipient, $subject, $body, $isHtml);
        }
        return $results;
    }

    /**
     * Envoie une notification pour un nouveau utilisateur
     */
    public function sendNewUserNotification($user, $tempPassword)
    {
        $subject = 'Bienvenue sur la plateforme D-Evidences';
        $body = $this->renderTemplate('new_user', [
            'user' => $user,
            'temp_password' => $tempPassword
        ]);

        return $this->send($user['email'], $subject, $body);
    }

    /**
     * Envoie une notification pour un nouveau rapport
     */
    public function sendReportUploadedNotification($report, $order, $recipients)
    {
        $subject = 'Nouveau rapport déposé - Commande #' . $order['order_number'];
        $body = $this->renderTemplate('report_uploaded', [
            'report' => $report,
            'order' => $order
        ]);

        return $this->sendMultiple($recipients, $subject, $body);
    }

    /**
     * Envoie une notification pour un événement
     */
    public function sendEventNotification($event, $order, $recipients)
    {
        $subject = 'Mise à jour - Commande #' . $order['order_number'];
        $body = $this->renderTemplate('event', [
            'event' => $event,
            'order' => $order
        ]);

        return $this->sendMultiple($recipients, $subject, $body);
    }

    /**
     * Envoie une notification pour une nouvelle intervention
     */
    public function sendInterventionScheduledNotification($intervention, $order, $recipients)
    {
        $subject = 'Intervention planifiée - Commande #' . $order['order_number'];
        $body = $this->renderTemplate('intervention_scheduled', [
            'intervention' => $intervention,
            'order' => $order
        ]);

        return $this->sendMultiple($recipients, $subject, $body);
    }

    /**
     * Rendu d'un template email
     */
    private function renderTemplate($template, $data)
    {
        $templatePath = __DIR__ . '/../Views/emails/' . $template . '.php';

        if (!file_exists($templatePath)) {
            // Template simple par défaut
            return $this->getDefaultTemplate($template, $data);
        }

        ob_start();
        extract($data);
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Template par défaut si pas de fichier template
     */
    private function getDefaultTemplate($template, $data)
    {
        switch ($template) {
            case 'new_user':
                return $this->getNewUserTemplate($data['user'], $data['temp_password']);

            case 'report_uploaded':
                return $this->getReportUploadedTemplate($data['report'], $data['order']);

            case 'event':
                return $this->getEventTemplate($data['event'], $data['order']);

            case 'intervention_scheduled':
                return $this->getInterventionTemplate($data['intervention'], $data['order']);

            default:
                return '';
        }
    }

    private function getNewUserTemplate($user, $tempPassword)
    {
        return "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Bienvenue sur D-Evidences !</h2>
            <p>Bonjour {$user['first_name']},</p>
            <p>Un compte a été créé pour vous sur notre plateforme de gestion.</p>
            <p><strong>Vos identifiants :</strong></p>
            <ul>
                <li>Email : {$user['email']}</li>
                <li>Mot de passe temporaire : <strong>{$tempPassword}</strong></li>
            </ul>
            <p>Veuillez vous connecter et changer votre mot de passe dès votre première connexion.</p>
            <p><a href='https://gestion.d-evidences.fr/login'>Se connecter</a></p>
        </body>
        </html>
        ";
    }

    private function getReportUploadedTemplate($report, $order)
    {
        return "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Nouveau rapport déposé</h2>
            <p>Un nouveau rapport a été téléversé pour la commande <strong>#{$order['order_number']}</strong>.</p>
            <ul>
                <li>Fichier : {$report['file_name']}</li>
                <li>Taille : " . number_format($report['file_size'] / 1024, 2) . " Ko</li>
                <li>Date : " . date('d/m/Y H:i') . "</li>
            </ul>
            <p><a href='https://gestion.d-evidences.fr/orders/{$order['id']}'>Voir la commande</a></p>
        </body>
        </html>
        ";
    }

    private function getEventTemplate($event, $order)
    {
        return "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Mise à jour - Commande #{$order['order_number']}</h2>
            <p><strong>{$event['event_type']}</strong></p>
            <p>{$event['description']}</p>
            <p><a href='https://gestion.d-evidences.fr/orders/{$order['id']}'>Voir la commande</a></p>
        </body>
        </html>
        ";
    }

    private function getInterventionTemplate($intervention, $order)
    {
        return "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Intervention planifiée</h2>
            <p>Une intervention a été planifiée pour la commande <strong>#{$order['order_number']}</strong>.</p>
            <ul>
                <li>Date : " . date('d/m/Y', strtotime($intervention['scheduled_date'])) . "</li>
                <li>Type : {$intervention['type']}</li>
            </ul>
            <p><a href='https://gestion.d-evidences.fr/orders/{$order['id']}'>Voir la commande</a></p>
        </body>
        </html>
        ";
    }
}
