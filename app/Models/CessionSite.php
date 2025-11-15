<?php

namespace Models;

use Core\Model;

/**
 * Modèle CessionSite
 * Gère la relation entre les cessions et les sites
 */
class CessionSite extends Model
{
    protected $table = 'cession_sites';

    /**
     * Ajoute un site à une cession
     */
    public function addSite($cessionId, $siteId)
    {
        // Vérifier si le site n'est pas déjà dans la cession
        $exists = $this->queryOne(
            "SELECT id FROM cession_sites WHERE cession_id = ? AND site_id = ?",
            [$cessionId, $siteId]
        );

        if ($exists) {
            return false; // Site déjà ajouté
        }

        // Ajouter le site
        $result = $this->create([
            'cession_id' => $cessionId,
            'site_id' => $siteId,
            'transfer_status' => 'pending'
        ]);

        // Mettre à jour le compteur de sites
        if ($result) {
            $this->execute(
                "UPDATE cessions SET total_sites = total_sites + 1 WHERE id = ?",
                [$cessionId]
            );
        }

        return $result;
    }

    /**
     * Retire un site d'une cession
     */
    public function removeSite($cessionId, $siteId)
    {
        // Vérifier que le site n'a pas déjà été transféré
        $site = $this->queryOne(
            "SELECT transfer_status FROM cession_sites WHERE cession_id = ? AND site_id = ?",
            [$cessionId, $siteId]
        );

        if (!$site) {
            return false;
        }

        if ($site['transfer_status'] === 'transferred') {
            return false; // Ne peut pas retirer un site déjà transféré
        }

        // Supprimer le site
        $result = $this->execute(
            "DELETE FROM cession_sites WHERE cession_id = ? AND site_id = ?",
            [$cessionId, $siteId]
        );

        // Mettre à jour le compteur de sites
        if ($result) {
            $this->execute(
                "UPDATE cessions SET total_sites = total_sites - 1 WHERE id = ?",
                [$cessionId]
            );
        }

        return $result;
    }

    /**
     * Récupère tous les sites d'une cession avec détails
     */
    public function getByCession($cessionId)
    {
        $sql = "SELECT cs.*,
                s.name, s.address, s.city, s.postal_code, s.reference_pch,
                s.building_type, s.construction_year, s.surface,
                COUNT(d.id) as diagnostics_total
                FROM cession_sites cs
                INNER JOIN sites s ON cs.site_id = s.id
                LEFT JOIN diagnostics d ON s.id = d.site_id
                WHERE cs.cession_id = ?
                GROUP BY cs.id, s.id
                ORDER BY cs.created_at DESC";

        return $this->query($sql, [$cessionId]);
    }

    /**
     * Marque un site comme transféré
     */
    public function markAsTransferred($cessionId, $siteId, $notes = null)
    {
        $updateData = [
            'transfer_status' => 'transferred',
            'transfer_date' => date('Y-m-d H:i:s')
        ];

        if ($notes) {
            $updateData['transfer_notes'] = $notes;
        }

        $sql = "UPDATE cession_sites
                SET transfer_status = ?,
                    transfer_date = ?,
                    transfer_notes = ?
                WHERE cession_id = ? AND site_id = ?";

        return $this->execute($sql, [
            'transferred',
            date('Y-m-d H:i:s'),
            $notes,
            $cessionId,
            $siteId
        ]);
    }

    /**
     * Marque un site comme échoué
     */
    public function markAsFailed($cessionId, $siteId, $notes = null)
    {
        $sql = "UPDATE cession_sites
                SET transfer_status = 'failed',
                    transfer_notes = ?
                WHERE cession_id = ? AND site_id = ?";

        return $this->execute($sql, [$notes, $cessionId, $siteId]);
    }

    /**
     * Compte les sites par statut pour une cession
     */
    public function countByStatus($cessionId, $status = null)
    {
        if ($status) {
            $sql = "SELECT COUNT(*) as count FROM cession_sites
                    WHERE cession_id = ? AND transfer_status = ?";
            $result = $this->queryOne($sql, [$cessionId, $status]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM cession_sites WHERE cession_id = ?";
            $result = $this->queryOne($sql, [$cessionId]);
        }

        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Récupère les statistiques d'une cession
     */
    public function getStats($cessionId)
    {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN transfer_status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN transfer_status = 'transferred' THEN 1 ELSE 0 END) as transferred,
                    SUM(CASE WHEN transfer_status = 'failed' THEN 1 ELSE 0 END) as failed,
                    SUM(diagnostics_count) as total_diagnostics
                FROM cession_sites
                WHERE cession_id = ?";

        return $this->queryOne($sql, [$cessionId]);
    }

    /**
     * Vérifie si un site peut être ajouté à une cession
     */
    public function canAddSite($cessionId, $siteId)
    {
        // Vérifier que le site existe
        $site = $this->queryOne("SELECT id, client_id FROM sites WHERE id = ?", [$siteId]);
        if (!$site) {
            return ['success' => false, 'message' => 'Site introuvable'];
        }

        // Récupérer la cession
        $cession = $this->queryOne("SELECT from_client_id, status FROM cessions WHERE id = ?", [$cessionId]);
        if (!$cession) {
            return ['success' => false, 'message' => 'Cession introuvable'];
        }

        // Vérifier que le site appartient au client cédant
        if ($site['client_id'] != $cession['from_client_id']) {
            return ['success' => false, 'message' => 'Le site n\'appartient pas au client cédant'];
        }

        // Vérifier que la cession est modifiable
        if (!in_array($cession['status'], ['draft', 'pending_validation'])) {
            return ['success' => false, 'message' => 'La cession ne peut plus être modifiée'];
        }

        // Vérifier que le site n'est pas déjà dans une autre cession active
        $otherCession = $this->queryOne(
            "SELECT cs.id, c.cession_number
             FROM cession_sites cs
             INNER JOIN cessions c ON cs.cession_id = c.id
             WHERE cs.site_id = ? AND c.status NOT IN ('completed', 'cancelled') AND cs.cession_id != ?",
            [$siteId, $cessionId]
        );

        if ($otherCession) {
            return [
                'success' => false,
                'message' => 'Le site est déjà dans une autre cession active'
            ];
        }

        return ['success' => true];
    }
}
