<?php

namespace Models;

use Core\Model;

class OrderDiagnostic extends Model
{
    protected $table = 'order_diagnostics';

    /**
     * Récupère les diagnostics demandés pour une commande
     */
    public function getByOrder($orderId)
    {
        $sql = "SELECT od.*, dt.name, dt.code, dt.color
                FROM order_diagnostics od
                JOIN diagnostic_types dt ON od.diagnostic_type_id = dt.id
                WHERE od.order_id = ?
                ORDER BY dt.name";

        return $this->query($sql, [$orderId]);
    }

    /**
     * Ajoute des diagnostics à une commande
     */
    public function addDiagnosticsToOrder($orderId, $diagnosticTypeIds, $notes = [])
    {
        $results = [];

        foreach ($diagnosticTypeIds as $diagnosticTypeId) {
            $note = $notes[$diagnosticTypeId] ?? null;

            // Vérifier si déjà existant
            $existing = $this->queryOne(
                "SELECT id FROM order_diagnostics WHERE order_id = ? AND diagnostic_type_id = ?",
                [$orderId, $diagnosticTypeId]
            );

            if (!$existing) {
                $results[] = $this->create([
                    'order_id' => $orderId,
                    'diagnostic_type_id' => $diagnosticTypeId,
                    'notes' => $note
                ]);
            }
        }

        return $results;
    }

    /**
     * Supprime tous les diagnostics d'une commande
     */
    public function removeAllFromOrder($orderId)
    {
        return $this->delete(['order_id' => $orderId]);
    }

    /**
     * Met à jour les diagnostics d'une commande
     */
    public function updateOrderDiagnostics($orderId, $diagnosticTypeIds, $notes = [])
    {
        // Supprimer tous les existants
        $this->removeAllFromOrder($orderId);

        // Ajouter les nouveaux
        return $this->addDiagnosticsToOrder($orderId, $diagnosticTypeIds, $notes);
    }

    /**
     * Compte les commandes par type de diagnostic
     */
    public function countOrdersByDiagnosticType()
    {
        $sql = "SELECT dt.id, dt.name, dt.code, dt.color, COUNT(DISTINCT od.order_id) as order_count
                FROM diagnostic_types dt
                LEFT JOIN order_diagnostics od ON dt.id = od.diagnostic_type_id
                WHERE dt.active = 1
                GROUP BY dt.id, dt.name, dt.code, dt.color
                ORDER BY order_count DESC, dt.name";

        return $this->query($sql);
    }
}
