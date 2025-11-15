<?php

namespace Models;

use Core\Model;

/**
 * Modèle Cession
 * Gère les cessions de sites entre clients
 */
class Cession extends Model
{
    protected $table = 'cessions';

    /**
     * Génère un numéro de cession unique
     * Format: CES-YYYYMMDD-XXXX
     */
    public function generateCessionNumber()
    {
        $date = date('Ymd');
        $count = $this->count(['cession_number' => ['LIKE', "CES-{$date}-%"]]) + 1;
        return sprintf('CES-%s-%04d', $date, $count);
    }

    /**
     * Récupère une cession avec tous les détails
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT c.*,
                cf.organization_name as from_client_name, cf.email as from_client_email,
                ct.organization_name as to_client_name, ct.email as to_client_email,
                u1.first_name as creator_first_name, u1.last_name as creator_last_name,
                u2.first_name as validator_first_name, u2.last_name as validator_last_name
                FROM cessions c
                LEFT JOIN clients cf ON c.from_client_id = cf.id
                LEFT JOIN clients ct ON c.to_client_id = ct.id
                LEFT JOIN users u1 ON c.created_by = u1.id
                LEFT JOIN users u2 ON c.validated_by = u2.id
                WHERE c.id = ?";
        return $this->queryOne($sql, [$id]);
    }

    /**
     * Récupère toutes les cessions avec pagination
     */
    public function getAll($page = 1, $limit = 20, $status = null)
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT c.*,
                cf.organization_name as from_client_name,
                ct.organization_name as to_client_name,
                u.first_name as creator_first_name, u.last_name as creator_last_name
                FROM cessions c
                LEFT JOIN clients cf ON c.from_client_id = cf.id
                LEFT JOIN clients ct ON c.to_client_id = ct.id
                LEFT JOIN users u ON c.created_by = u.id";

