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
}
