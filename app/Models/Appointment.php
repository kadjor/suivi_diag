<?php
namespace Models;
use Core\Model;

class Appointment extends Model
{
    protected $table = 'appointments';

    /**
     * Récupère tous les rendez-vous dans une période
     */
    public function getAll($start = null, $end = null)
    {
        $sql = "SELECT a.*, o.order_number, s.name as site_name,
                u.first_name as technician_first_name, u.last_name as technician_last_name
                FROM appointments a
                LEFT JOIN orders o ON a.order_id = o.id
                LEFT JOIN sites s ON a.site_id = s.id
                LEFT JOIN users u ON a.technician_id = u.id";

        $params = [];
        $conditions = [];

        if ($start) {
            $conditions[] = "a.start_datetime >= ?";
            $params[] = $start;
        }

        if ($end) {
            $conditions[] = "a.end_datetime <= ?";
            $params[] = $end;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY a.start_datetime ASC";

        return $this->query($sql, $params);
    }

    /**
     * Récupère les rendez-vous d'un technicien
     */
    public function getByTechnician($technicianId, $start = null, $end = null)
    {
        $sql = "SELECT a.*, o.order_number, s.name as site_name, s.address as site_address
                FROM appointments a
                LEFT JOIN orders o ON a.order_id = o.id
                LEFT JOIN sites s ON a.site_id = s.id
                WHERE a.technician_id = ?";

        $params = [$technicianId];

        if ($start) {
            $sql .= " AND a.start_datetime >= ?";
            $params[] = $start;
        }

        if ($end) {
            $sql .= " AND a.end_datetime <= ?";
            $params[] = $end;
        }

        $sql .= " ORDER BY a.start_datetime ASC";

        return $this->query($sql, $params);
    }
}