        if ($status) {
            $sql .= " WHERE c.status = ?";
            $sql .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
            return $this->query($sql, [$status, $limit, $offset]);
        } else {
            $sql .= " ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
            return $this->query($sql, [$limit, $offset]);
        }
    }

    /**
     * Récupère les cessions d'un client (cédant ou cessionnaire)
     */
    public function getByClient($clientId, $limit = 20, $page = 1)
    {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT c.*,
                cf.organization_name as from_client_name,
                ct.organization_name as to_client_name,
                u.first_name as creator_first_name, u.last_name as creator_last_name
                FROM cessions c
                LEFT JOIN clients cf ON c.from_client_id = cf.id
                LEFT JOIN clients ct ON c.to_client_id = ct.id
                LEFT JOIN users u ON c.created_by = u.id
                WHERE c.from_client_id = ? OR c.to_client_id = ?
                ORDER BY c.created_at DESC
                LIMIT ? OFFSET ?";

        return $this->query($sql, [$clientId, $clientId, $limit, $offset]);
    }

    /**
     * Compte les cessions par statut
     */
    public function countByStatus($status)
    {
        $sql = "SELECT COUNT(*) as count FROM cessions WHERE status = ?";
        $result = $this->queryOne($sql, [$status]);
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Récupère les sites d'une cession
     */
    public function getSites($cessionId)
    {
        $sql = "SELECT cs.*, s.name, s.address, s.city, s.postal_code, s.reference_pch
                FROM cession_sites cs
                INNER JOIN sites s ON cs.site_id = s.id
                WHERE cs.cession_id = ?
                ORDER BY cs.created_at DESC";
        return $this->query($sql, [$cessionId]);
    }

    /**
     * Ajoute un événement à la timeline de la cession
     */
    public function addEvent($cessionId, $eventType, $userId, $data = null)
    {
        $sql = "INSERT INTO cession_events
                (cession_id, event_type, user_id, timestamp, data, ip_address, is_system)
                VALUES (?, ?, ?, NOW(), ?, ?, ?)";

        return $this->execute($sql, [
            $cessionId,
            $eventType,
            $userId,
            $data ? json_encode($data) : null,
            $this->getClientIp(),
            false
        ]);
    }

    /**
     * Récupère la timeline des événements d'une cession
     */
    public function getTimeline($cessionId)
    {
        $sql = "SELECT ce.*, u.first_name, u.last_name, u.username
                FROM cession_events ce
                LEFT JOIN users u ON ce.user_id = u.id
                WHERE ce.cession_id = ?
                ORDER BY ce.timestamp DESC";
        return $this->query($sql, [$cessionId]);
    }

    /**
     * Met à jour le statut d'une cession
     */
    public function updateStatus($cessionId, $status, $userId, $data = [])
    {
        $updateData = ['status' => $status];

        // Champs spécifiques selon le statut
        if ($status === 'validated') {
            $updateData['validated_by'] = $userId;
            $updateData['validated_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'cancelled') {
            $updateData['cancelled_by'] = $userId;
            $updateData['cancelled_at'] = date('Y-m-d H:i:s');
            if (isset($data['cancellation_reason'])) {
                $updateData['cancellation_reason'] = $data['cancellation_reason'];
            }
        } elseif ($status === 'completed') {
            $updateData['completion_date'] = date('Y-m-d');
        }

        $this->update($cessionId, $updateData);
        $this->addEvent($cessionId, $status, $userId, $data);

        return true;
    }

    /**
     * Transfère un site (change le client_id)
     */
    public function transferSite($cessionId, $siteId, $toClientId)
    {
        // Début transaction
        $this->beginTransaction();

        try {
            // Mettre à jour le client_id du site
            $siteModel = new Site();
            $siteModel->update($siteId, ['client_id' => $toClientId]);

            // Mettre à jour le statut dans cession_sites
            $sql = "UPDATE cession_sites
                    SET transfer_status = 'transferred',
                        transfer_date = NOW()
                    WHERE cession_id = ? AND site_id = ?";
            $this->execute($sql, [$cessionId, $siteId]);

            // Compter les diagnostics transférés
            $diagCount = $this->queryOne(
                "SELECT COUNT(*) as count FROM diagnostics WHERE site_id = ?",
                [$siteId]
            );

            if ($diagCount) {
                $sql = "UPDATE cession_sites
                        SET diagnostics_count = ?
                        WHERE cession_id = ? AND site_id = ?";
                $this->execute($sql, [$diagCount['count'], $cessionId, $siteId]);
            }

            // Mettre à jour le compteur de sites transférés
            $sql = "UPDATE cessions
                    SET transferred_sites = transferred_sites + 1
                    WHERE id = ?";
            $this->execute($sql, [$cessionId]);

            // Commit transaction
            $this->commit();

            return true;
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Transfère tous les sites d'une cession
     */
    public function transferAllSites($cessionId, $userId)
    {
        $cession = $this->findById($cessionId);
        if (!$cession) {
            return false;
        }

        $sites = $this->getSites($cessionId);

        foreach ($sites as $site) {
            if ($site['transfer_status'] === 'pending') {
                try {
                    $this->transferSite($cessionId, $site['site_id'], $cession['to_client_id']);
                } catch (\Exception $e) {
                    // Marquer comme échoué
                    $sql = "UPDATE cession_sites
                            SET transfer_status = 'failed',
                                transfer_notes = ?
                            WHERE cession_id = ? AND site_id = ?";
                    $this->execute($sql, [$e->getMessage(), $cessionId, $site['site_id']]);
                }
            }
        }

        // Vérifier si tous les sites sont transférés
        $cession = $this->findById($cessionId);
        if ($cession['transferred_sites'] >= $cession['total_sites']) {
            $this->updateStatus($cessionId, 'completed', $userId);
        }

        return true;
    }

    /**
     * Récupère les documents d'une cession
     */
    public function getDocuments($cessionId)
    {
        $sql = "SELECT cd.*, u.first_name, u.last_name
                FROM cession_documents cd
                LEFT JOIN users u ON cd.uploaded_by = u.id
                WHERE cd.cession_id = ?
                ORDER BY cd.uploaded_at DESC";
        return $this->query($sql, [$cessionId]);
    }

    /**
     * Récupère l'IP du client
     */
    private function getClientIp()
    {
        if (function_exists('getClientIp')) {
            return getClientIp();
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }

    /**
     * Vérifie si une cession peut être modifiée
     */
    public function canBeModified($cessionId)
    {
        $cession = $this->findById($cessionId);
        if (!$cession) {
            return false;
        }

        return in_array($cession['status'], ['draft', 'pending_validation']);
    }

    /**
     * Vérifie si une cession peut être validée
     */
    public function canBeValidated($cessionId)
    {
        $cession = $this->findById($cessionId);
        if (!$cession) {
            return false;
        }

        return $cession['status'] === 'pending_validation' && $cession['total_sites'] > 0;
    }

    /**
     * Vérifie si une cession peut être annulée
     */
    public function canBeCancelled($cessionId)
    {
        $cession = $this->findById($cessionId);
        if (!$cession) {
            return false;
        }

        return !in_array($cession['status'], ['completed', 'cancelled']);
    }
}
