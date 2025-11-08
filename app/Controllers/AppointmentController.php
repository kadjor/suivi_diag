<?php

namespace Controllers;

use Core\Controller;
use Core\Auth;
use Core\View;
use Core\Session;
use Models\Appointment;
use Models\OrderEvent;
use Helpers\Validator;

/**
 * Contrôleur de gestion des rendez-vous
 */
class AppointmentController extends Controller
{
    private $appointmentModel;

    public function __construct()
    {
        if (!Auth::check()) {
            View::redirect('/login');
        }

        $this->appointmentModel = new Appointment();
    }

    /**
     * Créer un nouveau rendez-vous
     */
    public function store()
    {
        if (!Auth::can('manage_appointments')) {
            View::json(['error' => 'Accès refusé'], 403);
        }

        $data = $_POST;

        $validator = new Validator($data);
        $validator->required(['order_id', 'technician_id', 'start_datetime']);

        if (!$validator->validate()) {
            View::json(['error' => implode(', ', $validator->getErrors())], 400);
        }

        try {
            $appointmentId = $this->appointmentModel->create([
                'order_id' => $data['order_id'],
                'site_id' => $data['site_id'] ?? null,
                'technician_id' => $data['technician_id'],
                'start_datetime' => $data['start_datetime'],
                'end_datetime' => $data['end_datetime'] ?? null,
                'status' => $data['status'] ?? 'scheduled',
                'notes' => $data['notes'] ?? null
            ]);

            // Créer un événement pour la commande
            $eventModel = new OrderEvent();
            $eventModel->create([
                'order_id' => $data['order_id'],
                'event_type' => 'appointment_created',
                'user_id' => Auth::id(),
                'description' => "Rendez-vous planifié pour le " . date('d/m/Y H:i', strtotime($data['start_datetime']))
            ]);

            View::json([
                'success' => true,
                'appointment_id' => $appointmentId,
                'message' => 'Rendez-vous créé avec succès'
            ]);

        } catch (\Exception $e) {
            View::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Exporter un rendez-vous au format ICS (iCalendar)
     */
    public function exportIcs($id)
    {
        $appointment = $this->appointmentModel->find($id);

        if (!$appointment) {
            Session::flash('error', 'Rendez-vous introuvable');
            View::redirect('/calendar');
        }

        // Vérifier les permissions
        $user = Auth::user();
        if ($user['role_name'] === 'technicien' && $appointment['technician_id'] != $user['id']) {
            Session::flash('error', 'Accès refusé');
            View::redirect('/calendar');
        }

        // Récupérer les détails complets
        $details = $this->appointmentModel->queryOne(
            "SELECT a.*, o.order_number, o.description as order_description,
                    s.name as site_name, s.address as site_address,
                    u.first_name, u.last_name, u.email
             FROM appointments a
             LEFT JOIN orders o ON a.order_id = o.id
             LEFT JOIN sites s ON a.site_id = s.id
             LEFT JOIN users u ON a.technician_id = u.id
             WHERE a.id = ?",
            [$id]
        );

        // Générer le fichier ICS
        $ics = $this->generateICS($details);

        // Envoyer les headers pour le téléchargement
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="rendez-vous-' . $id . '.ics"');

        echo $ics;
        exit;
    }

    /**
     * Générer le contenu ICS
     */
    private function generateICS($appointment)
    {
        $startDate = new \DateTime($appointment['start_datetime']);
        $endDate = $appointment['end_datetime']
            ? new \DateTime($appointment['end_datetime'])
            : clone $startDate->modify('+1 hour');

        $now = new \DateTime();

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Suivi Diag//Appointment//FR\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:" . $appointment['id'] . "@suividiag.fr\r\n";
        $ics .= "DTSTAMP:" . $now->format('Ymd\THis\Z') . "\r\n";
        $ics .= "DTSTART:" . $startDate->format('Ymd\THis\Z') . "\r\n";
        $ics .= "DTEND:" . $endDate->format('Ymd\THis\Z') . "\r\n";
        $ics .= "SUMMARY:Rendez-vous - " . ($appointment['order_number'] ?? 'N/A') . "\r\n";

        $description = "Commande: " . ($appointment['order_number'] ?? 'N/A') . "\\n";
        if ($appointment['site_name']) {
            $description .= "Site: " . $appointment['site_name'] . "\\n";
        }
        if ($appointment['site_address']) {
            $description .= "Adresse: " . $appointment['site_address'] . "\\n";
        }
        if ($appointment['notes']) {
            $description .= "Notes: " . $appointment['notes'] . "\\n";
        }

        $ics .= "DESCRIPTION:" . $this->escapeICS($description) . "\r\n";

        if ($appointment['site_address']) {
            $ics .= "LOCATION:" . $this->escapeICS($appointment['site_address']) . "\r\n";
        }

        $ics .= "STATUS:" . strtoupper($appointment['status']) . "\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    /**
     * Échapper les caractères spéciaux pour ICS
     */
    private function escapeICS($text)
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(',', '\\,', $text);
        $text = str_replace(';', '\\;', $text);
        $text = str_replace("\n", '\\n', $text);
        return $text;
    }
}
