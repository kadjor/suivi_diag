<?php

namespace Models;

use Core\Model;

class Status extends Model
{
    protected $table = 'statuses';

    /**
     * Récupère tous les statuts par catégorie
     */
    public function getByCategory($category)
    {
        $sql = "SELECT * FROM statuses
                WHERE category = ? AND active = 1
                ORDER BY sort_order ASC, label ASC";
        return $this->query($sql, [$category]);
    }

    /**
     * Récupère tous les statuts avec regroupement par catégorie
     */
    public function getAllGrouped()
    {
        $sql = "SELECT * FROM statuses
                WHERE active = 1
                ORDER BY category ASC, sort_order ASC, label ASC";
        $statuses = $this->query($sql);

        $grouped = [];
        foreach ($statuses as $status) {
            $grouped[$status['category']][] = $status;
        }

        return $grouped;
    }

    /**
     * Mettre à jour la couleur d'un statut
     */
    public function updateColor($id, $color)
    {
        // Valider le format hexadécimal
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            throw new \Exception('Format de couleur invalide');
        }

        return $this->update($id, ['color' => $color]);
    }
}
