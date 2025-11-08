<?php

namespace Models;

use Core\Model;

class Diagnostic extends Model
{
    protected $table = 'diagnostics';

    public function getType()
    {
        return $this->queryOne("SELECT * FROM diagnostic_types WHERE id = ?", [$this->diagnostic_type_id]);
    }

    public function getSite()
    {
        return $this->queryOne("SELECT * FROM sites WHERE id = ?", [$this->site_id]);
    }

    /**
     * Récupère les diagnostics d'un client
     */
    public function getByClient($clientId)
    {
        $sql = "SELECT d.*, dt.name as type_name, dt.code as type_code, dt.color as type_color,
                s.name as site_name, s.address as site_address,
                st.label as status_label, st.color as status_color
                FROM diagnostics d
                LEFT JOIN diagnostic_types dt ON d.diagnostic_type_id = dt.id
                LEFT JOIN sites s ON d.site_id = s.id
                LEFT JOIN statuses st ON d.status = st.code AND st.category = 'diagnostic'
                WHERE s.client_id = ?
                ORDER BY d.date DESC";

        return $this->query($sql, [$clientId]);
    }

    /**
     * Récupère les diagnostics d'un site
     */
    public function getBySite($siteId)
    {
        $sql = "SELECT d.*, dt.name as type_name, dt.code as type_code, dt.color as type_color,
                st.label as status_label, st.color as status_color
                FROM diagnostics d
                LEFT JOIN diagnostic_types dt ON d.diagnostic_type_id = dt.id
                LEFT JOIN statuses st ON d.status = st.code AND st.category = 'diagnostic'
                WHERE d.site_id = ?
                ORDER BY d.date DESC";

        return $this->query($sql, [$siteId]);
    }
}
