<?php

namespace Models;

use Core\Model;

/**
 * Modèle CustomBranch
 * Gère les branches personnalisées pour le déploiement
 */
class CustomBranch extends Model
{
    protected $table = 'custom_branches';

    /**
     * Récupère toutes les branches personnalisées
     */
    public function getAll()
    {
        $sql = "SELECT cb.*, u.first_name, u.last_name
                FROM custom_branches cb
                LEFT JOIN users u ON cb.created_by = u.id
                ORDER BY cb.branch_name ASC";
        return $this->query($sql);
    }

    /**
     * Ajoute une branche personnalisée
     */
    public function addBranch($branchName, $description, $userId)
    {
        // Vérifier si la branche existe déjà
        $exists = $this->queryOne(
            "SELECT id FROM custom_branches WHERE branch_name = ?",
            [$branchName]
        );

        if ($exists) {
            return ['success' => false, 'message' => 'Cette branche existe déjà'];
        }

        $result = $this->create([
            'branch_name' => $branchName,
            'description' => $description,
            'created_by' => $userId
        ]);

        if ($result) {
            return ['success' => true, 'message' => 'Branche ajoutée avec succès'];
        }

        return ['success' => false, 'message' => 'Erreur lors de l\'ajout de la branche'];
    }

    /**
     * Supprime une branche personnalisée
     */
    public function deleteBranch($id)
    {
        $result = $this->delete($id);

        if ($result) {
            return ['success' => true, 'message' => 'Branche supprimée avec succès'];
        }

        return ['success' => false, 'message' => 'Erreur lors de la suppression'];
    }
}
