<?php

namespace Models;

use Core\Model;

class Setting extends Model
{
    protected $table = 'settings';

    /**
     * Récupère tous les paramètres sous forme de tableau clé=>valeur
     */
    public function getAllAsArray()
    {
        $settings = $this->query("SELECT setting_key, setting_value FROM settings WHERE active = 1");
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }
        
        return $result;
    }

    /**
     * Récupère un paramètre par sa clé
     */
    public function get($key, $default = null)
    {
        $sql = "SELECT setting_value FROM settings WHERE setting_key = ? AND active = 1 LIMIT 1";
        $result = $this->query($sql, [$key]);
        
        return $result[0]['setting_value'] ?? $default;
    }

    /**
     * Définit un paramètre
     */
    public function set($key, $value)
    {
        // Vérifier si le paramètre existe
        $existing = $this->query("SELECT id FROM settings WHERE setting_key = ?", [$key]);
        
        if ($existing) {
            // Mettre à jour
            return $this->query(
                "UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?",
                [$value, $key]
            );
        } else {
            // Créer
            return $this->query(
                "INSERT INTO settings (setting_key, setting_value, created_at) VALUES (?, ?, NOW())",
                [$key, $value]
            );
        }
    }

    /**
     * Supprime un paramètre
     */
    public function delete($key)
    {
        return $this->query("UPDATE settings SET active = 0 WHERE setting_key = ?", [$key]);
    }
}
