<?php
namespace Models;
use Core\Model;

class Intervention extends Model
{
    protected $table = 'interventions';

    /**
     * Récupère les interventions d'un technicien par statut
     */
    public function getByTechnician($technicianId, $status = null)
    {
        $sql = "SELECT i.*, o.order_number, s.name as site_name, s.address as site_address
                FROM interventions i
                LEFT JOIN orders o ON i.order_id = o.id
                LEFT JOIN sites s ON i.site_id = s.id
                WHERE i.technician_id = ?";

        $params = [$technicianId];

        if ($status) {
            $sql .= " AND i.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY i.scheduled_date ASC";

        return $this->query($sql, $params);
    }

    /**
     * Récupère les rendez-vous du jour pour un technicien
     */
    public function getTodayAppointments($technicianId)
    {
        $sql = "SELECT a.*, o.order_number, s.name as site_name, s.address as site_address
                FROM appointments a
                LEFT JOIN orders o ON a.order_id = o.id
                LEFT JOIN sites s ON a.site_id = s.id
                WHERE a.technician_id = ?
                AND DATE(a.start_datetime) = CURDATE()
                ORDER BY a.start_datetime ASC";

        return $this->query($sql, [$technicianId]);
    }

    /**
     * Récupère les rendez-vous à venir pour un technicien
     */
    public function getUpcomingAppointments($technicianId, $days = 7)
    {
        $sql = "SELECT a.*, o.order_number, s.name as site_name, s.address as site_address
                FROM appointments a
                LEFT JOIN orders o ON a.order_id = o.id
                LEFT JOIN sites s ON a.site_id = s.id
                WHERE a.technician_id = ?
                AND a.start_datetime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
                ORDER BY a.start_datetime ASC";

        return $this->query($sql, [$technicianId, $days]);
    }

    /**
     * Récupère les interventions pour une commande spécifique
     */
    public function getByOrderId($orderId)
    {
        $sql = "SELECT i.*,
                       u.first_name as technician_first_name,
                       u.last_name as technician_last_name
                FROM interventions i
                LEFT JOIN users u ON i.technician_id = u.id
                WHERE i.order_id = ?
                ORDER BY i.scheduled_date DESC";

        return $this->query($sql, [$orderId]);
    }

    /**
     * Crée une nouvelle intervention
     */
    public function createIntervention($data)
    {
        return $this->create([
            'order_id' => $data['order_id'],
            'technician_id' => $data['technician_id'] ?? null,
            'scheduled_date' => $data['scheduled_date'],
            'scheduled_time' => $data['scheduled_time'] ?? null,
            'duration' => $data['duration'] ?? 60,
            'type' => $data['type'] ?? 'diagnostic',
            'description' => $data['description'] ?? null,
            'status' => 'scheduled'
        ]);
    }
}
