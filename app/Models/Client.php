<?php

namespace Models;

use Core\Model;

class Client extends Model
{
    protected $table = 'clients';

    public function getSites()
    {
        return $this->query("SELECT * FROM sites WHERE client_id = ? ORDER BY name", [$this->id]);
    }

    public function getOrders()
    {
        return $this->query("SELECT * FROM orders WHERE client_id = ? ORDER BY created_at DESC", [$this->id]);
    }

    /**
     * Récupère tous les clients avec stats
     */
    public function getAll()
    {
        $sql = "SELECT c.*,
                COUNT(DISTINCT s.id) as sites_count,
                COUNT(DISTINCT o.id) as orders_count
                FROM clients c
                LEFT JOIN sites s ON s.client_id = c.id
                LEFT JOIN orders o ON o.client_id = c.id
                WHERE c.active = 1
                GROUP BY c.id
                ORDER BY c.organization_name";
        return $this->query($sql);
    }
}
