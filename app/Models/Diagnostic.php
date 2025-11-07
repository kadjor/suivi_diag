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
}
