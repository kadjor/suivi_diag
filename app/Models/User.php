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

    /**
     * Récupère tous les utilisateurs avec leurs rôles
     */
    public function getAll()
    {
        $sql = "SELECT u.*, r.name as role_name, r.label as role_label,
                c.organization_name as client_name
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN clients c ON u.client_id = c.id
                ORDER BY u.created_at DESC";
        return $this->query($sql);
    }

    /**
     * Trouve un utilisateur par nom d'utilisateur
     */
    public function findByUsername($username)
    {
        return $this->whereOne(['username' => $username]);
    }

    /**
     * Trouve un utilisateur par email
     */
    public function findByEmail($email)
    {
        return $this->whereOne(['email' => $email]);
    }

    /**
     * Trouve un utilisateur par token de réinitialisation
     */
    public function findByResetToken($token)
    {
        return $this->whereOne(['password_reset_token' => $token]);
    }

    /**
     * Récupère les utilisateurs par rôle
     */
    public function getByRole($roleName)
    {
        $sql = "SELECT u.*, r.name as role_name, r.label as role_label
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                WHERE r.name = ? AND u.active = 1
                ORDER BY u.first_name, u.last_name";
        return $this->query($sql, [$roleName]);
    }
}
