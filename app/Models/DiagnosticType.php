<?php

namespace Models;

use Core\Model;

class DiagnosticType extends Model
{
    protected $table = 'diagnostic_types';

    /**
     * Récupère tous les types de diagnostics actifs
     */
    public function getActive()
    {
        $sql = "SELECT * FROM diagnostic_types WHERE active = 1 ORDER BY name";
        return $this->query($sql);
    }

    /**
     * Récupère tous les types de diagnostics
     */
    public function getAll()
    {
        $sql = "SELECT * FROM diagnostic_types ORDER BY name";
        return $this->query($sql);
    }

    /**
     * Récupère un type par code
     */
    public function getByCode($code)
    {
        $sql = "SELECT * FROM diagnostic_types WHERE code = ? LIMIT 1";
        return $this->queryOne($sql, [$code]);
    }

    /**
     * Active ou désactive un type de diagnostic
     */
    public function toggleActive($id)
    {
        $current = $this->find($id);
        if ($current) {
            $newStatus = $current['active'] ? 0 : 1;
            return $this->update($id, ['active' => $newStatus]);
        }
        return false;
    }

    /**
     * Compte le nombre de commandes par type de diagnostic
     */
    public function getOrderCountsByType()
    {
        $sql = "SELECT dt.*, COUNT(DISTINCT od.order_id) as order_count
                FROM diagnostic_types dt
                LEFT JOIN order_diagnostics od ON dt.id = od.diagnostic_type_id
                WHERE dt.active = 1
                GROUP BY dt.id
                ORDER BY order_count DESC, dt.name";
        return $this->query($sql);
    }
}
