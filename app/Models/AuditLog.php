<?php

namespace Models;

use Core\Model;

class AuditLog extends Model
{
    protected $table = 'audit_log';
    protected $timestamps = false;

    public function log($userId, $action, $entityType = null, $entityId = null, $result = 'success', $payload = null)
    {
        return $this->create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip_address' => getClientIp(),
            'user_agent' => getUserAgent(),
            'payload' => $payload ? json_encode($payload) : null,
            'result' => $result
        ]);
    }
}
