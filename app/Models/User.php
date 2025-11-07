<?php

namespace Models;

use Core\Model;

class User extends Model
{
    protected $table = 'users';

    public function getRole()
    {
        return $this->queryOne("SELECT * FROM roles WHERE id = ?", [$this->role_id]);
    }

    public function getClient()
    {
        if (!$this->client_id) return null;
        return $this->queryOne("SELECT * FROM clients WHERE id = ?", [$this->client_id]);
    }

    public function createUser($data)
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        unset($data['password']);
        return $this->create($data);
    }

    public function updatePassword($userId, $newPassword)
    {
        return $this->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT)
        ]);
    }
}
